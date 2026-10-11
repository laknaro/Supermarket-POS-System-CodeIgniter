<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductModel;

/** api/get_products.php: same query parameters and JSON as the original. */
class Products extends BaseController
{
    public function index()
    {
        $this->response->noCache();

        try {
            $model   = new ProductModel();
            $barcode = trim((string) $this->request->getGet('barcode'));
            $search  = trim((string) $this->request->getGet('search'));
            $catId   = filter_var($this->request->getGet('category_id'), FILTER_VALIDATE_INT) ?: null;

            if ($barcode !== '') {
                $p = $model->findActiveByBarcode($barcode);
                if (! $p) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error', 'message' => 'Product not found for barcode: ' . $barcode,
                    ]);
                }

                return $this->response->setJSON(['status' => 'success', 'data' => $this->strip($p)]);
            }

            $rows = $search !== '' || $catId
                ? $model->filterActive($search, $catId)
                : $model->listActive();

            return $this->response->setJSON(['status' => 'success', 'data' => array_map([$this, 'strip'], array_slice($rows, 0, 300))]);
        } catch (\Throwable $e) {
            log_message('error', 'api/get_products failed: {m}', ['m' => $e->getMessage()]);

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error', 'message' => 'Database error while searching products.',
            ]);
        }
    }

    private function strip(array $p): array
    {
        unset($p['cost_price']);   // cashiers must not receive cost prices

        return $p;
    }
}
