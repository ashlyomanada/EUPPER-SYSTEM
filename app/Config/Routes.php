<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'MainController::index');

$routes->get('/getUsers', 'AdminController::getUsers');
$routes->get('/getUsersInfo', 'AdminController::getUsersInfo');
$routes->get('/getAdmins', 'AdminController::getAdmins');
$routes->get('/getPpo', 'AdminController::getPpo');
$routes->post('/generate', 'AdminController::generateExcel');
//$routes->get('/generatePdf', 'AdminController::generatePdf');
// Update your routes
$routes->get('generatePdf/(:any)/(:any)', 'AdminController::generatePdf/$1/$2');

$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {
   // $routes->post('updateAdmin/(:num)', 'AdminController::updateAdmin/$1');
    $routes->post('saveUserRates', 'AdminController::saveUserRates');
    $routes->post('saveAdmin', 'AdminController::saveAdmin');
    $routes->post('saveUser', 'AdminController::saveUser');
    $routes->post('saveProfile', 'MainController::saveProfile');
    
});
$routes->post('/toggleUserStatus', 'AdminController::toggleUserStatus');
$routes->post('/changeAllUserStatus', 'AdminController::changeAllUserStatus');
$routes->get('/filterUsers/(:any)', 'UserController::filterUsers/$1');
$routes->get('findData/(:num)/(:num)', 'AdminController::findData/$1/$2');

$routes->post('/login', 'MainController::login');
$routes->post('/loginAdmin', 'AdminController::loginAdmin');
$routes->post('/upload', 'MainController::upload');
$routes->post('/sendEmail', 'MainController::sendEmail');
$routes->get('/getUserData/(:num)', 'MainController::getUserData/$1');
$routes->get('/getUserRatings', 'MainController::getUserRatings');

$routes->post('/insertRating', 'MainController::insertRating');
$routes->get('/viewUserRatings/(:num)', 'MainController::viewUserRatings/$1');
$routes->get('/countUserRatings', 'AdminController::countUserRatings');
$routes->get('/countUser', 'AdminController::countUser');
$routes->get('/calculateRatings/(:num)', 'AdminController::calculateRatings/$1');