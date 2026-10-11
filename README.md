# 🛒 EGOTECH World Supermart - POS & ERP System

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4-EF4223?style=for-the-badge&logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Architecture](https://img.shields.io/badge/Architecture-MVC%20RESTful-2ea44f?style=for-the-badge)](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

A robust, enterprise-grade **Supermarket Point of Sale (POS) and Enterprise Resource Planning (ERP)** web application built with **CodeIgniter 4**, **PHP 8.2+**, and **MySQL**.

This system delivers an end-to-end retail management experience featuring high-speed cashier checkout, barcode lookup, automated stock deductions, multi-tender transactions (Cash, Card, LankaQR), real-time stock alert mechanisms, comprehensive analytics, and granular Role-Based Access Control (RBAC).

---

## 📑 Table of Contents

- [Overview & Architecture](#-overview--architecture)
- [Key Features](#-key-features)
  - [1. Point of Sale (POS) Billing](#1-point-of-sale-pos-billing)
  - [2. Inventory & Stock Control](#2-inventory--stock-control)
  - [3. Products & Category Management](#3-products--category-management)
  - [4. Sales History & Receipt Printing](#4-sales-history--receipt-printing)
  - [5. Reporting & Analytics](#5-reporting--analytics)
  - [6. User & Staff Management (RBAC)](#6-user--staff-management-rbac)
  - [7. Security & Integrity](#7-security--integrity)
- [System Architecture & Directory Structure](#-system-architecture--directory-structure)
- [Requirements](#-requirements)
- [Installation & Setup Guide](#-installation--setup-guide)
  - [Option A: Development Server (CLI / Spark)](#option-a-development-server-cli--spark)
  - [Option B: XAMPP / Apache Deployment](#option-b-xampp--apache-deployment)
- [Default Demo Credentials](#-default-demo-credentials)
- [Database Schema](#-database-schema)
- [Security Hardening & Highlights](#-security-hardening--highlights)
- [Contributing & License](#-contributing--license)

---

## 🔍 Overview & Architecture

The system is organized into a clean decoupled MVC pattern:
- **Backend**: **CodeIgniter 4** (Controllers, Models, Database Migrations, Database Seeders, and Security Filters).
- **Frontend**: High-performance, lightweight UI with instant live search, barcode keyboard emulation support, asynchronous REST API communication (`fetch`), and responsive layouts.
- **Atomic Operations**: Financial checkouts execute within strict database transactions (`TRANS_START` / `TRANS_COMPLETE`) ensuring zero stock anomalies or partial billing records.

---

## ✨ Key Features

### 1. Point of Sale (POS) Billing
- **High-Speed Checkout Engine**: Optimized for fast-paced retail supermarket environments.
- **Instant Product Search & Barcode Lookup**: Search items by name, barcode, or SKU with live keyboard navigation.
- **Cart Calculations**: Real-time computation of subtotal, configurable tax, item discounts, total amount, tender amount, and change due.
- **Multi-Payment Support**: Accepts **Cash**, **Credit/Debit Cards**, and **LankaQR** digital payments.
- **Stock Guard**: Prevents selling out-of-stock items or exceeding on-hand quantities during checkout.
- **Receipt Modal & Thermal Print**: Instant receipt preview with formatted print styles for POS receipt printers.

### 2. Inventory & Stock Control
- **Real-Time Stock Tracking**: Immediate updates to product quantity upon every transaction.
- **Low Stock Warning Alerts**: Automated indicators triggered when inventory levels fall below specified `alert_quantity` thresholds.
- **Stock Status Flags**: Clear visual cues for in-stock, low-stock, and out-of-stock products.

### 3. Products & Category Management
- **Complete Product Catalog**: Manage barcodes, SKUs, product names, categories, selling prices, and cost prices.
- **Category Organization**: Group items by department (e.g., Beverages, Bakery, Dairy, Grocery, Household).
- **Safe Archival (Soft Delete)**: Product archival mechanism that preserves historical billing links and financial audit trails.
- **Image Upload Support**: Associate product pictures for visual grid selection.

### 4. Sales History & Receipt Printing
- **Full Transaction Ledger**: Searchable historical list of all processed bills.
- **Detailed Invoice Inspection**: View cashier details, timestamp, item breakdowns, pricing, payment method, and tendered amounts.
- **Reprint Capability**: Re-issue receipts at any time for customer returns or verification.

### 5. Reporting & Analytics
- **Executive Dashboard**: Key Performance Indicators (KPIs) including total daily sales, monthly revenue, total products, and low stock counters.
- **Profit & Margin Analytics**: Built-in calculation of gross profits based on Cost vs. Retail price.
- **Sales Trends**: Periodic sales breakdown (Today, This Week, This Month).

### 6. User & Staff Management (RBAC)
Granular access control based on user roles:
- **Admin**: Full administrative permissions including system configuration, user creation, and sensitive reports.
- **Manager**: Store-level access for reports, inventory, category updates, and POS operations.
- **Cashier**: Restricted to the POS Terminal and Bill History.
- **Inventory Staff**: Restricted to Products, Categories, Stock, and Inventory Reports.
- **Self-Protection Guard**: Prevents accidental deletion of the last remaining Administrator account.

### 7. Security & Integrity
- **CSRF Injection Filter**: Automatically safeguards POST requests without breaking front-end scripts.
- **Server-Side Price Validation**: Item unit prices and totals are verified against database records, preventing front-end tampering.
- **Rate-Limited Authentication**: Brute-force protection for user logins (10 attempts/minute limit).
- **Password Protection**: Passwords securely hashed using modern `BCRYPT`.

---

## 📂 System Architecture & Directory Structure

```
supermarket-pos-ci4/
├── backend/                              # CodeIgniter 4 Core Application
│   ├── app/
│   │   ├── Config/                       # Routes, Database, Filters, App configuration
│   │   │   ├── Routes.php                # URL mapping and RBAC access definitions
│   │   │   ├── Database.php              # Connection configuration
│   │   │   └── Filters.php               # Auth, CSRF, and Role filters
│   │   ├── Controllers/
│   │   │   ├── Api/                      # REST endpoints (Products search, Save Bill)
│   │   │   ├── Auth.php                  # Authentication & Session control
│   │   │   ├── Dashboard.php             # Executive metrics & charts
│   │   │   ├── Pos.php                   # POS Terminal controller
│   │   │   ├── Products.php              # Product catalog operations
│   │   │   ├── Categories.php            # Category management
│   │   │   ├── Inventory.php             # Stock adjustment & alerts
│   │   │   ├── BillHistory.php           # Transaction history
│   │   │   ├── Reports.php               # Sales & revenue reports
│   │   │   └── Users.php                 # Employee & role management
│   │   ├── Database/
│   │   │   ├── Migrations/               # Database schema definition
│   │   │   └── Seeds/                    # Default demo seeds (DatabaseSeeder)
│   │   ├── Filters/                      # Security & access interception filters
│   │   └── Models/                       # CI4 Models (ProductModel, BillModel, UserModel, etc.)
│   ├── database/                         # Standalone SQL schema definitions
│   ├── spark                             # CodeIgniter CLI tool
│   ├── composer.json                     # Backend PHP dependencies
│   └── env                               # Environment template file
│
├── frontend/                             # User Interface Layer
│   ├── public/                           # Web Root / Public Entry Point
│   │   ├── assets/
│   │   │   ├── css/style.css             # Main styling & responsive POS layouts
│   │   │   └── js/
│   │   │       ├── pos.js                # Core cart logic, keyboard shortcuts & checkout
│   │   │       └── instant_search.js     # Real-time search engine
│   │   └── index.php                     # Frontend bootstrap & request dispatcher
│   └── views/                            # PHP / HTML View templates
│       ├── includes/                     # Reusable header, footer, and sidebar navigation
│       ├── dashboard.php                 # Analytics dashboard
│       ├── pos.php                       # Point of Sale terminal interface
│       ├── products.php                  # Products directory view
│       ├── categories.php                # Categories view
│       ├── inventory.php                 # Inventory view
│       ├── bill_history.php              # Bill audit trail view
│       ├── reports.php                   # Financial reports view
│       └── users.php                     # Staff management view
│
├── .gitignore                            # Git exclusion rules
└── README.md                             # Comprehensive project documentation
```

---

## ⚙️ Requirements

- **PHP**: `8.2` or higher
  - Required PHP Extensions: `intl`, `mbstring`, `mysqli`, `curl`, `json`
- **Database**: MySQL `5.7+` or MariaDB `10.4+`
- **Dependency Manager**: [Composer](https://getcomposer.org/)
- **Web Server**: Apache / Nginx or CodeIgniter's built-in Spark CLI server

---

## 🚀 Installation & Setup Guide

### 1. Clone the Repository
```bash
git clone https://github.com/laknaro/Supermarket-POS-System-CodeIgniter.git
cd Supermarket-POS-System-CodeIgniter
```

### 2. Configure Environment (`.env`)
Navigate to the `backend/` directory and create the `.env` file from `env`:
```bash
cd backend
cp env .env
```
Open `backend/.env` in your text editor and adjust your database parameters:
```ini
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = supermarket_pos
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port     = 3306

app.baseURL = 'http://localhost:8080/'
```

---

### Option A: Development Server (CLI / Spark)

1. **Install Composer Dependencies**:
   ```bash
   cd backend
   composer install
   ```

2. **Create Database & Run Migrations**:
   Using MySQL CLI:
   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS supermarket_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
   *(Or create a database named `supermarket_pos` inside phpMyAdmin)*

3. **Run Migrations and Seed Demo Data**:
   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

4. **Start the Application Server**:
   ```bash
   php spark serve
   ```
   Open your browser and navigate to: **[http://localhost:8080](http://localhost:8080)**

---

### Option B: XAMPP / Apache Deployment

1. Place the project folder inside your XAMPP web root:
   ```
   C:\xampp\htdocs\supermarket-pos-ci4
   ```
2. In `C:\xampp\php\php.ini`, verify that the `intl` extension is active (remove the semicolon `;` before `extension=intl`).
3. Create the database `supermarket_pos` in [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
4. Run migrations and seed data using terminal or import `backend/database/schema_original.sql`.
5. Point your VirtualHost or Apache `DocumentRoot` to:
   ```
   C:/xampp/htdocs/supermarket-pos-ci4/frontend/public
   ```
6. Update `app.baseURL` in `backend/.env` to match your local Apache virtual host URL.

---

## 🔑 Default Demo Credentials

All seed accounts are initialized with the default password: **`admin123`**

| Role | Username | Email | Access Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin@supermarket.com` | Complete System Access & Staff Controls |
| **Manager** | `manager` | `manager@supermarket.com` | POS, Reports, Products, Categories, Stock |
| **Cashier** | `cashier1` | `cashier1@supermarket.com` | POS Checkout & Bill History |
| **Cashier** | `heshani` | `heshani@supermarket.com` | POS Checkout & Bill History |
| **Inventory Staff** | `inventory` | `inventory@supermarket.com` | Product Catalog, Categories, Stock & Inventory Reports |

> ⚠️ **Important**: For production deployments, change these passwords and remove demo data before going live.

---

## 🗄️ Database Schema

The database consists of five interconnected tables with enforced foreign key constraints:

- **`users`**: User profiles, employee IDs, hashed passwords, roles (`Admin`, `Manager`, `Cashier`, `Inventory Staff`), branch assignment, and active status.
- **`categories`**: Product classifications and descriptions.
- **`products`**: Stock records, barcodes, SKUs, selling price, cost price, current stock, alert quantities, and image references.
- **`bills`**: Master transaction records containing invoice numbers, cashier ID, customer name, payment method (`Cash`, `Card`, `LankaQR`), subtotal, discounts, tax, and tender/change amounts.
- **`bill_items`**: Line items linked to each bill containing product reference, unit price, quantity, and line total.

---

## 🔒 Security Hardening & Highlights

- **SQL Injection Defense**: Built entirely on CodeIgniter 4's Query Builder and parameterized prepared statements.
- **Strict Role-Based Routing**: Route-level middleware (`RoleFilter`) intercepts unauthorized URL requests and displays access error notices.
- **Transaction Safety**: Checkout operations utilize database locks to prevent race conditions during high-volume sales.
- **Client Sanitization**: All HTML view outputs are safely escaped to protect against Cross-Site Scripting (XSS).
- **Data Protection**: Sensitive financial calculations (`cost_price`, supplier margins, password hashes) are never exposed via public client API endpoints.

---

## 🤝 Contributing & License

Contributions, bug reports, and feature requests are welcome!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

This project is licensed under the [MIT License](backend/LICENSE).

---

<p align="center">
  <b>Developed for Modern Retail Management</b><br>
  Built with ❤️ using CodeIgniter 4 & PHP
</p>
