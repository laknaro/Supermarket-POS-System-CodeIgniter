<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class BillModel extends Model
{
    protected $table      = 'bills';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    public function withCashier(): static
    {
        return $this->select("bills.*, COALESCE(users.username, 'Unknown') AS cashier_name", false)
            ->join('users', 'bills.cashier_id = users.id', 'left');
    }

    public function search(string $q = '', string $method = ''): static
    {
        if ($q !== '') {
            $this->groupStart()
                ->like('bills.bill_number', $q)
                ->orLike('bills.customer_name', $q)
                ->orLike('users.username', $q)
                ->groupEnd();
        }
        if (in_array($method, ['Cash', 'Card', 'LankaQR'], true)) {
            $this->where('bills.payment_method', $method);
        }

        return $this;
    }

    /** all | today | week | month */
    public function period(string $period): static
    {
        return match ($period) {
            'today' => $this->where('bills.created_at >=', date('Y-m-d 00:00:00')),
            'week'  => $this->where('bills.created_at >=', date('Y-m-d 00:00:00', strtotime('monday this week'))),
            'month' => $this->where('bills.created_at >=', date('Y-m-01 00:00:00')),
            default => $this,
        };
    }

    /** Same keys the original reports.php used. */
    public function metrics(string $period): array
    {
        $row = $this->period($period)->select(
            "COUNT(*) AS total_bills,
             COALESCE(SUM(bills.total_amount), 0) AS total_revenue,
             COALESCE(AVG(bills.total_amount), 0) AS avg_bill,
             COALESCE(SUM(CASE WHEN bills.payment_method = 'Cash'    THEN bills.total_amount END), 0) AS cash_total,
             COALESCE(SUM(CASE WHEN bills.payment_method = 'Card'    THEN bills.total_amount END), 0) AS card_total,
             COALESCE(SUM(CASE WHEN bills.payment_method = 'LankaQR' THEN bills.total_amount END), 0) AS qr_total",
            false
        )->first();

        return $row ?: [];
    }

    /** Bill + line items (same shape as the original fetch_full_bill_id endpoint). */
    public function findWithItems(int $id): ?array
    {
        $bill = $this->withCashier()->where('bills.id', $id)->first();
        if (! $bill) {
            return null;
        }
        $bill['items'] = $this->items($id);

        return $bill;
    }

    public function items(int $billId): array
    {
        return $this->db->table('bill_items')->where('bill_id', $billId)->orderBy('id', 'ASC')->get()->getResultArray();
    }

    /**
     * Complete a sale in ONE transaction (bill + items + stock deduction).
     * Prices come from the database, never from the browser. Same JSON contract as the
     * original api/save_bill.php: items[{id, qty}], discount, tender_amount, payment_method, customer_name.
     *
     * @throws \DomainException  business-rule failure, safe to show the cashier
     */
    public function createSale(array $in, int $cashierId, string $cashierName): array
    {
        // 1. Normalise the cart (merge duplicate lines)
        if (empty($in['items']) || ! is_array($in['items'])) {
            throw new \DomainException('Cart is empty. At least one line item is required.');
        }
        $qtyById = [];
        foreach ($in['items'] as $row) {
            $pid = is_array($row) ? (int) ($row['id'] ?? 0) : 0;
            if ($pid <= 0) {
                throw new \DomainException('Invalid product reference in bill items.');
            }
            $qty = filter_var($row['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (! $qty) {
                throw new \DomainException("Invalid quantity for line item ID: {$pid}");
            }
            $qtyById[$pid] = ($qtyById[$pid] ?? 0) + $qty;
        }

        $method = (string) ($in['payment_method'] ?? 'Cash');
        if (! in_array($method, ['Cash', 'Card', 'LankaQR'], true)) {
            throw new \DomainException('Invalid payment method selected.');
        }

        $db = $this->db;
        $db->transBegin();

        try {
            // 2. Lock the product rows (FOR UPDATE) and read trusted prices + stock
            $products = $db->query(
                "SELECT id, name, barcode, price, stock FROM products WHERE status = 'Active' AND id IN ? FOR UPDATE",
                [array_keys($qtyById)]
            )->getResultArray();
            $byId = array_column($products, null, 'id');

            $items    = [];
            $subtotal = 0.0;
            foreach ($qtyById as $pid => $qty) {
                $p = $byId[$pid] ?? null;
                if (! $p) {
                    throw new \DomainException("Product with ID #{$pid} is inactive or no longer exists.");
                }
                $stock = (int) $p['stock'];
                if ($qty > $stock) {
                    throw new \DomainException("Insufficient stock for '{$p['name']}'. Requested: {$qty}, Available: {$stock}.");
                }
                $price     = (float) $p['price'];
                $lineTotal = round($price * $qty, 2);
                $subtotal += $lineTotal;
                $items[]   = [
                    'id'              => (int) $pid,
                    'barcode'         => $p['barcode'],
                    'name'            => $p['name'],
                    'price'           => $price,
                    'qty'             => $qty,
                    'total'           => $lineTotal,
                    'remaining_stock' => $stock - $qty,
                ];
            }

            // 3. Totals (tax is computed here, not taken from the browser)
            $subtotal = round($subtotal, 2);
            $discount = round(min(max(0.0, (float) ($in['discount'] ?? 0)), $subtotal), 2);
            $tax      = round(($subtotal - $discount) * (config('Store')->taxRatePercent / 100), 2);
            $total    = round(max(0.0, $subtotal - $discount + $tax), 2);

            $tender = round(max(0.0, (float) ($in['tender_amount'] ?? 0)), 2);
            if ($method === 'Cash' && $tender + 0.001 < $total) {
                throw new \DomainException('Tendered cash (Rs. ' . number_format($tender, 2) . ') is less than net total (Rs. ' . number_format($total, 2) . ').');
            }
            if ($method !== 'Cash') {
                $tender = $total;
            }
            $change = round(max(0.0, $tender - $total), 2);

            // 4. Invoice number (same format as before) + customer
            do {
                $billNumber = 'INV-' . date('YmdHis') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
                $exists     = $db->table('bills')->where('bill_number', $billNumber)->countAllResults() > 0;
            } while ($exists);

            $customer = trim((string) ($in['customer_name'] ?? ''));
            $customer = $customer !== '' ? mb_substr($customer, 0, 100) : 'Walk-in Customer';
            $now      = date('Y-m-d H:i:s');

            $db->table('bills')->insert([
                'bill_number'     => $billNumber,
                'cashier_id'      => $cashierId > 0 ? $cashierId : null,
                'customer_name'   => $customer,
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'tax_amount'      => $tax,
                'total_amount'    => $total,
                'tender_amount'   => $tender,
                'change_amount'   => $change,
                'payment_method'  => $method,
                'created_at'      => $now,
            ]);
            $billId = (int) $db->insertID();

            // 5. Line items + atomic stock deduction
            foreach ($items as $it) {
                $db->table('bill_items')->insert([
                    'bill_id'      => $billId,
                    'product_id'   => $it['id'],
                    'product_name' => $it['name'],
                    'price'        => $it['price'],
                    'quantity'     => $it['qty'],
                    'line_total'   => $it['total'],
                ]);

                $db->query(
                    "UPDATE products SET stock = stock - ? WHERE id = ? AND status = 'Active' AND stock >= ?",
                    [$it['qty'], $it['id'], $it['qty']]
                );
                if ($db->affectedRows() !== 1) {
                    throw new \DomainException("Stock update failed for '{$it['name']}'. Stock level changed concurrently.");
                }
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return [
            'success'        => true,
            'status'         => 'success',
            'message'        => 'Bill created and stock updated successfully.',
            'bill_id'        => $billId,
            'bill_no'        => $billNumber,
            'date'           => $now,
            'cashier'        => $cashierName,
            'customer'       => $customer,
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'tax'            => $tax,
            'total'          => $total,
            'tender'         => $tender,
            'change'         => $change,
            'payment_method' => $method,
            'items'          => $items,
        ];
    }
}
