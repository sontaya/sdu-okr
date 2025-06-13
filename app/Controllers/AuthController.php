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
                    'user_id' => $user['id']
                ]);

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

        // ตั้งค่า session
        $session = session();
        $session->set([
            'isLoggedIn' => true,
            'uid' =>'sontaya_yam',
            'full_name' => 'Sontaya Yamdach',
            'role' => 'Admin',
            'department' => 18,
            'user_id' => 'sontaya_yam'
        ]);

        $ip = get_client_ip();
        $now = date('Y-m-d H:i:s');

        return $this->response->setJSON(['status' => 'success']);

    }

    public function logout()
    {
        $session = session();
        $session->destroy();

        return redirect()->to('/login');
    }

}
