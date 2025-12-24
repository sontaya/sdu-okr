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

    public function initController($request, $response, $logger)
    {
        // ✅ เรียก parent initController ก่อน
        parent::initController($request, $response, $logger);

        helper(['permission']);

        if (session('isLoggedIn')) {
            $this->globalData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'client_ip' => $this->request->getIPAddress(),
                'user_id' => session('user_id'),
            ];


            // ข้อมูลสิทธิ์ผู้ใช้สำหรับ template
            $this->data['current_user'] = [
                'uid' => session('uid'),
                'full_name' => session('full_name'),
                'department' => session('department'),
                'is_admin' => session('is_admin') ?? false,
                'is_approver' => session('is_approver') ?? false,
                'is_reporter' => session('is_reporter') ?? false,
                'user_roles' => session('user_roles') ?? []
            ];

            // ตรวจสอบว่าผู้ใช้ยังมีอยู่ในระบบหรือไม่
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find(session('user_id'));

            if (!$user) {
                session()->destroy();
                redirect()->to('/login')->send();
                exit;
            }
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
        $router = service('router');
        $controllerName = $router->controllerName();
        $methodName = $router->methodName();

        // ✅ แก้ไข: ลบ backslash ข้างหน้าออก
        $controllerName = ltrim($controllerName, '\\');
        $currentRoute = $controllerName . '::' . $methodName;

        // กำหนด menu mapping แบบละเอียด (Controller + Method)
        $detailedMenuMapping = [
            'App\Controllers\DashboardController::index' => 'dashboard-executive',
            'App\Controllers\DashboardController::executive' => 'dashboard-executive',
            'App\Controllers\DashboardController::department' => 'dashboard-department', // <-- เพิ่มบรรทัดนี้
            'App\Controllers\DashboardController::progress' => 'dashboard-progress',
            'App\Controllers\KeyresultController::list' => 'keyresult-list',
            'App\Controllers\KeyresultController::view' => 'keyresult-list',
            'App\Controllers\KeyresultController::form' => 'keyresult-list',
            'App\Controllers\KeyresultController::editEntry' => 'keyresult-list',
            'App\Controllers\ProgressController::list' => 'progress-list',
            'App\Controllers\MainController::index' => 'dashboard-executive',
            'App\Controllers\MainController::dashboard' => 'dashboard-executive',
            'App\Controllers\StrategicController::index' => 'strategic-overview',
            'App\Controllers\StrategicController::overview' => 'strategic-overview',
            'App\Controllers\AdminController::managePermissions' => 'admin-permissions',
            'App\Controllers\ProgressController::pendingApprovals' => 'keyresult-pending-approvals',
        ];

        if (isset($detailedMenuMapping[$currentRoute])) {
            $menuCode = $detailedMenuMapping[$currentRoute];
            $this->setActiveMenu($menuCode);
        } else {
            // Fallback: ใช้ Controller-based mapping
            $controllerMenuMapping = [
                'App\Controllers\DashboardController' => 'dashboard-executive',
                'App\Controllers\KeyresultController' => 'keyresult-list',
                'App\Controllers\MainController' => 'dashboard-executive',
            ];

            if (isset($controllerMenuMapping[$controllerName])) {
                $menuCode = $controllerMenuMapping[$controllerName];
                $this->setActiveMenu($menuCode);
            }
        }
    }

    public function render()
    {
        // ✅ Debug ขั้นที่ 1 - ตรวจสอบว่า render() ถูกเรียกหรือไม่
        log_message('error',"🔴 DEBUG: render() method called");

        // ✅ Debug ขั้นที่ 2 - ตรวจสอบ router information
        $router = service('router');
        $controllerName = $router->controllerName();
        $methodName = $router->methodName();
        log_message('error',"🔴 DEBUG: Controller = $controllerName");
        log_message('error',"🔴 DEBUG: Method = $methodName");

        // ✅ เรียก setAutoActiveMenu ก่อน render
        log_message('error',"🔴 DEBUG: About to call setAutoActiveMenu()");
        $this->setAutoActiveMenu();
        log_message('error',"🔴 DEBUG: setAutoActiveMenu() completed");

        // ตั้งค่าข้อมูลสิทธิ์ก่อน render
        $this->setPermissionData();

        // ✅ Debug ขั้นที่ 3 - ตรวจสอบ session
        $activeMenu = session('menu.active');
        log_message('error',"🔴 DEBUG: Active menu from session = '$activeMenu'");

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
        // ตั้งค่าข้อมูลสิทธิ์ก่อน render
        $this->setPermissionData();

        // Load header, content, and footer views into the $aTemplate array
        $this->aTemplate['header'] = view('template/header', $this->data);
        $this->aTemplate['content'] = view($this->contentTemplate, $this->data);
        $this->aTemplate['footer'] = view('template/footer', $this->data);

        // Render the template without a menu
        return view('template/index-nomenu', $this->aTemplate);
    }

    // methods สำหรับตรวจสอบสิทธิ์
    protected function requireRole($role, $message = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้')
    {
        if (!hasRole($role)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            session()->setFlashdata('error', $message);
            return redirect()->back();
        }
        return null;
    }


    protected function requireAdmin($message = 'คุณไม่มีสิทธิ์ผู้ดูแลระบบ')
    {
        return $this->requireRole('Admin', $message);
    }

    protected function requireApprover($message = 'คุณไม่มีสิทธิ์อนุมัติ')
    {
        if (!hasRole('Approver') && !hasRole('Admin')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            session()->setFlashdata('error', $message);
            return redirect()->back();
        }
        return null;
    }

    protected function requireReporter($message = 'คุณไม่มีสิทธิ์รายงาน')
    {
        if (!hasRole('Reporter') && !hasRole('Approver') && !hasRole('Admin')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => $message]);
            }
            session()->setFlashdata('error', $message);
            return redirect()->back();
        }
        return null;
    }

    // helper method สำหรับตรวจสอบสิทธิ์ใน view
    protected function setPermissionData()
    {
        $this->data['permissions'] = [
            'can_report' => hasRole('Reporter') || hasRole('Approver') || hasRole('Admin'),
            'can_approve' => hasRole('Approver') || hasRole('Admin'),
            'is_admin' => hasRole('Admin')
        ];
    }

}