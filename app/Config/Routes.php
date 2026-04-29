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

// Officer Routes
$routes->group('officer', ['filter' => 'officer'], function($routes) {
    $routes->get('/', 'Officer\OfficerController::index');
    $routes->get('profile', 'Officer\OfficerController::profile');
    $routes->post('store', 'Officer\OfficerController::store');
    $routes->get('violations', 'Officer\OfficerController::violations');
    $routes->get('view/(:num)', 'Officer\OfficerController::view/$1');
    $routes->post('update/(:num)', 'Officer\OfficerController::update/$1');
    $routes->post('delete/(:num)', 'Officer\OfficerController::delete/$1');
    $routes->post('cancel/(:num)', 'Officer\OfficerController::cancel/$1');
    $routes->post('violation-types/store', 'Officer\OfficerController::storeViolationType');
});

// Driver/User Routes
$routes->group('user', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'User\UserController::index');
    $routes->get('violations', 'User\UserController::violations');
    $routes->get('history', 'User\UserController::history');
    $routes->get('view/(:segment)', 'User\UserController::viewViolation/$1');
    $routes->get('pay/(:segment)', 'User\UserController::payViolation/$1');
    $routes->post('pay/process', 'User\UserController::processPayment');
    $routes->get('receipt/(:segment)', 'User\UserController::receipt/$1');
});

// Admin Routes
$routes->group('', ['filter' => 'admin'], function($routes) {
    $routes->get('dashboard', 'Admin\AdminController::index');
    
    // User CRUD Routes
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/view/(:num)', 'Admin\UserController::viewDriver/$1');
    $routes->get('users/view-enforcer/(:num)', 'Admin\UserController::viewEnforcer/$1');
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
    $routes->post('penalties/delete/(:num)', 'Admin\PenaltyController::delete/$1');
    $routes->get('penalties/history', 'Admin\PenaltyController::history');
    $routes->get('penalties/reverse/(:num)', 'Admin\PenaltyController::reverse/$1');
    $routes->get('penalties/search', 'Admin\PenaltyController::search');

    // Violation Types Routes
    $routes->get('violation-types', 'Admin\ViolationTypeController::index');
    $routes->post('violation-types/store', 'Admin\ViolationTypeController::store');
    $routes->post('violation-types/update', 'Admin\ViolationTypeController::update');
    $routes->get('violation-types/delete/(:num)', 'Admin\ViolationTypeController::delete/$1');
});
