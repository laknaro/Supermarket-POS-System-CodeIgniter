# EGOTECH WORLD SUPERMART: POS & ERP (CodeIgniter 4)

```
supermarket-pos-ci4/
├── backend/    PHP (CodeIgniter 4): controllers, models, filters, routes, migrations, .env
└── frontend/   the ORIGINAL pages + assets, unchanged
    ├── views/  dashboard.php, pos.php, products.php ... includes/sidebar.php   (the original HTML)
    └── public/ web root: index.php (entry point), assets/css, assets/js/pos.js ...
```

## Run it (VS Code terminal)

Needs: PHP 8.2+ (with `intl`, `mbstring`, `mysqli` enabled), Composer, MySQL (XAMPP is fine).
XAMPP: in `php.ini` remove the `;` before `extension=intl`.

```bash
cd backend
composer install
mysql -u root -e "CREATE DATABASE supermarket_pos CHARACTER SET utf8mb4"
php spark migrate
php spark db:seed DatabaseSeeder
php spark serve
```
Open **http://localhost:8080**

* Windows + XAMPP, `mysql` is not in PATH: create the empty database `supermarket_pos` in phpMyAdmin instead of the `mysql ...` line.
* DB password / user: edit `backend/.env`.
* Already have the old database? Skip `migrate` and `db:seed`, just point `.env` at it (same tables).
* Later runs only need: `cd backend` then `php spark serve`.
* VS Code: **Terminal > Run Task** has the same three steps.

### Demo logins (password for all: `admin123`)
`admin` · `manager` · `cashier1` · `heshani` · `inventory`  (the login page quick-fill buttons use these)

### XAMPP Apache instead of `php spark serve`
Point the Apache DocumentRoot to `frontend/public` and set `app.baseURL` in `backend/.env` to that URL.

## What was (and was not) changed
* **Frontend:** the original templates/JS/CSS. Only one line per page differs: the sidebar `include` became `$this->include(...)`.
  `pos.js`, `instant_search.js`, `style.css` are byte-identical. URLs are the same (`pos.php`, `products.php`, `api/save_bill.php` ...).
* **Backend:** rewritten as MVC. The page variables and JSON responses are the same as before.
  CSRF tokens are added to the HTML/`fetch()` by the backend (`CsrfInjectFilter`), so no frontend file had to be edited.

### Backend behaviour that is different on purpose
1. Removed from login: the password `admin123` opening **any** account, and automatic creation of admin accounts.
2. Checkout (`api/save_bill.php`) is one transaction; prices and tax come from the database, not the browser; stock can't go negative.
3. "Delete" product / user = archive / deactivate, so old bills and receipts keep working (the old code deleted the bill items too).
4. Page access follows what the sidebar shows: Cashier = POS + Bill History, Inventory Staff = Products/Categories/Inventory/Reports,
   Admin + Manager = everything. Only an Admin can create/modify Admin accounts; the last Admin can't be removed.
   (Change the roles per page in `backend/app/Config/Routes.php`.)
5. `cost_price` and password hashes are no longer sent to the browser; login is throttled (10 tries/minute); the delete link only works from this site.

## Git
```bash
git init -b main
git add . && git commit -m "CodeIgniter 4 backend + original frontend"
```
`.env`, `vendor/`, logs and uploaded product images are git-ignored.
