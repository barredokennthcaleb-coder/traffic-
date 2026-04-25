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
$routes->get('user/dashboard', 'User\UserController::index', ['filter' => 'auth']);
$routes->get('user/violations', 'User\UserController::violations', ['filter' => 'auth']);
$routes->get('user/history', 'User\UserController::history', ['filter' => 'auth']);
$routes->get('user/pay/(:any)', 'User\UserController::payViolation/$1', ['filter' => 'auth']);
$routes->post('user/process-payment', 'User\UserController::processPayment', ['filter' => 'auth']);
$routes->get('user/receipt/(:any)', 'User\UserController::receipt/$1', ['filter' => 'auth']);

// Officer Routes
$routes->group('officer', ['filter' => 'officer'], function($routes) {
    $routes->get('/', 'Officer\OfficerController::index');
    $routes->post('store', 'Officer\OfficerController::store');
    $routes->get('violations', 'Officer\OfficerController::violations');
    $routes->get('view/(:num)', 'Officer\OfficerController::view/$1');
    $routes->post('cancel/(:num)', 'Officer\OfficerController::cancel/$1');
    $routes->post('violation-types/store', 'Officer\OfficerController::storeViolationType');
});

// Admin Routes
$routes->group('', ['filter' => 'admin'], function($routes) {
    $routes->get('dashboard', 'Admin\AdminController::index');
    $routes->get('analytics', 'Admin\AdminController::analytics');
    $routes->get('about', 'Admin\AdminController::about');
    
    // User CRUD Routes
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/view/(:num)', 'Admin\UserController::viewDriver/$1');
    $routes->get('users/create', 'Admin\UserController::create');
    $routes->post('users/store', 'Admin\UserController::store');
    $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1');
    $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');
    $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1');
    $routes->post('users/reset-password/(:num)', 'Admin\UserController::resetPassword/$1');

    // Penalty & Payment Routes
    $routes->get('penalties', 'Admin\PenaltyController::index');
    $routes->get('penalties/all', 'Admin\PenaltyController::all');
    $routes->get('penalties/view/(:num)', 'Admin\PenaltyController::view/$1');
    $routes->get('penalties/pay/(:num)', 'Admin\PenaltyController::pay/$1');
    $routes->post('penalties/store', 'Admin\PenaltyController::store');
    $routes->post('penalties/cancel/(:num)', 'Admin\PenaltyController::cancel/$1');
    $routes->get('penalties/history', 'Admin\PenaltyController::history');
    $routes->get('penalties/reverse/(:num)', 'Admin\PenaltyController::reverse/$1');
    $routes->get('penalties/search', 'Admin\PenaltyController::search');

    // Violation Types Routes
    $routes->get('violation-types', 'Admin\ViolationTypeController::index');
    $routes->post('violation-types/store', 'Admin\ViolationTypeController::store');
    $routes->post('violation-types/update', 'Admin\ViolationTypeController::update');
    $routes->get('violation-types/delete/(:num)', 'Admin\ViolationTypeController::delete/$1');
});
