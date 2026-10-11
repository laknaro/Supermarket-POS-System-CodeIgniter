<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BillModel;

class BillHistory extends BaseController
{
    public function index()
    {
        $model = new BillModel();

        // AJAX used by the page: bill_history.php?fetch_full_bill_id=ID
        if ($this->request->getGet('fetch_full_bill_id') !== null) {
            $bill = $model->findWithItems((int) $this->request->getGet('fetch_full_bill_id'));

            return $bill === null
                ? $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Bill not found'])
                : $this->response->setJSON(['status' => 'success', 'data' => $bill]);
        }

        $search = trim((string) $this->request->getGet('search'));
        $method = trim((string) $this->request->getGet('method'));

        return $this->page('bill_history', [
            'pageTitle'    => 'Bill History & Thermal Receipts - EGOTECH WORLD',
            'search'       => $search,
            'methodFilter' => $method,
            'bills'        => $model->withCashier()->search($search, $method)->orderBy('bills.created_at', 'DESC')->findAll(150),
        ]);
    }
}
