<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class Pos extends BaseController
{
    public function index(): string
    {
        // The cashier's browser must never receive cost prices.
        $products = array_map(static function (array $p): array {
            unset($p['cost_price']);

            return $p;
        }, (new ProductModel())->listActive());

        return $this->page('pos', [
            'pageTitle'        => 'Point of Sale (POS) - EGOTECH WORLD',
            'categories'       => (new CategoryModel())->withProductCount(),
            'products'         => $products,
            'totalActiveCount' => count($products),
        ]);
    }
}
