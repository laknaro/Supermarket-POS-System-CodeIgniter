-- Supermarket POS & ERP System Database Schema
-- Database: supermarket_pos

CREATE DATABASE IF NOT EXISTS supermarket_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE supermarket_pos;

-- 1. Users Table (Authentication & Roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Manager', 'Cashier', 'Inventory Staff') NOT NULL DEFAULT 'Cashier',
    branch VARCHAR(100) DEFAULT 'Main Branch',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Products & Inventory Table
CREATE TABLE IF NOT EXISTS products (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bills / Transactions Master Table
CREATE TABLE IF NOT EXISTS bills (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bill Items (Detail line items for each bill)
CREATE TABLE IF NOT EXISTS bill_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    line_total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Seed Data for Testing
-- Default Admin password: 'admin123'
-- Default Cashier password: 'cashier123'
INSERT INTO users (employee_id, username, email, password, role, branch) VALUES 
('EMP001', 'admin', 'admin@supermarket.com', '$2y$10$yAGGrD9KbnBSB6GMrskho.WPlB8uhIVuQaGK9GQW.Hyev12kPWJfW', 'Admin', 'Main Branch'),
('EMP002', 'cashier1', 'cashier1@supermarket.com', '$2y$10$yAGGrD9KbnBSB6GMrskho.WPlB8uhIVuQaGK9GQW.Hyev12kPWJfW', 'Cashier', 'Main Branch')
ON DUPLICATE KEY UPDATE username=VALUES(username), password=VALUES(password);

INSERT INTO categories (id, name, description) VALUES 
(1, 'Beverages', 'Soft drinks, milk, and juices'),
(2, 'Bakery & Snacks', 'Breads, biscuits, buns, and cakes'),
(3, 'Dairy & Refrigerated', 'Butter, cheese, milk powder, and yogurt'),
(4, 'Grocery & Spices', 'Rice, pulses, cooking oils, and condiments'),
(5, 'Household & Cleaning', 'Detergents, soaps, and cleaning items')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO products (category_id, name, barcode, sku, price, cost_price, stock, alert_quantity) VALUES 
(1, 'Coca Cola 400ml', '5449000000996', 'BEV-COC-400', 180.00, 150.00, 50, 10),
(2, 'Fresh White Bread 450g', '4792024000012', 'BAK-BRD-450', 160.00, 130.00, 25, 5),
(4, 'Keeri Samba Rice 5kg', '4792024000029', 'GRO-SAM-005', 1450.00, 1300.00, 40, 8),
(3, 'Anchor Milk Powder 400g', '4791001000034', 'DAI-ANC-400', 1050.00, 920.00, 30, 5),
(2, 'Munchee Cream Cracker 490g', '4792011000041', 'BAK-MUN-490', 380.00, 320.00, 60, 10),
(4, 'Dilmah Black Tea 100 Bags', '4792002000055', 'GRO-DIL-100', 750.00, 620.00, 20, 5),
(5, 'Sunlight Powder 1kg', '4791025000062', 'HOU-SUN-001', 590.00, 490.00, 35, 8),
(2, 'Maliban Gold Marie 300g', '4792033000079', 'BAK-MAL-300', 240.00, 200.00, 45, 10)
ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price), stock=VALUES(stock);