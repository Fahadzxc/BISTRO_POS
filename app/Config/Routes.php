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
    $routes->get('dashboard', 'Auth::dashboard');
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

    // Role-restricted: admin only
    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        // $routes->get('users', 'Users::index');
        // $routes->get('users/(:segment)', 'Users::$1');
    });

    // Role-restricted: admin, cashier (staff cannot access inventory)
    $routes->group('', ['filter' => 'role:admin,cashier'], static function ($routes) {
        $routes->get('inventory', 'Inventory::index');
        $routes->get('inventory/list', 'Inventory::list');
        $routes->post('inventory/adjust', 'Inventory::adjust');
        $routes->post('inventory/update-min-stock', 'Inventory::updateMinStock');
        $routes->get('inventory/logs', 'Inventory::logs');
    });
});
