<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');

// Auth routes (no auth filter)
$routes->get('login', 'Auth::login');
$routes->post('authenticate', 'Auth::authenticate');
$routes->get('logout', 'Auth::logout');

// Protected routes - require login
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/stats', 'Dashboard::stats');

    $routes->get('pos', 'Pos::index');
    $routes->get('pos/get-products', 'Pos::getProducts');
    $routes->post('pos/add-to-cart', 'Pos::addToCart');
    $routes->post('pos/update-cart', 'Pos::updateCart');
    $routes->post('pos/remove-from-cart', 'Pos::removeFromCart');
    $routes->post('pos/get-cart', 'Pos::getCart');
    $routes->post('pos/checkout', 'Pos::checkout');
    $routes->get('ktv-rooms', 'KtvRooms::index');
    $routes->get('ktv-rooms/get-rooms', 'KtvRooms::getRooms');
    $routes->post('ktv-rooms/start', 'KtvRooms::start');
    $routes->post('ktv-rooms/pause', 'KtvRooms::pause');
    $routes->post('ktv-rooms/resume', 'KtvRooms::resume');
    $routes->post('ktv-rooms/end', 'KtvRooms::end');
    $routes->post('ktv-rooms/set-available', 'KtvRooms::setAvailable');

    // Role-restricted: admin only (reports, categories, products)
    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        $routes->get('categories', 'Category::index');
        $routes->get('categories/create', 'Category::create');
        $routes->post('categories/store', 'Category::store');
        $routes->get('categories/edit/(:num)', 'Category::edit/$1');
        $routes->post('categories/update/(:num)', 'Category::update/$1');
        $routes->get('categories/delete/(:num)', 'Category::delete/$1');

        $routes->get('products', 'Product::index');
        $routes->get('products/create', 'Product::create');
        $routes->post('products/store', 'Product::store');
        $routes->get('products/edit/(:num)', 'Product::edit/$1');
        $routes->post('products/update/(:num)', 'Product::update/$1');
        $routes->get('products/delete/(:num)', 'Product::delete/$1');

        $routes->get('reports/sales', 'Reports::sales');
        $routes->get('reports/sales/data', 'Reports::salesData');
        $routes->get('reports/ktv', 'Reports::ktv');
        $routes->get('reports/ktv/data', 'Reports::ktvData');
        $routes->get('reports/inventory', 'Reports::inventory');
        $routes->get('reports/inventory/data', 'Reports::inventoryData');

        $routes->get('users', 'Users::index');
        $routes->get('users/create', 'Users::create');
        $routes->post('users/store', 'Users::store');
        $routes->get('users/edit/(:num)', 'Users::edit/$1');
        $routes->post('users/update/(:num)', 'Users::update/$1');
        $routes->get('users/disable/(:num)', 'Users::disable/$1');
        $routes->get('users/delete/(:num)', 'Users::delete/$1');
    });

    // Role-restricted: admin, cashier (staff cannot access inventory)
    $routes->group('', ['filter' => 'role:admin,cashier'], static function ($routes) {
        $routes->get('inventory', 'Inventory::index');
        $routes->get('inventory/list', 'Inventory::list');
        $routes->post('inventory/adjust', 'Inventory::adjust');
        $routes->post('inventory/update-min-stock', 'Inventory::updateMinStock');
        $routes->get('inventory/logs', 'Inventory::logs');

        $routes->get('orders', 'Orders::index');
        $routes->get('orders/view/(:num)', 'Orders::view/$1');
        $routes->get('orders/print/(:num)', 'Orders::print/$1');
    });
});
