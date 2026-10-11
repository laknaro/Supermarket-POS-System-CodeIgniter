<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\ProductModel;

class Inventory extends BaseController
{
    private const MIN_THRESHOLD = 10;   // the original page used a fixed reorder point of 10

    public function index()
    {
        $model = new ProductModel();

        if ($this->request->is('post') && $this->request->getPost('action') === 'restock') {
            $qty = (int) $this->request->getPost('restock_qty');
            if ($qty >= 1 && $qty <= 100000 && $model->addStock((int) $this->request->getPost('product_id'), $qty)) {
                $this->flash('Product stock replenished successfully!');
            } else {
                $this->flash('Restock failed: invalid product or quantity.', 'error');
            }

            return redirect()->to('inventory.php');
        }

        $search = trim((string) $this->request->getGet('search'));
        $items  = $model->inventoryList($search);
        $low    = array_values(array_filter($items, static fn (array $i): bool => (int) $i['stock'] <= self::MIN_THRESHOLD));

        return $this->page('inventory', [
            'pageTitle'           => 'Inventory & Stock Control - EGOTECH WORLD',
            'search'              => $search,
            'inventoryItems'      => $items,
            'minThresholdDefault' => self::MIN_THRESHOLD,
            'lowStockItems'       => $low,
            'lowStockCount'       => count($low),
        ]);
    }
}
