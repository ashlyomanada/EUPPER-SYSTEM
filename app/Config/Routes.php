<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'MainController::index');

$routes->get('/getUsers', 'AdminController::getUsers');
$routes->get('/getUsersInfo', 'AdminController::getUsersInfo');
$routes->get('/getAdmins', 'AdminController::getAdmins');
$routes->get('/getUsersOffice', 'AdminController::getUsersOffice');
$routes->get('/getUsersRatePPO', 'AdminController::getUsersRatePPO');
$routes->get('/getUsersRateRMFB', 'AdminController::getUsersRateRMFB');
$routes->get('/getUsersRateOcci', 'AdminController::getUsersRateOcci');
$routes->get('/getUsersRateOrmin', 'AdminController::getUsersRateOrmin');
$routes->get('/getUsersRateMarin', 'AdminController::getUsersRateMarin');
$routes->get('/getUsersRateRom', 'AdminController::getUsersRateRom');
$routes->get('/getUsersRatePal', 'AdminController::getUsersRatePal');
$routes->get('/getUsersRatePuer', 'AdminController::getUsersRatePuer');

$routes->get('/getAllUsersName', 'AdminController::getAllUsersName');

$routes->post('/getColumnsForOffice', 'AdminController::getColumnsForOffice');

$routes->post('/addColumnPPO', 'AdminController::addColumnPPO');
$routes->post('/addColumnRMFB', 'AdminController::addColumnRMFB');
$routes->post('/addColumnOcci', 'AdminController::addColumnOcci');
$routes->post('/addColumnOrmin', 'AdminController::addColumnOrmin');
$routes->post('/addColumnRom', 'AdminController::addColumnRom');
$routes->post('/addColumnMar', 'AdminController::addColumnMar');
$routes->post('/addColumnPal', 'AdminController::addColumnPal');
$routes->post('/addColumnPuer', 'AdminController::addColumnPuer');

$routes->get('getColumnNamePPO', 'UserController::getColumnNamePPO');
$routes->get('getColumnNameRMFB', 'UserController::getColumnNameRMFB');
$routes->get('getColumnNameOcci', 'UserController::getColumnNameOcci');
$routes->get('getColumnNameOrmin', 'UserController::getColumnNameOrmin');
$routes->get('getColumnNameRom', 'UserController::getColumnNameRom');
$routes->get('getColumnNameMar', 'UserController::getColumnNameMar');
$routes->get('getColumnNamePal', 'UserController::getColumnNamePal');
$routes->get('getColumnNamePuer', 'UserController::getColumnNamePuer');

$routes->post('/getUsersRateByMonth', 'AdminController::getUsersRateByMonth');
$routes->post('/getUsersRateByOffice', 'AdminController::getUsersRateByOffice');

$routes->post('insertDataPPO', 'UserController::insertDataPPO');
$routes->post('insertDataRMFB', 'UserController::insertDataRMFB');
$routes->post('insertDataOcci', 'UserController::insertDataOcci');
$routes->post('insertDataOrmin', 'UserController::insertDataOrmin');
$routes->post('insertDataRom', 'UserController::insertDataRom');
$routes->post('insertDataMar', 'UserController::insertDataMar');
$routes->post('insertDataPal', 'UserController::insertDataPal');
$routes->post('insertDataPuer', 'UserController::insertDataPuer');

$routes->post('generatePPOOffice', 'AdminController::generatePPOOffice');
$routes->post('generateRMFBOffice', 'AdminController::generateRMFBOffice');
$routes->post('generateOcciReport', 'AdminController::generateOcciReport');
$routes->post('generateOrminReport', 'AdminController::generateOrminReport');
$routes->post('generateMarinReport', 'AdminController::generateMarinReport');
$routes->post('generateRomReport', 'AdminController::generateRomReport');
$routes->post('generatePalReport', 'AdminController::generatePalReport');
$routes->post('generatePuerReport', 'AdminController::generatePuerReport');

$routes->get('createDynamicTable', 'AdminController::createDynamicTable');
$routes->post('sendPasswordResetEmail', 'UserController::sendPasswordResetEmail');
$routes->post('resetPassword', 'UserController::resetPassword');

$routes->get('/getAllRates', 'MainController::getAllRates');
$routes->post('/updateUserRating', 'MainController::updateUserRating');
$routes->post('/sendSMS', 'MainController::sendSMS');

$routes->post('getColumnNamePerTbl', 'MainController::getColumnNamePerTbl');
$routes->post('/viewUserByTblRates', 'MainController::viewUserByTblRates');

$routes->get('generatePdf/(:any)/(:any)', 'AdminController::generatePdf/$1/$2');

$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {
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
$routes->get('/getUserAdmin/(:num)', 'MainController::getUserAdmin/$1');
$routes->get('/getUserRatings', 'MainController::getUserRatings');

$routes->post('/insertRating', 'MainController::insertRating');
$routes->post('/insertMps', 'MainController::insertMps');

$routes->get('/viewUserRatings/(:num)', 'MainController::viewUserRatings/$1');
$routes->get('/viewUserPPO/(:num)', 'MainController::viewUserPPO/$1');
$routes->get('/countUserRatings', 'AdminController::countUserRatings');
$routes->get('/countUser', 'AdminController::countUser');
$routes->get('/calculateRatings/(:num)', 'AdminController::calculateRatings/$1');

$routes->get('/viewUserPPORates/(:num)', 'UserController::viewUserPPORates/$1');
$routes->get('/viewUserRMFBRates/(:num)', 'UserController::viewUserRMFBRates/$1');

$routes->get('/viewUserOcciRates/(:num)', 'UserController::viewUserOcciRates/$1');
$routes->get('/viewUserOrienRates/(:num)', 'UserController::viewUserOrienRates/$1');
$routes->get('/viewUserMarinRates/(:num)', 'UserController::viewUserMarinRates/$1');
$routes->get('/viewUserRombRates/(:num)', 'UserController::viewUserRombRates/$1');
$routes->get('/viewUserPalRates/(:num)', 'UserController::viewUserPalRates/$1');
$routes->get('/viewUserPuertoRates/(:num)', 'UserController::viewUserPuertoRates/$1');

$routes->get('/getRmfbRates', 'AdminController::getRmfbRates');
$routes->get('/getOcciRates', 'AdminController::getOcciRates');
$routes->get('/getOrienRates', 'AdminController::getOrienRates');
$routes->get('/getMarinRates', 'AdminController::getMarinRates');
$routes->get('/getRomRates', 'AdminController::getRomRates');
$routes->get('/getPalRates', 'AdminController::getPalRates');
$routes->get('/getPuertRates', 'AdminController::getPuertRates');

$routes->post('/updateAdminInformation', 'AdminController::updateAdminInformation');
$routes->post('/generateRmfbExcel', 'AdminController::generateRmfbExcel');
$routes->post('/generateReport', 'AdminController::generateReport');
$routes->post('/getFilterRmfbRates', 'AdminController::getFilterRmfbRates');

$routes->post('/generateRmfbReport', 'AdminController::generateRmfbReport');

$routes->post('/insertRmfb', 'InsertController::insertRmfb');
$routes->post('/insertMpsOcci', 'InsertController::insertMpsOcci');
$routes->get('/viewRmfbRates', 'InsertController::viewRmfbRates');
$routes->get('/userRmfbRates/(:num)', 'InsertController::userRmfbRates/$1');
$routes->get('viewMpsOcciRates', 'InsertController::viewMpsOcciRates');
$routes->get('/userMpsOcciRates/(:num)', 'InsertController::userMpsOcciRates/$1');
$routes->post('/saveRmfbUserRates', 'InsertController::saveRmfbUserRates');