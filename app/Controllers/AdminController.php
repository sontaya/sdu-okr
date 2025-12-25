<?php
/**
 * Enhanced AdminController with eProfile API integration
 */

namespace App\Controllers;

use App\Models\UserModel;

class AdminController extends TemplateController
{
    protected $allowed = [];

    public function index()
    {
        return redirect()->to(base_url('admin/dashboard'));
    }

    public function dashboard()
    {
        $this->data['title'] = 'แผงควบคุมผู้ดูแลระบบ';
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->contentTemplate = 'admin/dashboard';
        return $this->render();
    }

    public function managePermissions()
    {
        $authCheck = $this->requireAdmin();
        if ($authCheck) return $authCheck;

        $db = \Config\Database::connect();

        // ดึงข้อมูลผู้ใช้ในหน่วยงานเดียวกัน
        $users = $db->table('users u')
            ->select('
                u.id,
                u.uid,
                u.full_name,
                u.department_id,
                d.short_name as department_name_short, d.name as department_name,
                COALESCE(GROUP_CONCAT(DISTINCT dur.role_type ORDER BY dur.role_type), "") as current_roles
            ')
            ->join('departments d', 'u.department_id = d.id')
            ->join('department_user_roles dur', 'u.id = dur.user_id AND u.department_id = dur.department_id', 'left')
            // ->where('u.department_id', session('department')) // Allow viewing all users
            ->groupBy('u.id')
            ->orderBy('u.full_name')
            ->get()
            ->getResultArray();

        // ดึงข้อมูลหน่วยงานทั้งหมดสำหรับ dropdown
        $departments = $db->table('departments')
            ->select('id, short_name, name')
            ->orderBy('short_name')
            ->get()
            ->getResultArray();

        $allDepartments = $db->table('departments')
            ->select('id, short_name, name')
            ->orderBy('short_name')
            ->get()
            ->getResultArray();


        // ดึงสถิติจำนวนแต่ละ Role แยกตามหน่วยงาน
        $roleStatsRaw = $db->table('department_user_roles dur')
            ->select('d.short_name, d.name as department_name, dur.role_type, COUNT(*) as count')
            ->join('departments d', 'dur.department_id = d.id')
            ->groupBy('d.id, dur.role_type')
            ->orderBy('d.short_name')
            ->get()
            ->getResultArray();

        $roleStats = [];
        foreach ($roleStatsRaw as $row) {
            $key = $row['short_name']; // Use Short Name as key
            if (!isset($roleStats[$key])) {
                $roleStats[$key] = [
                    'short_name' => $row['short_name'],
                    'full_name' => $row['department_name'],
                    'stats' => [
                        'Reporter' => 0,
                        'Approver' => 0,
                        'StrategicViewer' => 0
                    ]
                ];
            }
            // Fix: Access 'stats' key
            $roleStats[$key]['stats'][$row['role_type']] = $row['count'];
        }

        $this->data['users'] = $users;
        $this->data['departments'] = $departments;
        $this->data['role_stats'] = $roleStats; // Pass stats to view
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->data['all_departments'] = $allDepartments;
        $this->data['title'] = 'จัดการสิทธิ์ผู้ใช้งาน';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/admin/manage-permissions.js'
        ];
        $this->contentTemplate = 'admin/manage-permissions';
        return $this->render(); // Ensure this matches the existing return statement structure if it was different
    }

    /**
     * ค้นหาผู้ใช้จาก eProfile API
     */
    public function searchEprofileUsers()
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        // ปิด Debug Toolbar สำหรับ AJAX
        if ($this->request->isAJAX()) {
            $this->response->setHeader('X-Debug-Toolbar', 'off');
        }

        $searchKey = $this->request->getPost('search_key');

        if (empty($searchKey) || strlen($searchKey) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'กรุณาใส่คำค้นหาอย่างน้อย 2 ตัวอักษร'
            ]);
        }

        try {
            $client = \Config\Services::curlrequest();

            // เพิ่ม debug log
            log_message('info', 'eProfile API Request - Search Key: ' . $searchKey);

            $response = $client->request('POST', 'https://eprofile.dusit.ac.th/app/api/get_personnel_profile', [
                'headers' => [
                    'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImVsZWFybmluZyJ9.oml1RCGtLv46NvFwzOq_WN6R9vudnW0b6KwaoUeD1z0',
                    'Content-Type' => 'application/json'
                ],
                'form_params' => [
                    'search_key' => $searchKey
                ],
                'timeout' => 30,
                'debug' => ENVIRONMENT === 'development' // เปิด debug ใน dev mode
            ]);

            $responseBody = $response->getBody();
            $responseData = json_decode($responseBody, true);

            // เพิ่ม debug log สำหรับ response
            log_message('info', 'eProfile API Response Status: ' . $response->getStatusCode());
            log_message('info', 'eProfile API Response Body: ' . substr($responseBody, 0, 500) . '...');

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('API returned status code: ' . $response->getStatusCode());
            }

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            // ✅ แก้ไข: ปรับให้ตรงกับโครงสร้าง API จริง
            if (!isset($responseData['status']) || $responseData['status'] !== 200) {
                log_message('error', 'API Error Response: ' . $responseBody);
                throw new \Exception('API returned error status: ' . ($responseData['status'] ?? 'unknown'));
            }

            // กรองข้อมูลที่จำเป็น และตรวจสอบว่ามีอยู่ในระบบแล้วหรือไม่
            $filteredUsers = [];
            $userModel = new UserModel();

            // ✅ แก้ไข: ใช้ 'profile' แทน 'data'
            $userData = $responseData['profile'] ?? [];

            if (is_array($userData) && count($userData) > 0) {
                foreach ($userData as $user) {
                    // ข้าม record ที่ไม่มีข้อมูลสำคัญ
                    if (empty($user['USER_ID'])) {
                        continue;
                    }

                    // ตรวจสอบว่าผู้ใช้มีอยู่ในระบบแล้วหรือไม่
                    $existingUser = $userModel->where('uid', $user['USER_ID'])->first();

                    $filteredUsers[] = [
                        'USER_ID' => $user['USER_ID'] ?? '',
                        'CITIZEN_CODE' => $user['CITIZEN_CODE'] ?? '',
                        'FIRST_NAME_THA' => $user['FIRST_NAME_THA'] ?? '',
                        'LAST_NAME_THA' => $user['LAST_NAME_THA'] ?? '',
                        'ACADEMIC_FULLNAME_TH' => $user['ACADEMIC_FULLNAME_TH'] ?? '',
                        'NAME_FACULTY' => $user['NAME_FACULTY'] ?? '',
                        'existing_user' => $existingUser ? true : false
                    ];
                }
            }

            log_message('info', 'eProfile API - Filtered users count: ' . count($filteredUsers));

            // Clear any previous output
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            return $this->response->setContentType('application/json')
                ->setBody(json_encode([
                    'success' => true,
                    'data' => $filteredUsers,
                    'message' => 'ค้นหาสำเร็จ พบ ' . count($filteredUsers) . ' รายการ',
                    'debug' => ENVIRONMENT === 'development' ? [
                        'search_key' => $searchKey,
                        'api_status' => $response->getStatusCode(),
                        'raw_count' => is_array($userData) ? count($userData) : 0
                    ] : null
                ], JSON_UNESCAPED_UNICODE));

        } catch (\Exception $e) {
            log_message('error', 'eProfile API Error: ' . $e->getMessage());
            log_message('error', 'eProfile API Error Trace: ' . $e->getTraceAsString());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ eProfile API: ' . $e->getMessage(),
                'debug' => ENVIRONMENT === 'development' ? [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ] : null
            ]);
        }
    }

    /**
     * เพิ่มผู้ใช้ใหม่จากข้อมูล eProfile
     */
    public function addUserFromEprofile()
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $userData = $this->request->getPost('user_data');
        $departmentId = $this->request->getPost('department_id');
        $roles = $this->request->getPost('roles') ?? []; // ✅ แก้ไข: ไม่ใช้ FILTER_REQUIRE_ARRAY

        // Validation
        if (empty($userData) || empty($departmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ข้อมูลไม่ครบถ้วน'
            ]);
        }

        try {
            $userData = json_decode($userData, true);

            if (!$userData || !isset($userData['USER_ID'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ข้อมูลผู้ใช้ไม่ถูกต้อง'
                ]);
            }

            $userModel = new UserModel();

            // ตรวจสอบว่ามีผู้ใช้นี้อยู่แล้วหรือไม่
            $existingUser = $userModel->where('uid', $userData['USER_ID'])->first();
            if ($existingUser) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ผู้ใช้นี้มีอยู่ในระบบแล้ว'
                ]);
            }

            // เตรียมข้อมูลสำหรับบันทึก
            $newUserData = [
                'uid' => $userData['USER_ID'],
                'citizen_id' => $userData['CITIZEN_CODE'] ?? '',
                'first_name' => $userData['FIRST_NAME_THA'] ?? '',
                'last_name' => $userData['LAST_NAME_THA'] ?? '',
                'full_name' => $userData['ACADEMIC_FULLNAME_TH'] ?? '',
                'department_id' => $departmentId,
                'created_by' => session('uid'),
                'created_date' => date('Y-m-d H:i:s')
            ];

            $db = \Config\Database::connect();
            $db->transStart();

            // บันทึกข้อมูลผู้ใช้
            $userId = $userModel->insert($newUserData);

            if (!$userId) {
                throw new \Exception('ไม่สามารถบันทึกข้อมูลผู้ใช้ได้');
            }

            // ✅ แก้ไข: กำหนดสิทธิ์ (ตรวจสอบ roles เป็น array และไม่ว่าง)
            $validRoles = ['Reporter', 'Approver', 'Admin'];
            $rolesGranted = [];

            if (is_array($roles) && !empty($roles)) {
                foreach ($roles as $role) {
                    if (in_array($role, $validRoles)) {
                        $granted = grantDepartmentRole($userId, $departmentId, $role, session('user_id'));
                        if ($granted) {
                            $rolesGranted[] = $role;
                        }
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }

            // สร้างข้อความตอบกลับ
            $message = 'เพิ่มผู้ใช้ "' . $newUserData['full_name'] . '" สำเร็จ';
            if (!empty($rolesGranted)) {
                $message .= ' พร้อมสิทธิ์: ' . implode(', ', array_map('getUserRoleNames', $rolesGranted));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Add User Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }

    public function grantRole()
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $userId = $this->request->getPost('user_id');
        $roleType = $this->request->getPost('role_type');
        // Use POST department_id if available, else session
        $departmentId = $this->request->getPost('department_id') ?? session('department');

        if (grantDepartmentRole($userId, $departmentId, $roleType)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'เพิ่มสิทธิ์ ' . getUserRoleNames($roleType) . ' สำเร็จ'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }

    public function revokeRole()
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $userId = $this->request->getPost('user_id');
        $roleType = $this->request->getPost('role_type');
        // Use POST department_id if available, else session
        $departmentId = $this->request->getPost('department_id') ?? session('department');

        if ($userId == session('user_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถเพิกถอนสิทธิ์ตัวเองได้']);
        }

        if (revokeDepartmentRole($userId, $departmentId, $roleType)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'เพิกถอนสิทธิ์ ' . getUserRoleNames($roleType) . ' สำเร็จ'
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }

    public function getUserRoles($userId)
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $permissions = getDepartmentUserRoles($userId, session('department'));

        return $this->response->setJSON([
            'success' => true,
            'permissions' => $permissions
        ]);
    }

    public function systemStats()
    {
        $authCheck = $this->requireAdmin();
        if ($authCheck) return $authCheck;

        $db = \Config\Database::connect();
        $departmentId = session('department');

        $stats = [
            'total_users' => $db->table('users')->where('department_id', $departmentId)->countAllResults(),
            'total_key_results' => $db->table('key_result_departments krd')
                ->join('key_results kr', 'krd.key_result_id = kr.id')
                ->where('krd.department_id', $departmentId)
                ->where('kr.key_result_year', '2568')
                ->countAllResults(),
            'pending_approvals' => getPendingApprovalsCount(),
            'approved_this_month' => $db->table('key_result_progress krp')
                ->join('key_results kr', 'krp.key_result_id = kr.id')
                ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
                ->where('krp.status', 'approved')
                ->where('krd.department_id', $departmentId)
                ->where('krd.role', 'Leader')
                ->where('MONTH(krp.approved_date)', date('n'))
                ->where('YEAR(krp.approved_date)', date('Y'))
                ->countAllResults(),
        ];

        $usersByRole = $db->table('department_user_roles dur')
            ->select('dur.role_type, COUNT(*) as count')
            ->where('dur.department_id', $departmentId)
            ->groupBy('dur.role_type')
            ->get()
            ->getResultArray();

        $this->data['stats'] = $stats;
        $this->data['users_by_role'] = $usersByRole;
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->data['title'] = 'สถิติระบบ';
        $this->contentTemplate = 'admin/system-stats';
        return $this->render();
    }

    /**
     * แสดงหน้าแก้ไขผู้ใช้
     */
    public function editUser($userId)
    {
        $authCheck = $this->requireAdmin();
        if ($authCheck) return $authCheck;

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['department_id'] != session('department')) {
            session()->setFlashdata('error', 'ไม่พบผู้ใช้ในหน่วยงานนี้');
            return redirect()->back();
        }

        // ดึงข้อมูลสิทธิ์ปัจจุบัน
        $userRoles = getUserRoles($userId, $user['department_id']);

        // ดึงข้อมูลหน่วยงานทั้งหมด
        $db = \Config\Database::connect();
        $departments = $db->table('departments')
            ->select('id, short_name, name')
            ->orderBy('short_name')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'user' => $user,
            'current_roles' => $userRoles,
            'departments' => $departments
        ]);
    }

    /**
     * อัปเดตข้อมูลผู้ใช้
     */
    public function updateUser($userId)
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user || $user['department_id'] != session('department')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบผู้ใช้ในหน่วยงานนี้']);
        }

        // ห้ามแก้ไขข้อมูลตัวเอง (ยกเว้น Admin)
        if ($userId == session('user_id') && !hasRole('Admin')) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถแก้ไขข้อมูลตัวเองได้']);
        }

        $departmentId = $this->request->getPost('department_id');
        $roles = $this->request->getPost('roles') ?? [];

        if (empty($departmentId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาเลือกหน่วยงาน']);
        }

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // อัปเดตหน่วยงาน (หากเปลี่ยน)
            if ($user['department_id'] != $departmentId) {
                $userModel->update($userId, [
                    'department_id' => $departmentId,
                    'updated_by' => session('uid'),
                    'updated_date' => date('Y-m-d H:i:s')
                ]);

                // ลบสิทธิ์เก่าทั้งหมด
                $db->table('department_user_roles')
                    ->where('user_id', $userId)
                    ->delete();
            } else {
                // ลบสิทธิ์เก่าในหน่วยงานปัจจุบัน
                $db->table('department_user_roles')
                    ->where('user_id', $userId)
                    ->where('department_id', $departmentId)
                    ->delete();
            }

            // เพิ่มสิทธิ์ใหม่
            $validRoles = ['Reporter', 'Approver', 'Admin'];
            $rolesGranted = [];

            if (is_array($roles) && !empty($roles)) {
                foreach ($roles as $role) {
                    if (in_array($role, $validRoles)) {
                        $granted = grantDepartmentRole($userId, $departmentId, $role, session('user_id'));
                        if ($granted) {
                            $rolesGranted[] = $role;
                        }
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }

            // อัปเดต session หากเป็นการแก้ไขตัวเอง
            if ($userId == session('user_id')) {
                refreshUserPermissions($userId, $departmentId);
                session()->set('department', $departmentId);
            }

            $message = 'อัปเดตข้อมูลผู้ใช้ "' . $user['full_name'] . '" สำเร็จ';
            if (!empty($rolesGranted)) {
                $message .= ' พร้อมสิทธิ์: ' . implode(', ', array_map('getUserRoleNames', $rolesGranted));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Update User Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }
}