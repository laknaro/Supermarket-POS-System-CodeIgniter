<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class Products extends BaseController
{
    private ProductModel $products;

    public function __construct()
    {
        $this->products = new ProductModel();
    }

    public function index()
    {
        // The original frontend deletes through a plain link: products.php?action=delete&id=X
        if ($this->request->is('get') && $this->request->getGet('action') === 'delete') {
            return $this->archiveFromLink();
        }

        if ($this->request->is('post')) {
            $this->handlePost();

            return redirect()->to('products.php');
        }

        $search = trim((string) $this->request->getGet('search'));
        $catId  = filter_var($this->request->getGet('category_id'), FILTER_VALIDATE_INT) ?: null;
        $stats  = $this->products->stats();

        return $this->page('products', [
            'pageTitle'       => 'Products & Inventory - EGOTECH WORLD',
            'search'          => $search,
            'catFilter'       => $catId,
            'categories'      => (new CategoryModel())->orderBy('name', 'ASC')->findAll(),
            'products'        => $this->products->filterActive($search, $catId),
            'totalProducts'   => $stats['total'],
            'lowStockCount'   => $stats['low'],
            'totalStockValue' => $stats['value'],
        ]);
    }

    /**
     * "Delete" archives the product (status = Inactive). Hard-deleting would also delete its
     * bill_items and break old receipts / sales history.
     * The link is a GET request, so only accept it when it comes from this same site (CSRF guard).
     */
    private function archiveFromLink()
    {
        $host    = $this->request->getUri()->getHost();
        $referer = (string) $this->request->getServer('HTTP_REFERER');
        $site    = strtolower($this->request->getHeaderLine('Sec-Fetch-Site'));

        $sameOrigin = $site === 'same-origin'
            || ($referer !== '' && strcasecmp((string) parse_url($referer, PHP_URL_HOST), $host) === 0);

        if (! $sameOrigin) {
            $this->flash('Delete blocked: the request did not come from this site.', 'error');

            return redirect()->to('products.php');
        }

        $id = (int) $this->request->getGet('id');
        if ($id > 0) {
            $this->products->update($id, ['status' => 'Inactive']);
            $this->flash('Product deleted successfully!');
        }

        return redirect()->to('products.php');
    }

    private function handlePost(): void
    {
        $in     = $this->request;
        $action = (string) $in->getPost('action');

        if ($action === 'add_product') {
            $image = $this->saveUploadedImage();
            if ($image === null) {
                $url   = trim((string) $in->getPost('image_url'));
                $image = preg_match('#^https?://#i', $url) === 1 ? $url : '';
            }
            $cost = trim((string) $in->getPost('cost_price'));

            $data = [
                'category_id'    => ((int) $in->getPost('category_id')) ?: null,
                'name'           => trim((string) $in->getPost('name')),
                'barcode'        => trim((string) $in->getPost('barcode')),
                'sku'            => trim((string) $in->getPost('sku')),
                'price'          => trim((string) $in->getPost('price')),
                'cost_price'     => $cost === '' ? 0 : $cost,
                'stock'          => max(0, (int) ($in->getPost('stock') ?? 1)),
                'alert_quantity' => max(0, (int) ($in->getPost('alert_quantity') ?? 5)),
                'image_url'      => $image,
                'status'         => $in->getPost('status') === 'Inactive' ? 'Inactive' : 'Active',
            ];

            try {
                if ($this->products->insert($data)) {
                    $this->flash('New product created and saved successfully!');
                } else {
                    $this->flash(implode(' ', $this->products->errors()), 'error');
                }
            } catch (\Throwable $e) {
                log_message('error', 'Product insert failed: {m}', ['m' => $e->getMessage()]);
                $this->flash('Database Error: could not save the product.', 'error');
            }
        } elseif ($action === 'edit_product') {
            $id   = (int) $in->getPost('id');
            $cost = trim((string) $in->getPost('cost_price'));

            $data = [
                'id'          => $id,   // lets is_unique ignore this row
                'category_id' => ((int) $in->getPost('category_id')) ?: null,
                'name'        => trim((string) $in->getPost('name')),
                'barcode'     => trim((string) $in->getPost('barcode')),
                'sku'         => trim((string) $in->getPost('sku')),
                'price'       => trim((string) $in->getPost('price')),
                'cost_price'  => $cost === '' ? 0 : $cost,
                'stock'       => max(0, (int) $in->getPost('stock')),
                'status'      => $in->getPost('status') === 'Inactive' ? 'Inactive' : 'Active',
            ];

            $this->products->setValidationRule('sku', 'required|max_length[100]|is_unique[products.sku,id,{id}]');

            try {
                if ($id > 0 && $this->products->update($id, $data)) {
                    $this->flash('Product details updated successfully!');
                } else {
                    $this->flash('Update failed: ' . implode(' ', $this->products->errors()), 'error');
                }
            } catch (\Throwable $e) {
                log_message('error', 'Product update failed: {m}', ['m' => $e->getMessage()]);
                $this->flash('Update failed.', 'error');
            }
        } elseif ($action === 'restock') {
            $qty = (int) $in->getPost('add_stock');
            if ($qty >= 1 && $qty <= 100000 && $this->products->addStock((int) $in->getPost('id'), $qty)) {
                $this->flash('Product stock replenished successfully!');
            } else {
                $this->flash('Restock failed: invalid product or quantity.', 'error');
            }
        } elseif ($action === 'delete_product') {
            $id = (int) $in->getPost('id');
            if ($id > 0) {
                $this->products->update($id, ['status' => 'Inactive']);
                $this->flash('Product deleted successfully!');
            }
        }
    }

    /** web path like assets/images/products/xyz.jpg or null (SVG is not accepted: stored-XSS risk) */
    private function saveUploadedImage(): ?string
    {
        $file = $this->request->getFile('image_file');
        if ($file === null || ! $file->isValid() || $file->hasMoved() || $file->getSizeByUnit('mb') > 2) {
            return null;
        }
        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
            return null;
        }

        $dir = FCPATH . 'assets/images/products';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = $file->getRandomName();
        $file->move($dir, $name);

        return 'assets/images/products/' . $name;
    }
}
