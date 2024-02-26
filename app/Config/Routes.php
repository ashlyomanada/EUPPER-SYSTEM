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
$routes->post('/insertMps', 'MainController::insertMps');

$routes->get('/viewUserRatings/(:num)', 'MainController::viewUserRatings/$1');
$routes->get('/viewUserPPO/(:num)', 'MainController::viewUserPPO/$1');
$routes->get('/countUserRatings', 'AdminController::countUserRatings');
$routes->get('/countUser', 'AdminController::countUser');
$routes->get('/calculateRatings/(:num)', 'AdminController::calculateRatings/$1');

$routes->post('/savePPORate', 'UserController::savePPORate');
$routes->post('/saveRMFBRate', 'UserController::saveRMFBRate');
$routes->post('/saveMunOcciRate', 'UserController::saveMunOcciRate');
$routes->post('/saveMunOrientalRate', 'UserController::saveMunOrientalRate');
$routes->post('/saveMunPalawanRate', 'UserController::saveMunPalawanRate');
$routes->post('/saveMunMarinduqueRate', 'UserController::saveMunMarinduqueRate');
$routes->post('/saveMunRomblonRate', 'UserController::saveMunRomblonRate');
$routes->post('/saveMunPuertoRate', 'UserController::saveMunPuertoRate');

$routes->get('/viewUserPPORates/(:num)', 'UserController::viewUserPPORates/$1');
$routes->get('/viewUserRMFBRates/(:num)', 'UserController::viewUserRMFBRates/$1');

$routes->get('/viewUserOcciRates/(:num)', 'UserController::viewUserOcciRates/$1');
$routes->get('/viewUserOrienRates/(:num)', 'UserController::viewUserOrienRates/$1');
$routes->get('/viewUserMarinRates/(:num)', 'UserController::viewUserMarinRates/$1');
$routes->get('/viewUserRombRates/(:num)', 'UserController::viewUserRombRates/$1');
$routes->get('/viewUserPalRates/(:num)', 'UserController::viewUserPalRates/$1');
$routes->get('/viewUserPuertoRates/(:num)', 'UserController::viewUserPuertoRates/$1');