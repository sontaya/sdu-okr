<?php
/**
 * สร้างไฟล์ใหม่: app/Controllers/AdminController.php
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
        // $authCheck = $this->requireAdmin();
        // if ($authCheck) return $authCheck;

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
                d.short_name as department_name,
                COALESCE(GROUP_CONCAT(DISTINCT dur.role_type ORDER BY dur.role_type), "") as current_roles
            ')
            ->join('departments d', 'u.department_id = d.id')
            ->join('department_user_roles dur', 'u.id = dur.user_id AND u.department_id = dur.department_id', 'left')
            ->where('u.department_id', session('department'))
            ->groupBy('u.id')
            ->orderBy('u.full_name')
            ->get()
            ->getResultArray();

        // นับจำนวนรายงานที่รอการอนุมัติ
        $pendingCount = getPendingApprovalsCount();

        $this->data['users'] = $users;
        $this->data['pending_approvals_count'] = $pendingCount;
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->data['title'] = 'จัดการสิทธิ์ผู้ใช้งาน';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/admin/manage-permissions.js'
        ];
        $this->contentTemplate = 'admin/manage-permissions';
        return $this->render();
    }

    public function grantRole()
    {
        if (!isAdminFast()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์']);
        }

        $userId = $this->request->getPost('user_id');
        $roleType = $this->request->getPost('role_type');
        $departmentId = session('department');

        // ตรวจสอบข้อมูล
        if (!$userId || !$roleType || !in_array($roleType, ['Reporter', 'Approver', 'Admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }

        // ตรวจสอบว่าผู้ใช้อยู่ในหน่วยงานเดียวกันหรือไม่
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user || $user['department_id'] != $departmentId) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบผู้ใช้ในหน่วยงานนี้']);
        }

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
        $departmentId = session('department');

        // ห้ามเพิกถอนสิทธิ์ตัวเอง
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

        // สถิติต่างๆ
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

        // ข้อมูลผู้ใช้แยกตามสิทธิ์
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
}