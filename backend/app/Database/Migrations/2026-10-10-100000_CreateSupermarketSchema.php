<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Exactly the schema of the original app (database/schema.sql).
 * CREATE TABLE IF NOT EXISTS: safe to run on a database you already created from the old schema.
 */
class CreateSupermarketSchema extends Migration
{
    public function up(): void
    {
        $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

        $this->db->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(50) NOT NULL UNIQUE,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('Admin', 'Manager', 'Cashier', 'Inventory Staff') NOT NULL DEFAULT 'Cashier',
            branch VARCHAR(100) DEFAULT 'Main Branch',
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) {$opts}");

        $this->db->query("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) {$opts}");

        $this->db->query("CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT,
            name VARCHAR(255) NOT NULL,
            barcode VARCHAR(100) NOT NULL UNIQUE,
            sku VARCHAR(100) NOT NULL UNIQUE,
            price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            cost_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            stock INT NOT NULL DEFAULT 0,
            alert_quantity INT NOT NULL DEFAULT 5,
            image_url VARCHAR(255) NULL,
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        ) {$opts}");

        $this->db->query("CREATE TABLE IF NOT EXISTS bills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bill_number VARCHAR(50) NOT NULL UNIQUE,
            cashier_id INT,
            customer_name VARCHAR(100) DEFAULT 'Walk-in Customer',
            subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            discount_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            tax_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            tender_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            change_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
            payment_method ENUM('Cash', 'Card', 'LankaQR') DEFAULT 'Cash',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE SET NULL
        ) {$opts}");

        $this->db->query("CREATE TABLE IF NOT EXISTS bill_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bill_id INT NOT NULL,
            product_id INT,
            product_name VARCHAR(255) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            line_total DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) {$opts}");
    }

    public function down(): void
    {
        foreach (['bill_items', 'bills', 'products', 'categories', 'users'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
