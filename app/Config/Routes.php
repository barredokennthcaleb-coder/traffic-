<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::login');

// Auth Routes
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::loginPost');
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::registerPost');
$routes->get('logout', 'AuthController::logout');

// User Dashboard Route
$routes->get('user/dashboard', 'UserController::index', ['filter' => 'auth']);
$routes->get('user/pay/(:any)', 'UserController::payViolation/$1', ['filter' => 'auth']);
$routes->post('user/process-payment', 'UserController::processPayment', ['filter' => 'auth']);
$routes->get('user/receipt/(:any)', 'UserController::receipt/$1', ['filter' => 'auth']);

// Officer Routes
$routes->group('officer', ['filter' => 'officer'], function($routes) {
    $routes->get('/', 'Admin\OfficerController::index');
    $routes->post('store', 'Admin\OfficerController::store');
    $routes->get('violations', 'Admin\OfficerController::violations');
});

// Admin Routes
$routes->group('', ['filter' => 'admin'], function($routes) {
    $routes->get('dashboard', 'Admin\AdminController::index');
    $routes->get('analytics', 'Admin\AdminController::analytics');
    $routes->get('about', 'Admin\AdminController::about');
    
    // User CRUD Routes
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/create', 'Admin\UserController::create');
    $routes->post('users/store', 'Admin\UserController::store');
    $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1');
    $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');
    $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1');

    // Penalty & Payment Routes
    $routes->get('penalties', 'Admin\PenaltyController::index');
    $routes->get('penalties/pay/(:num)', 'Admin\PenaltyController::pay/$1');
    $routes->post('penalties/store', 'Admin\PenaltyController::store');
    $routes->get('penalties/history', 'Admin\PenaltyController::history');

    // Violation Types Routes
    $routes->get('violation-types', 'Admin\ViolationTypeController::index');
    $routes->post('violation-types/store', 'Admin\ViolationTypeController::store');
    $routes->post('violation-types/update', 'Admin\ViolationTypeController::update');
    $routes->get('violation-types/delete/(:num)', 'Admin\ViolationTypeController::delete/$1');
});
