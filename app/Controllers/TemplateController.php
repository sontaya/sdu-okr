<?php
// แทนที่ส่วน constructor และ initController ใน TemplateController.php

namespace App\Controllers;

use CodeIgniter\Controller;

class TemplateController extends BaseController
{
    protected $data = [];
    protected $aTemplate = [];
    protected $globalData;
    protected $contentTemplate;
    protected $allowed = [];

    public function __construct()
    {
        // ✅ ลบโค้ดออกหมด เพราะ $this->request ยังไม่พร้อม
    }

    public function initController($request, $response, $logger)
    {
        // ✅ เรียก parent initController ก่อน
        parent::initController($request, $response, $logger);

        // ✅ ย้ายโค้ดมาไว้ที่นี่แทน เพราะ $this->request พร้อมใช้แล้ว
        if (session('isLoggedIn')) {
            $this->globalData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'client_ip' => $this->request->getIPAddress(),
                'user_id' => session('user_id'),
            ];
        }

        $method = service('router')->methodName();

        if (!session('isLoggedIn') && !in_array($method, $this->allowed)) {
            redirect()->to('/login')->send();
            exit;
        }
    }

    public function setActiveMenu($target)
    {
        $sessionMenu = [
            'active' => $target,
        ];
        session()->set('menu', $sessionMenu);
    }

    public function setAutoActiveMenu()
    {
        log_message('error', '🔴 DEBUG: setAutoActiveMenu() START');

        $router = service('router');
        $controllerName = $router->controllerName();
        $methodName = $router->methodName();

        // ✅ แก้ไข: ลบ backslash ข้างหน้าออก
        $controllerName = ltrim($controllerName, '\\');

        $currentRoute = $controllerName . '::' . $methodName;
        log_message('error', "🔴 DEBUG: Original controller = " . $router->controllerName());
        log_message('error', "🔴 DEBUG: Fixed controller = $controllerName");
        log_message('error', "🔴 DEBUG: Current route = $currentRoute");

        // กำหนด menu mapping แบบละเอียด (Controller + Method)
        $detailedMenuMapping = [
            'App\Controllers\DashboardController::index' => 'dashboard-executive',
            'App\Controllers\DashboardController::executive' => 'dashboard-executive',
            'App\Controllers\DashboardController::department' => 'dashboard-department',
            'App\Controllers\DashboardController::progress' => 'dashboard-progress',
            'App\Controllers\KeyresultController::list' => 'keyresult-list',
            'App\Controllers\KeyresultController::view' => 'keyresult-list',
            'App\Controllers\KeyresultController::form' => 'keyresult-list',
            'App\Controllers\KeyresultController::editEntry' => 'keyresult-list',
        ];

        if (isset($detailedMenuMapping[$currentRoute])) {
            $menuCode = $detailedMenuMapping[$currentRoute];
            log_message('error', "🔴 DEBUG: ✅ Found exact mapping, setting menu to: $menuCode");
            $this->setActiveMenu($menuCode);
        } else {
            // Fallback: ใช้ Controller-based mapping (เดิม)
            $controllerMenuMapping = [
                'App\Controllers\DashboardController' => 'dashboard-executive',
                'App\Controllers\KeyresultController' => 'keyresult-list',
            ];

            if (isset($controllerMenuMapping[$controllerName])) {
                $menuCode = $controllerMenuMapping[$controllerName];
                log_message('error', "🔴 DEBUG: ⚠️ Using fallback, setting menu to: $menuCode");
                $this->setActiveMenu($menuCode);
            } else {
                log_message('error', "🔴 DEBUG: ❌ No mapping found for controller: $controllerName");
            }
        }

        // ✅ ตรวจสอบผลลัพธ์หลังจาก setActiveMenu
        $activeMenu = session('menu.active');
        log_message('error', "🔴 DEBUG: Final active menu = '$activeMenu'");

        log_message('error', '🔴 DEBUG: setAutoActiveMenu() END');
    }

    public function render()
    {
        // ✅ Debug ขั้นที่ 1 - ตรวจสอบว่า render() ถูกเรียกหรือไม่
        log_message('error',"🟢 DEBUG: render() method called");

        // ✅ Debug ขั้นที่ 2 - ตรวจสอบ router information
        $router = service('router');
        $controllerName = $router->controllerName();
        $methodName = $router->methodName();
        log_message('error',"🟢 DEBUG: Controller = $controllerName");
        log_message('error',"🟢 DEBUG: Method = $methodName");

        // ✅ เรียก setAutoActiveMenu ก่อน render
        log_message('error',"🟢 DEBUG: About to call setAutoActiveMenu()");
        $this->setAutoActiveMenu();
        log_message('error',"🟢 DEBUG: setAutoActiveMenu() completed");

        // ✅ Debug ขั้นที่ 3 - ตรวจสอบ session
        $activeMenu = session('menu.active');
        log_message('error',"🟢 DEBUG: Active menu from session = '$activeMenu'");

        // Load header, content, and footer views into the $aTemplate array
        $this->aTemplate['header'] = view('template/header', $this->data);
        $this->aTemplate['sidebar'] = view('template/sidebar_menu', $this->data);
        $this->aTemplate['content'] = view($this->contentTemplate, $this->data);
        $this->aTemplate['footer'] = view('template/footer', $this->data);

        // Render the main template
        return view('template/index', $this->aTemplate);
    }


    public function renderNoMenu()
    {
        // Load header, content, and footer views into the $aTemplate array
        $this->aTemplate['header'] = view('template/header', $this->data);
        $this->aTemplate['content'] = view($this->contentTemplate, $this->data);
        $this->aTemplate['footer'] = view('template/footer', $this->data);

        // Render the template without a menu
        return view('template/index-nomenu', $this->aTemplate);
    }
}