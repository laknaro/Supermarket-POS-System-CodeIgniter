<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Demo data from the original schema.sql. Demo password for every account: admin123
 * (the login page quick-fill buttons use it). Delete / change these before real use.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);

        $users = [
            ['EMP001', 'admin',     'admin@supermarket.com',     'Admin'],
            ['EMP002', 'cashier1',  'cashier1@supermarket.com',  'Cashier'],
            ['EMP003', 'heshani',   'heshani@supermarket.com',   'Cashier'],
            ['EMP004', 'manager',   'manager@supermarket.com',   'Manager'],
            ['EMP005', 'inventory', 'inventory@supermarket.com', 'Inventory Staff'],
        ];
        foreach ($users as [$emp, $name, $email, $role]) {
            $this->db->table('users')->ignore(true)->insert([
                'employee_id' => $emp, 'username' => $name, 'email' => $email,
                'password' => $hash, 'role' => $role, 'branch' => 'Colombo – Head Office', 'status' => 'Active',
            ]);
        }

        $categories = [
            [1, 'Beverages',            'Soft drinks, milk, and juices'],
            [2, 'Bakery & Snacks',      'Breads, biscuits, buns, and cakes'],
            [3, 'Dairy & Refrigerated', 'Butter, cheese, milk powder, and yogurt'],
            [4, 'Grocery & Spices',     'Rice, pulses, cooking oils, and condiments'],
            [5, 'Household & Cleaning', 'Detergents, soaps, and cleaning items'],
        ];
        foreach ($categories as [$id, $name, $desc]) {
            $this->db->table('categories')->ignore(true)->insert(['id' => $id, 'name' => $name, 'description' => $desc]);
        }

        // category, name, barcode, sku, price, cost, stock, alert
        $products = [
            [1, 'Coca Cola 400ml',            '5449000000996', 'BEV-COC-400',  180.00,  150.00, 50, 10],
            [2, 'Fresh White Bread 450g',     '4792024000012', 'BAK-BRD-450',  160.00,  130.00, 25,  5],
            [4, 'Keeri Samba Rice 5kg',       '4792024000029', 'GRO-SAM-005', 1450.00, 1300.00, 40,  8],
            [3, 'Anchor Milk Powder 400g',    '4791001000034', 'DAI-ANC-400', 1050.00,  920.00, 30,  5],
            [2, 'Munchee Cream Cracker 490g', '4792011000041', 'BAK-MUN-490',  380.00,  320.00, 60, 10],
            [4, 'Dilmah Black Tea 100 Bags',  '4792002000055', 'GRO-DIL-100',  750.00,  620.00, 20,  5],
            [5, 'Sunlight Powder 1kg',        '4791025000062', 'HOU-SUN-001',  590.00,  490.00, 35,  8],
            [2, 'Maliban Gold Marie 300g',    '4792033000079', 'BAK-MAL-300',  240.00,  200.00, 45, 10],
        ];
        foreach ($products as [$cat, $name, $barcode, $sku, $price, $cost, $stock, $alert]) {
            $this->db->table('products')->ignore(true)->insert([
                'category_id' => $cat, 'name' => $name, 'barcode' => $barcode, 'sku' => $sku,
                'price' => $price, 'cost_price' => $cost, 'stock' => $stock, 'alert_quantity' => $alert,
            ]);
        }
    }
}
