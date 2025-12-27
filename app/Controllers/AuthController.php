<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use App\Libraries\ActivityLogger;


class AuthController extends BaseController
{


    public function __construct()
    {

    }

    public function index()
    {
        // log_message('debug', '✅ login method called');
        return view('auth/login');
    }


    public function login_prod()
    {
        $username = $this->request->getPost('login_user');
        $password = $this->request->getPost('login_password');

        // เรียกใช้ฟังก์ชัน ldap_bind_authenticate จาก helper
        $ldapData = ldap_bind_authenticate($username, $password);

        if ($ldapData !== null) {
            $uid = $ldapData['uid'][0];

            $userModel = new UserModel();
            $user = $userModel->where('uid', $uid)->first();

            if ($user) {
                // ตั้งค่า session
                $session = session();
                $session->set([
                    'isLoggedIn' => true,
                    'uid' => $user['uid'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role'],
                    'department' => $user['department_id'],
                    'user_id' => $user['id']  // เปลี่ยนจาก string เป็น int
                ]);

                // เพิ่มการ initialize user permissions
                $this->initializeUserPermissions($user['id'], $user['department_id']);

                $ip = get_client_ip();
                $now = date('Y-m-d H:i:s');

                $userModel->update($user['id'], [
                    'lasted_login' => $now,
                    'lasted_ip' => $ip
                ]);

                // ✅ เพิ่มส่วนนี้ - เรียก getRedirectUrlByRole และส่ง redirect_url กลับไป
                $redirectUrl = $this->getRedirectUrlByRole($user['id'], $user['department_id']);

                // Log Successful Login
                $logger = new ActivityLogger();
                $description = "User {$user['full_name']} ({$user['uid']}) logged in via LDAP";
                $logger->log('login', ['method' => 'ldap'], $user['id'], $description, 'auth');

                return $this->response->setJSON(['status' => 'success', 'redirect_url' => $redirectUrl]);

            } else {
                // Log Failed Login (User not found locally after LDAP success)
                $logger = new ActivityLogger();
                $description = "Failed login: LDAP success for $username but user not found locally";
                $logger->log('failed_login', ['username' => $username, 'reason' => 'LDAP success but user not found locally'], null, $description, 'auth');

                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ไม่มีสิทธิการใช้งาน'
                ]);
            }
        } else {
            // Log Failed Login (LDAP fail)
            $logger = new ActivityLogger();
            $description = "Failed login: LDAP authentication failed for $username";
            $logger->log('failed_login', ['username' => $username, 'reason' => 'LDAP authentication failed'], null, $description, 'auth');

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
            ]);
        }
    }

    public function login()
    {
        $username = $this->request->getPost('login_user');
        $password = $this->request->getPost('login_password');

        // ดึงข้อมูล user จาก database แทนการ hardcode
        $userModel = new UserModel();
        $user = $userModel->where('uid', $username)->first();

        if ($user) {
            // ตั้งค่า session
            $session = session();
            $session->set([
                'isLoggedIn' => true,
                'uid' => $user['uid'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'department' => $user['department_id'],
                'user_id' => $user['id']  // ใช้ integer ID แทน string
            ]);

            // เพิ่มการ initialize user permissions
            $this->initializeUserPermissions($user['id'], $user['department_id']);
            log_message('debug', '🔍 User Roles after init: ' . json_encode(session('user_roles')));

            $ip = get_client_ip();
            $now = date('Y-m-d H:i:s');

            $userModel->update($user['id'], [
                'lasted_login' => $now,
                'lasted_ip' => $ip
            ]);

            // ✅ เพิ่มส่วนนี้ - เรียก getRedirectUrlByRole และส่ง redirect_url กลับไป
            $redirectUrl = $this->getRedirectUrlByRole($user['id'], $user['department_id']);
            log_message('debug', '🎯 Redirect URL determined: ' . $redirectUrl);

            $response = ['status' => 'success', 'redirect_url' => $redirectUrl];
            log_message('debug', '📤 Sending response: ' . json_encode($response));

            // Log Successful Login
            $logger = new ActivityLogger();
            $description = "User {$user['full_name']} ({$user['uid']}) logged in via Local Dev";
            $logger->log('login', ['method' => 'local_dev'], $user['id'], $description, 'auth');

            return $this->response->setJSON($response);

        } else {
            // Log Failed Login
            $logger = new ActivityLogger();
            $description = "Failed login: User $username not found";
            $logger->log('failed_login', ['username' => $username, 'reason' => 'User not found'], null, $description, 'auth');

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ไม่พบผู้ใช้งาน'
            ]);
        }
    }


    private function getRedirectUrlByRole($userId, $departmentId)
    {
        $db = \Config\Database::connect();

        // ดึงสิทธิ์ของผู้ใช้
        $roles = $db->table('department_user_roles')
            ->select('role_type')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->get()
            ->getResultArray();

        $roleTypes = array_column($roles, 'role_type');

        // 1. Admin หรือ StrategicViewer ไปที่ Executive Dashboard
        if (in_array('Admin', $roleTypes) || in_array('StrategicViewer', $roleTypes)) {
            return base_url('dashboard');
        }

        // 2. ✅ (ใหม่) Approver (ที่ไม่ใช่ Admin) ไปที่ Department Dashboard
        if (in_array('Approver', $roleTypes)) {
            return base_url('dashboard/department');
        }

        // 3. Reporter (ที่ไม่มีสิทธิ์สูงกว่า) ไปที่ Key Results
        if (in_array('Reporter', $roleTypes)) {
            return base_url('keyresult/list');
        }

        // กรณีอื่นๆ (ถ้ามี) ใช้ default executive dashboard
        return base_url('dashboard');
    }

    // initialize user permissions
    private function initializeUserPermissions($userId, $departmentId)
    {
        $db = \Config\Database::connect();

        // ดึงสิทธิ์ทั้งหมดของผู้ใช้ในหน่วยงาน
        $permissions = $db->table('department_user_roles')
            ->select('role_type')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->get()
            ->getResultArray();

        $roles = array_column($permissions, 'role_type');

        // เก็บข้อมูลสิทธิ์ใน session
        session()->set([
            'user_roles' => $roles,
            'is_admin' => in_array('Admin', $roles),
            'is_approver' => in_array('Approver', $roles),
            'is_reporter' => in_array('Reporter', $roles)
        ]);
    }

    public function logout()
    {
        // Log Logout
        $logger = new ActivityLogger();
        $user = session('full_name') ?? 'Unknown User';
        $uid = session('uid') ?? 'Unknown';

        $description = "User $user ($uid) logged out";
        $logger->log('logout', [], session('user_id'), $description, 'auth');

        $session = session();
        $session->destroy();

        return redirect()->to('/login');
    }

}
