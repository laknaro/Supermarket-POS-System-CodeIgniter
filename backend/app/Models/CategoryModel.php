<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table         = 'categories';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['name', 'description'];
    protected $useTimestamps = false;   // created_at has a DB default

    protected $validationRules = [
        'id'          => 'permit_empty|is_natural_no_zero',   // CI4 needs this for the {id} placeholder
        'name'        => 'required|max_length[100]|is_unique[categories.name,id,{id}]',
        'description' => 'permit_empty|max_length[2000]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'  => 'Category name cannot be empty.',
            'is_unique' => 'A category with this name already exists.',
        ],
    ];

    /** Categories + number of ACTIVE products (optionally filtered by name/description). */
    public function withProductCount(string $search = ''): array
    {
        $builder = $this->select('categories.*, COUNT(products.id) AS product_count')
            ->join('products', "products.category_id = categories.id AND products.status = 'Active'", 'left')
            ->groupBy('categories.id')
            ->orderBy('categories.name', 'ASC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('categories.name', $search)
                ->orLike('categories.description', $search)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
