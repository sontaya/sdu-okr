<?php
namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes = Services::routes();

if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}


$routes->get('/', 'AuthController::index');
$routes->get('/demo', 'DemoController::index');
$routes->get('login', 'AuthController::index');
$routes->post('auth/login', 'AuthController::login');
$routes->get('auth/logout', 'AuthController::logout');
$routes->get('redirect-after-login', 'AuthController::redirectAfterLogin');


// Admin Routes - เฉพาะผู้ที่มีสิทธิ์ Admin
$routes->group('admin', function($routes) {
    $routes->get('/', 'AdminController::dashboard');
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('manage-permissions', 'AdminController::managePermissions');
    $routes->get('system-stats', 'AdminController::systemStats');

    // API endpoints
    $routes->post('grant-role', 'AdminController::grantRole');
    $routes->post('revoke-role', 'AdminController::revokeRole');
    $routes->get('user-roles/(:num)', 'AdminController::getUserRoles/$1');

    $routes->post('search-eprofile-users', 'AdminController::searchEprofileUsers');
    $routes->post('add-user-from-eprofile', 'AdminController::addUserFromEprofile');

    $routes->get('edit-user/(:num)', 'AdminController::editUser/$1');
    $routes->post('update-user/(:num)', 'AdminController::updateUser/$1');
});


// $routes->get('/', 'DashboardController::index');
$routes->get('dashboard', 'DashboardController::index');

$routes->group('keyresult', function($routes) {
    $routes->get('/', 'KeyresultController::list');
    $routes->get('list', 'KeyresultController::list');
    $routes->get('view/(:num)', 'KeyresultController::view/$1');
    $routes->get('form/(:num)', 'KeyresultController::form/$1');
    $routes->get('edit-entry/(:num)', 'KeyresultController::editEntry/$1');
    $routes->get('view-entry/(:num)', 'KeyresultController::viewEntry/$1');

    $routes->post('save-entry', 'KeyresultController::saveEntry');
    $routes->post('update-entry/(:num)', 'KeyresultController::updateEntry/$1');
    $routes->delete('delete-entry/(:num)', 'KeyresultController::deleteEntry/$1');
    $routes->delete('delete-file/(:num)', 'KeyresultController::deleteFile/$1');
    $routes->get('entry-details/(:num)', 'KeyresultController::getEntryDetails/$1');
});

$routes->post('upload/temp', 'UploadController::uploadTemp');
$routes->post('upload/remove-temp', 'UploadController::removeTemp');


// Progress Routes
$routes->group('progress', function($routes) {
    $routes->get('/', 'ProgressController::list');
    $routes->get('list', 'ProgressController::list');
    $routes->get('view/(:num)', 'ProgressController::view/$1');
    $routes->get('view/(:num)/(:num)', 'ProgressController::view/$1/$2');

    // Form routes - ตรวจสอบสิทธิ์ใน Controller
    $routes->get('form/(:num)', 'ProgressController::form/$1');
    $routes->get('form/(:num)/(:num)', 'ProgressController::form/$1/$2');
    $routes->get('form/(:num)/(:num)/(:num)', 'ProgressController::form/$1/$2/$3');

    // Action routes
    $routes->post('save', 'ProgressController::save');
    $routes->post('update/(:num)', 'ProgressController::update/$1');
    $routes->post('submit/(:num)', 'ProgressController::submit/$1');
    $routes->post('approve/(:num)', 'ProgressController::approve/$1');
    $routes->post('reject/(:num)', 'ProgressController::reject/$1');
    $routes->delete('delete/(:num)', 'ProgressController::delete/$1');

    // Approval management
    $routes->get('pending-approvals', 'ProgressController::pendingApprovals');
    $routes->get('progress-details/(:num)', 'ProgressController::getProgressDetails/$1');

    // Comments
    $routes->post('add-comment', 'ProgressController::addComment');

    $routes->get('detailed/(:num)', 'ProgressController::detailedReport/$1');
});


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


// Strategic Dashboard Routes - เฉพาะผู้ที่มีสิทธิ์ Strategic Viewer หรือ Admin
$routes->group('strategic', function($routes) {
    $routes->get('/', 'StrategicController::index');
    $routes->get('overview', 'StrategicController::overview');

    // API endpoints สำหรับ Strategic Dashboard
    $routes->get('api', 'StrategicController::api');
    $routes->get('api/stats', 'StrategicController::api');
    $routes->get('api/department/(:num)', 'StrategicController::api');

    // Export functionality
    $routes->get('export', 'StrategicController::export');
});