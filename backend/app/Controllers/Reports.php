<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BillModel;

class Reports extends BaseController
{
    public function index()
    {
        $period = (string) $this->request->getGet('period');
        $period = in_array($period, ['all', 'today', 'week', 'month'], true) ? $period : 'all';
        $model  = new BillModel();

        // reports.php?fetch_full_bill_id=ID  (receipt re-print)
        if ($this->request->getGet('fetch_full_bill_id') !== null) {
            $bill = $model->findWithItems((int) $this->request->getGet('fetch_full_bill_id'));

            return $bill === null
                ? $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Bill not found'])
                : $this->response->setJSON(['status' => 'success', 'data' => $bill]);
        }

        // reports.php?fetch_bill_id=ID  (line-items popup: plain array)
        if ($this->request->getGet('fetch_bill_id') !== null) {
            return $this->response->setJSON($model->items((int) $this->request->getGet('fetch_bill_id')));
        }

        // reports.php?action=export_csv&period=...
        if ($this->request->getGet('action') === 'export_csv') {
            return $this->exportCsv($period);
        }

        return $this->page('reports', [
            'pageTitle'    => 'Reports & Analytics - EGOTECH WORLD',
            'filterPeriod' => $period,
            'metrics'      => $model->metrics($period),
            'bills'        => (new BillModel())->withCashier()->period($period)->orderBy('bills.created_at', 'DESC')->findAll(150),
        ]);
    }

    private function exportCsv(string $period)
    {
        $rows = (new BillModel())->withCashier()->period($period)->orderBy('bills.created_at', 'DESC')->findAll();

        $fh = fopen('php://temp', 'r+');
        fputcsv($fh, ['Bill Number', 'Date & Time', 'Cashier', 'Customer', 'Payment Method', 'Subtotal (Rs)', 'Discount (Rs)', 'Net Total (Rs)'], ',', '"', '\\');
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['bill_number'],
                "\t" . date('Y-m-d H:i', strtotime((string) $r['created_at'])),
                $this->csvSafe((string) $r['cashier_name']),
                $this->csvSafe((string) ($r['customer_name'] ?? 'Walk-in Customer')),
                $r['payment_method'],
                number_format((float) $r['subtotal'], 2, '.', ''),
                number_format((float) $r['discount_amount'], 2, '.', ''),
                number_format((float) $r['total_amount'], 2, '.', ''),
            ], ',', '"', '\\');
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="Sales_Report_' . $period . '_' . date('Ymd_His') . '.csv"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    /** Stops spreadsheet formula injection (=, +, -, @ at the start of a cell). */
    private function csvSafe(string $v): string
    {
        return preg_match('/^[=+\-@]/', $v) === 1 ? "'" . $v : $v;
    }
}
