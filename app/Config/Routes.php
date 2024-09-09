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
$routes->post('/getUsersRatePPO', 'AdminController::getUsersRatePPO');
$routes->post('/getUsersRateRMFB', 'AdminController::getUsersRateRMFB');
$routes->post('/getUsersRateOcci', 'AdminController::getUsersRateOcci');
$routes->post('/getUsersRateOrmin', 'AdminController::getUsersRateOrmin');
$routes->post('/getUsersRateMarin', 'AdminController::getUsersRateMarin');
$routes->post('/getUsersRateRom', 'AdminController::getUsersRateRom');
$routes->post('/getUsersRatePal', 'AdminController::getUsersRatePal');
$routes->post('/getUsersRatePuer', 'AdminController::getUsersRatePuer');
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
$routes->post('/getUsersRatePPOByMonth', 'AdminController::getUsersRatePPOByMonth');
$routes->post('/getUsersRateRMFBByMonth', 'AdminController::getUsersRateRMFBByMonth');
$routes->post('/getUsersRateOcciByMonth', 'AdminController::getUsersRateOcciByMonth');
$routes->post('/getUsersRateOrminByMonth', 'AdminController::getUsersRateOrminByMonth');
$routes->post('/getUsersRateMarinByMonth', 'AdminController::getUsersRateMarinByMonth');
$routes->post('/getUsersRateRomByMonth', 'AdminController::getUsersRateRomByMonth');
$routes->post('/getUsersRatePalByMonth', 'AdminController::getUsersRatePalByMonth');
$routes->post('/getUsersRatePuerByMonth', 'AdminController::getUsersRatePuerByMonth');
$routes->post('/getUsersRateByOffice', 'AdminController::getUsersRateByOffice');

$routes->post('generatePPOOffice', 'ExcelController::generatePPOOffice');
$routes->post('generateRMFBOffice', 'ExcelController::generateRMFBOffice');
$routes->post('generateOcciReport', 'ExcelController::generateOcciReport');
$routes->post('generateOrminReport', 'ExcelController::generateOrminReport');
$routes->post('generateMarinReport', 'ExcelController::generateMarinReport');
$routes->post('generateRomReport', 'ExcelController::generateRomReport');
$routes->post('generatePalReport', 'ExcelController::generatePalReport');
$routes->post('generatePuerReport', 'ExcelController::generatePuerReport');

$routes->get('getColumnNamePPO', 'UserController::getColumnNamePPO');
$routes->get('getColumnNameRMFB', 'UserController::getColumnNameRMFB');
$routes->get('getColumnNameOcci', 'UserController::getColumnNameOcci');
$routes->get('getColumnNameOrmin', 'UserController::getColumnNameOrmin');
$routes->get('getColumnNameRom', 'UserController::getColumnNameRom');
$routes->get('getColumnNameMar', 'UserController::getColumnNameMar');
$routes->get('getColumnNamePal', 'UserController::getColumnNamePal');
$routes->get('getColumnNamePuer', 'UserController::getColumnNamePuer');
$routes->post('insertDataPPO', 'UserController::insertDataPPO');
$routes->post('insertDataRMFB', 'UserController::insertDataRMFB');
$routes->post('insertDataOcci', 'UserController::insertDataOcci');
$routes->post('insertDataOrmin', 'UserController::insertDataOrmin');
$routes->post('insertDataRom', 'UserController::insertDataRom');
$routes->post('insertDataMar', 'UserController::insertDataMar');
$routes->post('insertDataPal', 'UserController::insertDataPal');
$routes->post('insertDataPuer', 'UserController::insertDataPuer');
$routes->post('sendPasswordResetEmail', 'UserController::sendPasswordResetEmail');
$routes->post('resetPassword', 'UserController::resetPassword');
$routes->post('verifyOtp', 'UserController::verifyOtp');

$routes->get('/getAllAverageRatesPPO/(:num)', 'MainController::getAllAverageRatesPPO/$1');
$routes->get('/getAllAverageRatesRMFB/(:num)', 'MainController::getAllAverageRatesRMFB/$1');
$routes->get('/getAllAverageRatesOccidental/(:num)', 'MainController::getAllAverageRatesOccidental/$1');
$routes->get('/getAllAverageRatesOriental/(:num)', 'MainController::getAllAverageRatesOriental/$1');
$routes->get('/getAllAverageRatesMarinduque/(:num)', 'MainController::getAllAverageRatesMarinduque/$1');
$routes->get('/getAllAverageRatesRomblon/(:num)', 'MainController::getAllAverageRatesRomblon/$1');
$routes->get('/getAllAverageRatesPalawan/(:num)', 'MainController::getAllAverageRatesPalawan/$1');
$routes->get('/getAllAverageRatesPuerto/(:num)', 'MainController::getAllAverageRatesPuerto/$1');

$routes->get('/getAllRatesPPO', 'MainController::getAllRatesPPO');
$routes->get('/getAllRatesRMFB', 'MainController::getAllRatesRMFB');
$routes->get('/getAllRatesOccidental', 'MainController::getAllRatesOccidental');
$routes->get('/getAllRatesOriental', 'MainController::getAllRatesOriental');
$routes->get('/getAllRatesMarinduque', 'MainController::getAllRatesMarinduque');
$routes->get('/getAllRatesRomblon', 'MainController::getAllRatesRomblon');
$routes->get('/getAllRatesPalawan', 'MainController::getAllRatesPalawan');
$routes->get('/getAllRatesPuerto', 'MainController::getAllRatesPuerto');

$routes->post('/updateUserRating', 'MainController::updateUserRating');
$routes->post('/sendSMS', 'MainController::sendSMS');
$routes->post('/sendSMSToUser', 'MainController::sendSMSToUser');
$routes->post('/sendSMSToAllUser', 'MainController::sendSMSToAllUser');
$routes->post('getColumnNamePerTbl', 'MainController::getColumnNamePerTbl');
$routes->post('/viewUserByTblRates', 'MainController::viewUserByTblRates');

$routes->group('api', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->post('saveAdmin', 'AdminController::saveAdmin');
    $routes->post('saveUser', 'AdminController::saveUser');
    $routes->post('saveProfile', 'MainController::saveProfile'); 
});
$routes->post('/toggleUserStatus', 'AdminController::toggleUserStatus');
$routes->post('/changeAllUserStatus', 'AdminController::changeAllUserStatus');

$routes->get('/countUser', 'AdminController::countUser');
$routes->post('/loginAdmin', 'AdminController::loginAdmin');
$routes->post('/login', 'MainController::login');
$routes->post('/upload', 'MainController::upload');
$routes->post('/uploadAdmin', 'MainController::uploadAdmin');
$routes->post('/uploadProfile', 'MainController::uploadProfile');
$routes->post('/sendEmail', 'MainController::sendEmail');
$routes->get('/getUserData/(:num)', 'MainController::getUserData/$1');
$routes->get('/getUserAdmin/(:num)', 'MainController::getUserAdmin/$1');        

$routes->post('/insertDue', 'MainController::insertDue');
$routes->get('/selectDue', 'MainController::selectDue');

$routes->get('/viewUserPPORates/(:num)', 'UserController::viewUserPPORates/$1');
$routes->get('/viewUserRMFBRates/(:num)', 'UserController::viewUserRMFBRates/$1');
$routes->get('/viewUserOcciRates/(:num)', 'UserController::viewUserOcciRates/$1');
$routes->get('/viewUserOrienRates/(:num)', 'UserController::viewUserOrienRates/$1');
$routes->get('/viewUserMarinRates/(:num)', 'UserController::viewUserMarinRates/$1');
$routes->get('/viewUserRombRates/(:num)', 'UserController::viewUserRombRates/$1');
$routes->get('/viewUserPalRates/(:num)', 'UserController::viewUserPalRates/$1');
$routes->get('/viewUserPuertoRates/(:num)', 'UserController::viewUserPuertoRates/$1');

$routes->post('/updateAdminInformation', 'AdminController::updateAdminInformation');
$routes->post('generatePdfPPO', 'PdfController::generatePdfPPO');
$routes->post('generatePdfRMFB', 'PdfController::generatePdfRMFB');
$routes->post('generatePdfOccidental', 'PdfController::generatePdfOccidental');
$routes->post('generatePdfOriental', 'PdfController::generatePdfOriental');
$routes->post('generatePdfMarinduque', 'PdfController::generatePdfMarinduque');
$routes->post('generatePdfRomblon', 'PdfController::generatePdfRomblon');
$routes->post('generatePdfPalawan', 'PdfController::generatePdfPalawan');
$routes->post('generatePdfPuerto', 'PdfController::generatePdfPuerto');

$routes->get('/displayColumnsPPO', 'AdminController::displayColumnsPPO');
$routes->post('/updateColumnPPO', 'AdminController::updateColumnPPO');
$routes->get('/displayColumnsRMFB', 'AdminController::displayColumnsRMFB');
$routes->post('/updateColumnRMFB', 'AdminController::updateColumnRMFB');
$routes->get('/displayColumnsOcci', 'AdminController::displayColumnsOcci');
$routes->post('/updateColumnOcci', 'AdminController::updateColumnOcci');
$routes->get('/displayColumnsOrmin', 'AdminController::displayColumnsOrmin');
$routes->post('/updateColumnOrmin', 'AdminController::updateColumnOrmin');
$routes->get('/displayColumnsRom', 'AdminController::displayColumnsRom');
$routes->post('/updateColumnRom', 'AdminController::updateColumnRom');
$routes->get('/displayColumnsMar', 'AdminController::displayColumnsMar');
$routes->post('/updateColumnMar', 'AdminController::updateColumnMar');
$routes->get('/displayColumnsPal', 'AdminController::displayColumnsPal');
$routes->post('/updateColumnPal', 'AdminController::updateColumnPal');
$routes->get('/displayColumnsPuer', 'AdminController::displayColumnsPuer');
$routes->post('/updateColumnPuer', 'AdminController::updateColumnPuer');

$routes->post('/deleteColumnPPO', 'AdminController::deleteColumnPPO');
$routes->post('/deleteColumnRMFB', 'AdminController::deleteColumnRMFB');
$routes->post('/deleteColumnOcci', 'AdminController::deleteColumnOcci');
$routes->post('/deleteColumnOrmin', 'AdminController::deleteColumnOrmin');
$routes->post('/deleteColumnRom', 'AdminController::deleteColumnRom');
$routes->post('/deleteColumnMar', 'AdminController::deleteColumnMar');
$routes->post('/deleteColumnPal', 'AdminController::deleteColumnPal');
$routes->post('/deleteColumnPuer', 'AdminController::deleteColumnPuer');

$routes->get('/getRatePerMonth/(:any)/(:num)/(:any)', 'AdminController::getRatePerMonth/$1/$2/$3');