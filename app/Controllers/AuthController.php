<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;


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

                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'ไม่มีสิทธิการใช้งาน'
                ]);
            }
        } else {
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

            $ip = get_client_ip();
            $now = date('Y-m-d H:i:s');

            $userModel->update($user['id'], [
                'lasted_login' => $now,
                'lasted_ip' => $ip
            ]);

            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ไม่พบผู้ใช้งาน'
            ]);
        }
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
        $session = session();
        $session->destroy();

        return redirect()->to('/login');
    }

}
