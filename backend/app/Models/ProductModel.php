<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    /** Same low-stock number the original dashboard / sidebar / products page used. */
    public const LOW_STOCK_LIMIT = 5;

    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'category_id', 'name', 'barcode', 'sku', 'price',
        'cost_price', 'stock', 'alert_quantity', 'image_url', 'status',
    ];
    protected $useTimestamps = false;

    protected $validationRules = [
        'id'             => 'permit_empty|is_natural_no_zero',
        'name'           => 'required|max_length[255]',
        'barcode'        => 'required|max_length[100]|is_unique[products.barcode,id,{id}]',
        'sku'            => 'permit_empty|max_length[100]|is_unique[products.sku,id,{id}]',
        'price'          => 'required|numeric|greater_than[0]',
        'cost_price'     => 'permit_empty|numeric|greater_than_equal_to[0]',
        'stock'          => 'permit_empty|integer|greater_than_equal_to[0]',
        'alert_quantity' => 'permit_empty|integer|greater_than_equal_to[0]',
        'status'         => 'permit_empty|in_list[Active,Inactive]',
    ];

    protected $validationMessages = [
        'name'    => ['required' => 'Please fill in all required fields (Product Name, Barcode, and Price).'],
        'barcode' => [
            'required'  => 'Please fill in all required fields (Product Name, Barcode, and Price).',
            'is_unique' => 'A product with this barcode already exists.',
        ],
        'sku'     => ['is_unique' => 'A product with this SKU already exists.'],
        'price'   => [
            'required'     => 'Please fill in all required fields (Product Name, Barcode, and Price).',
            'greater_than' => 'Please fill in all required fields (Product Name, Barcode, and Price).',
        ],
    ];

    protected $beforeInsert = ['ensureSku'];

    protected function ensureSku(array $data): array
    {
        if (empty($data['data']['sku'])) {
            $data['data']['sku'] = 'SKU-' . strtoupper(bin2hex(random_bytes(4)));   // auto-generated when left blank
        }

        return $data;
    }

    private function withCategory(): static
    {
        return $this->select('products.*, categories.name AS category_name')
            ->join('categories', 'products.category_id = categories.id', 'left');
    }

    public function findActiveByBarcode(string $barcode): ?array
    {
        return $this->withCategory()
            ->where('products.status', 'Active')
            ->where('products.barcode', $barcode)
            ->first();
    }

    /** POS grid. */
    public function listActive(): array
    {
        return $this->withCategory()->where('products.status', 'Active')->orderBy('products.id', 'DESC')->findAll();
    }

    /** Products page (search + category filter). */
    public function filterActive(string $search = '', ?int $categoryId = null): array
    {
        $builder = $this->withCategory()->where('products.status', 'Active');

        if ($search !== '') {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.barcode', $search)
                ->orLike('products.sku', $search)
                ->groupEnd();
        }
        if ($categoryId) {
            $builder->where('products.category_id', $categoryId);
        }

        return $builder->orderBy('products.id', 'DESC')->findAll();
    }

    /** Inventory page: lowest stock first. */
    public function inventoryList(string $search = ''): array
    {
        $builder = $this->select("products.*, COALESCE(categories.name, 'General Staples') AS category_name", false)
            ->join('categories', 'products.category_id = categories.id', 'left')
            ->where('products.status', 'Active');

        if ($search !== '') {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.barcode', $search)
                ->orLike('products.sku', $search)
                ->groupEnd();
        }

        return $builder->orderBy('products.stock', 'ASC')->orderBy('products.name', 'ASC')->findAll();
    }

    public function lowStockCount(): int
    {
        return $this->where('status', 'Active')->where('stock <=', self::LOW_STOCK_LIMIT)->countAllResults();
    }

    /** @return array{total:int,low:int,value:float} */
    public function stats(): array
    {
        $row = $this->select(
            'COUNT(id) AS total,
             COALESCE(SUM(CASE WHEN stock <= ' . self::LOW_STOCK_LIMIT . ' THEN 1 ELSE 0 END), 0) AS low,
             COALESCE(SUM(price * stock), 0) AS value',
            false
        )->where('status', 'Active')->first();

        return ['total' => (int) $row['total'], 'low' => (int) $row['low'], 'value' => (float) $row['value']];
    }

    /** Atomic stock increase. */
    public function addStock(int $id, int $qty): bool
    {
        if ($id <= 0 || $qty <= 0) {
            return false;
        }
        $this->db->table($this->table)->set('stock', 'stock + ' . $qty, false)->where('id', $id)->update();

        return $this->db->affectedRows() === 1;
    }
}
