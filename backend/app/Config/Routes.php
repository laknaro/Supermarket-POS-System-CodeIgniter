<?php

use CodeIgniter\Router\RouteCollection;

/**
 * URLs are kept identical to the original plain-PHP app (pos.php, products.php ...)
 * so the original frontend files work without any change.
 *
 * @var RouteCollection $routes
 */

// Login page + logout (?action=logout). index.php posts to itself.
$routes->match(['get', 'post'], '/', 'Auth::index');
$routes->match(['get', 'post'], 'index.php', 'Auth::index');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    // Who may open what (same visibility rules as the original sidebar).
    $mgmt  = 'role:Admin,Manager';
    $till  = 'role:Admin,Manager,Cashier';
    $stock = 'role:Admin,Manager,Inventory Staff';

    $routes->get('dashboard.php', 'Dashboard::index', ['filter' => $mgmt]);
    $routes->get('pos.php', 'Pos::index', ['filter' => $till]);

    // AJAX endpoints used by assets/js/pos.js
    $routes->get('api/get_products.php', 'Api\Products::index', ['filter' => $till]);
    $routes->post('api/save_bill.php', 'Api\Bills::store', ['filter' => $till]);

    $routes->match(['get', 'post'], 'products.php', 'Products::index', ['filter' => $stock]);
    $routes->match(['get', 'post'], 'categories.php', 'Categories::index', ['filter' => $stock]);
    $routes->match(['get', 'post'], 'inventory.php', 'Inventory::index', ['filter' => $stock]);

    $routes->get('bill_history.php', 'BillHistory::index', ['filter' => $till]);
    $routes->get('reports.php', 'Reports::index', ['filter' => $stock]);

    $routes->match(['get', 'post'], 'users.php', 'Users::index', ['filter' => $mgmt]);
});
