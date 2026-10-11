<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BillModel;

/** api/save_bill.php: same JSON in / out contract as the original (used unchanged by assets/js/pos.js). */
class Bills extends BaseController
{
    public function store()
    {
        try {
            $input = $this->request->getJSON(true);
        } catch (\Throwable $e) {
            $input = null;
        }
        if (! is_array($input)) {
            return $this->fail('Invalid JSON payload received.', 400);
        }

        try {
            $result = (new BillModel())->createSale(
                $input,
                (int) session()->get('user_id'),
                (string) session()->get('username')
            );
        } catch (\DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Checkout failed: {m}', ['m' => $e->getMessage()]);

            return $this->fail('Transaction failed. Please try again.', 500);
        }

        return $this->response->setStatusCode(201)->setJSON($result);
    }

    private function fail(string $message, int $code)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => false, 'status' => 'error', 'error' => $message, 'message' => $message,
        ]);
    }
}
