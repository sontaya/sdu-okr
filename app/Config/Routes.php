<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'AuthController::index');
$routes->post('auth/login', 'AuthController::login');
$routes->get('auth/logout', 'AuthController::logout');


$routes->get('main', 'MainController::index');
// $routes->get('dashboard', 'MainController::dashboard');

$routes->group('keyresult', function ($routes) {
    $routes->get('/', 'KeyresultController::list');
    $routes->get('view/(:num)', 'KeyresultController::view/$1');
    $routes->get('form/(:num)', 'KeyresultController::form/$1');
    $routes->post('save-entry', 'KeyresultController::saveEntry');
    $routes->get('edit-entry/(:num)', 'KeyresultController::editEntry/$1');
    $routes->post('update-entry/(:num)', 'KeyresultController::updateEntry/$1');
    $routes->post('delete-file/(:num)', 'KeyresultController::deleteFile/$1'); // เปลี่ยนเป็น POST
    $routes->post('delete-entry/(:num)', 'KeyresultController::deleteEntry/$1');
    $routes->get('get-entry-details/(:num)', 'KeyresultController::getEntryDetails/$1');
});

$routes->post('upload/temp', 'UploadController::uploadTemp');
$routes->post('upload/remove-temp', 'UploadController::removeTemp');



// Dashboard Routes
$routes->group('dashboard', function ($routes) {
    // Executive Dashboard (สำหรับผู้บริหาร)
    $routes->get('/', 'DashboardController::index');
    $routes->get('executive', 'DashboardController::index');

    // Department Dashboard (สำหรับหัวหน้าหน่วยงาน)
    $routes->get('department', 'DashboardController::department');

    // Progress Dashboard (สำหรับติดตามความคืบหน้า Real-time)
    $routes->get('progress', 'DashboardController::progress');

    // API Routes สำหรับ AJAX calls
    $routes->group('api', function ($routes) {
        // Real-time data APIs
        $routes->get('overview', 'DashboardController::apiOverview');
        $routes->get('trends', 'DashboardController::apiTrends');
        $routes->get('departments', 'DashboardController::apiDepartments');
        $routes->get('keyresult/(:num)', 'DashboardController::apiKeyResultDetail/$1');

        // Export APIs
        $routes->get('export', 'DashboardController::apiExport');

        // Settings APIs
        $routes->get('settings', 'DashboardController::apiSettings');
        $routes->post('settings', 'DashboardController::apiSettings');
    });
});


