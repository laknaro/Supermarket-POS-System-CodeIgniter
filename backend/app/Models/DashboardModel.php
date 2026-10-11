<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/** Read-only queries for the executive dashboard (same variables the original dashboard.php computed). */
class DashboardModel
{
    private BaseConnection $db;
    private string $from;
    private string $to;

    public function __construct()
    {
        $this->db   = db_connect();
        $this->from = date('Y-m-d 00:00:00');
        $this->to   = date('Y-m-d 00:00:00', strtotime('+1 day'));
    }

    /** @return array<string,mixed> */
    public function all(): array
    {
        $db = $this->db;

        $today = $db->query(
            'SELECT COALESCE(SUM(total_amount), 0) AS rev, COUNT(id) AS bills FROM bills WHERE created_at >= ? AND created_at < ?',
            [$this->from, $this->to]
        )->getRowArray();

        $activeStaff   = (int) $db->table('users')->where('status', 'Active')->countAllResults();
        $lowStockCount = (new ProductModel())->lowStockCount();

        // Register drawer settlement
        $tender = ['Cash' => 0.0, 'Card' => 0.0, 'LankaQR' => 0.0];
        foreach ($db->query(
            'SELECT payment_method, COALESCE(SUM(total_amount), 0) AS t FROM bills WHERE created_at >= ? AND created_at < ? GROUP BY payment_method',
            [$this->from, $this->to]
        )->getResultArray() as $r) {
            $tender[$r['payment_method']] = (float) $r['t'];
        }
        $settlementTotal = array_sum($tender);
        $pct             = static fn (float $v) => $settlementTotal > 0 ? (int) round($v / $settlementTotal * 100) : 0;

        // Hourly velocity 08:00 - 19:00
        $hourly = [];
        for ($h = 8; $h <= 19; $h++) {
            $hourly[$h] = ['count' => 0, 'revenue' => 0.0];
        }
        foreach ($db->query(
            'SELECT HOUR(created_at) AS hr, COUNT(id) AS c, COALESCE(SUM(total_amount), 0) AS r FROM bills WHERE created_at >= ? AND created_at < ? GROUP BY HOUR(created_at)',
            [$this->from, $this->to]
        )->getResultArray() as $r) {
            $hr = (int) $r['hr'];
            if (isset($hourly[$hr])) {
                $hourly[$hr] = ['count' => (int) $r['c'], 'revenue' => (float) $r['r']];
            }
        }
        $maxBillCount = 1;
        $maxCount     = 0;
        $peak         = 'No Sales Recorded Today';
        foreach ($hourly as $hr => $stat) {
            if ($stat['count'] > $maxCount) {
                $maxCount = $stat['count'];
                $peak     = 'Peak: ' . date('h:00 A', mktime($hr, 0)) . ' - ' . date('h:00 A', mktime($hr + 1, 0));
            }
            $maxBillCount = max($maxBillCount, $stat['count']);
        }

        // Fast-moving products today
        $fast = $db->query(
            'SELECT bi.product_name, SUM(bi.quantity) AS units_sold
               FROM bill_items bi JOIN bills b ON bi.bill_id = b.id
              WHERE b.created_at >= ? AND b.created_at < ?
              GROUP BY bi.product_id, bi.product_name
              ORDER BY units_sold DESC LIMIT 4',
            [$this->from, $this->to]
        )->getResultArray();

        // Top revenue categories today (falls back to all-time when nothing sold today)
        $cats = $db->query(
            'SELECT c.name AS category_name,
                    COALESCE(SUM(CASE WHEN b.id IS NOT NULL THEN bi.line_total ELSE 0 END), 0) AS cat_revenue
               FROM categories c
               LEFT JOIN products p ON p.category_id = c.id
               LEFT JOIN bill_items bi ON bi.product_id = p.id
               LEFT JOIN bills b ON bi.bill_id = b.id AND b.created_at >= ? AND b.created_at < ?
              GROUP BY c.id, c.name
              ORDER BY cat_revenue DESC, c.name ASC LIMIT 5',
            [$this->from, $this->to]
        )->getResultArray();
        $catTotal = array_sum(array_column($cats, 'cat_revenue'));
        if ($catTotal == 0) {
            $cats = $db->query(
                'SELECT c.name AS category_name, COALESCE(SUM(bi.line_total), 0) AS cat_revenue
                   FROM categories c
                   LEFT JOIN products p ON p.category_id = c.id
                   LEFT JOIN bill_items bi ON bi.product_id = p.id
                  GROUP BY c.id, c.name ORDER BY cat_revenue DESC, c.name ASC LIMIT 5'
            )->getResultArray();
            $catTotal = array_sum(array_column($cats, 'cat_revenue'));
        }

        return [
            'todayRevenue'    => (float) $today['rev'],
            'todayBills'      => (int) $today['bills'],
            'activeStaff'     => $activeStaff,
            'lowStockCount'   => $lowStockCount,
            'cashTotal'       => $tender['Cash'],
            'cardTotal'       => $tender['Card'],
            'lankaQrTotal'    => $tender['LankaQR'],
            'settlementTotal' => $settlementTotal,
            'cashPct'         => $pct($tender['Cash']),
            'cardPct'         => $pct($tender['Card']),
            'qrPct'           => $pct($tender['LankaQR']),
            'hourlyData'      => $hourly,
            'maxBillCount'    => $maxBillCount,
            'peakHourText'    => $peak,
            'fastMoving'      => $fast,
            'topCategories'   => $cats,
            'totalCatRevenue' => (float) $catTotal,
        ];
    }
}
