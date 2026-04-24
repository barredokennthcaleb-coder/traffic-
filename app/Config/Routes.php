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

// Admin Routes
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'AdminController::index');
    $routes->get('analytics', 'AdminController::analytics');
    $routes->get('about', 'AdminController::about');
});
