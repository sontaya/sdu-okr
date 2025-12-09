<?php
/**
 * ============================================================================
 * Permission Helper Functions สำหรับระบบ Department-based Permissions
 * ============================================================================
 *
 * ไฟล์นี้ให้วางที่: app/Helpers/permission_helper.php
 *
 * จากนั้นเพิ่มใน app/Config/Autoload.php:
 * public $helpers = ['permission'];
 */

if (!function_exists('getDepartmentUserRoles')) {
    /**
     * ดึงข้อมูลสิทธิ์ของผู้ใช้ในหน่วยงาน
     */
    function getDepartmentUserRoles($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        if (!$userId || !$departmentId) {
            return null;
        }

        $db = \Config\Database::connect();

        // ดึงสิทธิ์จากตาราง department_user_roles
        $roles = $db->table('department_user_roles')
            ->select('role_type')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->get()
            ->getResultArray();

        if (empty($roles)) {
            return [
                'can_report' => false,
                'can_approve' => false,
                'is_admin' => false,
                'roles' => []
            ];
        }

        $roleTypes = array_column($roles, 'role_type');

        return [
            'can_report' => in_array('Reporter', $roleTypes),
            'can_approve' => in_array('Approver', $roleTypes),
            'is_admin' => in_array('Admin', $roleTypes),
            'roles' => $roleTypes
        ];
    }
}

// function ใหม่สำหรับดึงสิทธิ์แบบง่าย
if (!function_exists('getUserRoles')) {
    /**
     * ดึงรายการสิทธิ์ของผู้ใช้
     */
    function getUserRoles($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        if (!$userId || !$departmentId) {
            return [];
        }

        $db = \Config\Database::connect();

        $result = $db->table('department_user_roles')
            ->select('role_type')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->get()
            ->getResultArray();

        return array_column($result, 'role_type');
    }
}

// function สำหรับตรวจสอบสิทธิ์จาก session
if (!function_exists('hasRole')) {
    /**
     * ตรวจสอบสิทธิ์จาก session
     */
    function hasRole($role)
    {
        $userRoles = session('user_roles') ?? [];
        return in_array($role, $userRoles);
    }
}

if (!function_exists('canReportProgress')) {
    /**
     * ตรวจสอบว่าผู้ใช้สามารถรายงานความคืบหน้าได้หรือไม่
     * ต้องเป็น Leader ของ Key Result + มีสิทธิ์ Reporter/Approver/Admin
     */
    function canReportProgress($keyResultId, $userId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = session('department');

        if (!$userId || !$departmentId) {
            return false;
        }

        // ✅ ตรวจสอบว่าหน่วยงานเป็น Leader ของ Key Result นี้หรือไม่ (เงื่อนไขหลัก)
        if (!isDepartmentLeader($keyResultId, $departmentId)) {
            return false;
        }

        // ✅ ตรวจสอบว่าผู้ใช้มีสิทธิ์รายงาน (Reporter/Approver/Admin)
        return hasRole('Reporter') || hasRole('Approver') || hasRole('Admin');
    }
}

if (!function_exists('canManageEntries')) {
    /**
     * ตรวจสอบว่าผู้ใช้สามารถจัดการรายการข้อมูล (Entries) ได้หรือไม่
     * ทั้ง Leader และ CoWorking สามารถจัดการ Entries ได้
     */
    function canManageEntries($keyResultId, $userId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = session('department');

        if (!$userId || !$departmentId) {
            return false;
        }

        // ✅ ตรวจสอบว่าหน่วยงานมีส่วนเกี่ยวข้องกับ Key Result (Leader หรือ CoWorking)
        $db = \Config\Database::connect();
        $result = $db->table('key_result_departments')
            ->where('key_result_id', $keyResultId)
            ->where('department_id', $departmentId)
            ->whereIn('role', ['Leader', 'CoWorking'])
            ->get()
            ->getRowArray();

        if (!$result) {
            return false;
        }

        // ✅ ตรวจสอบว่าผู้ใช้มีสิทธิ์ขั้นพื้นฐาน (Reporter/Approver/Admin)
        return hasRole('Reporter') || hasRole('Approver') || hasRole('Admin');
    }
}

if (!function_exists('canApproveProgress')) {
    function canApproveProgress($progressId, $userId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = session('department');

        if (!$userId || !$departmentId || !$progressId) {
            return false;
        }

        // ตรวจสอบสิทธิ์ Approver/Admin
        if (!hasRole('Approver') && !hasRole('Admin')) {
            return false;
        }

        // ดึงข้อมูลความคืบหน้า
        $progress = getProgressById($progressId);
        if (!$progress) {
            return false;
        }

        // Admin สามารถอนุมัติรายงานตนเองได้
        if ($progress['created_by'] == $userId && !hasRole('Admin')) {
            return false;
        }

        // ตรวจสอบสถานะ
        if ($progress['status'] !== 'submitted') {
            return false;
        }

        // ตรวจสอบว่า Key Result เป็นของหน่วยงานนี้หรือไม่
        $isLeader = isDepartmentLeader($progress['key_result_id'], $departmentId);

        return $isLeader;
    }
}

if (!function_exists('canViewProgressHistory')) {
    /**
     * ตรวจสอบว่าผู้ใช้สามารถดูประวัติความคืบหน้าได้หรือไม่
     * Leader: ดูได้ทั้งหมด (รวม Draft, Submitted)
     * CoWorking: ดูได้เฉพาะที่ Approved
     * ✅ Admin/StrategicViewer: ดูได้ทั้งหมด
     */
    function canViewProgressHistory($keyResultId, $userId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = session('department');

        if (!$userId || !$departmentId) {
            return ['can_view' => false, 'role' => null, 'can_see_all_status' => false, 'can_see_approved_only' => false];
        }

        // ✅ เพิ่ม: ตรวจสอบสิทธิ์พิเศษสำหรับ Admin และ StrategicViewer ก่อน
        // สอง Role นี้สามารถดูประวัติของทุก Key Result ได้
        if (hasRole('Admin') || isStrategicViewer($userId, $departmentId)) {
            return [
                'can_view' => true,
                'role' => 'StrategicViewer', // กำหนด role พิเศษเพื่อบ่งบอก
                'can_see_all_status' => true, // สามารถเห็นได้ทุกสถานะ
                'can_see_approved_only' => false
            ];
        }

        // --- โค้ดเดิมสำหรับผู้ใช้ระดับหน่วยงาน ---
        $keyResultRole = getKeyResultRole($keyResultId, $departmentId);

        if (!$keyResultRole) {
            return ['can_view' => false, 'role' => null, 'can_see_all_status' => false, 'can_see_approved_only' => false];
        }

        // ✅ ตรวจสอบสิทธิ์ผู้ใช้ (ตัด Admin ออก เพราะเช็คไปแล้ว)
        $hasPermission = hasRole('Reporter') || hasRole('Approver');

        return [
            'can_view' => $hasPermission,
            'role' => $keyResultRole,
            'can_see_all_status' => ($keyResultRole === 'Leader' && $hasPermission),
            'can_see_approved_only' => ($keyResultRole === 'CoWorking' && $hasPermission)
        ];
    }
}
if (!function_exists('isAdmin')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Admin หรือไม่
     */
    function isAdmin($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        $permissions = getDepartmentUserRoles($userId, $departmentId);
        return $permissions && $permissions['is_admin'];
    }
}

if (!function_exists('isApprover')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Approver หรือไม่
     */
    function isApprover($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        $permissions = getDepartmentUserRoles($userId, $departmentId);
        return $permissions && $permissions['can_approve'];
    }
}

if (!function_exists('isReporter')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Reporter หรือไม่
     */
    function isReporter($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        $permissions = getDepartmentUserRoles($userId, $departmentId);
        return $permissions && $permissions['can_report'];
    }
}

if (!function_exists('grantDepartmentRole')) {
    /**
     * เพิ่มสิทธิ์ให้ผู้ใช้ในหน่วยงาน
     */
    function grantDepartmentRole($userId, $departmentId, $roleType, $grantedBy = null)
    {
        $validRoles = ['Reporter', 'Approver', 'Admin'];
        if (!in_array($roleType, $validRoles)) {
            return false;
        }

        $db = \Config\Database::connect();

        // เช็คว่ามีสิทธิ์อยู่แล้วหรือไม่
        $existing = $db->table('department_user_roles')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->where('role_type', $roleType)
            ->get()
            ->getRowArray();

        if ($existing) {
            return true; // มีอยู่แล้ว
        }

        // เพิ่มสิทธิ์ใหม่
        return $db->table('department_user_roles')->insert([
            'user_id' => $userId,
            'department_id' => $departmentId,
            'role_type' => $roleType,
            'created_by' => $grantedBy ?? session('user_id'),
            'created_date' => date('Y-m-d H:i:s')
        ]);
    }
}

if (!function_exists('revokeDepartmentRole')) {
    /**
     * เพิกถอนสิทธิ์ผู้ใช้ในหน่วยงาน
     */
    function revokeDepartmentRole($userId, $departmentId, $roleType)
    {
        $db = \Config\Database::connect();

        return $db->table('department_user_roles')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->where('role_type', $roleType)
            ->delete();
    }
}

if (!function_exists('isDepartmentLeader')) {
    /**
     * ตรวจสอบว่าหน่วยงานเป็น Leader ของ Key Result หรือไม่
     */
    function isDepartmentLeader($keyResultId, $departmentId)
    {
        $db = \Config\Database::connect();

        $result = $db->table('key_result_departments')
            ->where('key_result_id', $keyResultId)
            ->where('department_id', $departmentId)
            ->where('role', 'Leader')
            ->get()
            ->getRowArray();

        return !empty($result);
    }
}

if (!function_exists('getProgressById')) {
    /**
     * ดึงข้อมูลความคืบหน้าตาม ID
     */
    function getProgressById($progressId)
    {
        $db = \Config\Database::connect();

        return $db->table('key_result_progress')
            ->where('id', $progressId)
            ->get()
            ->getRowArray();
    }
}

if (!function_exists('getPendingApprovalsCount')) {
    /**
     * นับจำนวนรายงานที่รอการอนุมัติ
     */
    function getPendingApprovalsCount($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        if (!isApprover($userId, $departmentId)) {
            return 0;
        }

        $db = \Config\Database::connect();

        $builder = $db->table('key_result_progress krp')
            ->join('key_results kr', 'krp.key_result_id = kr.id')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
            ->where('krp.status', 'submitted')
            ->where('krd.department_id', $departmentId)
            ->where('krd.role', 'Leader');

        // ไม่นับรายงานของตัวเอง
        $builder->where('krp.created_by !=', $userId);

        return $builder->countAllResults();
    }
}

if (!function_exists('getPendingReportsCount')) {
    /**
     * นับจำนวน Key Results ที่ผู้ใช้ต้องรายงาน (Leader role)
     */
    function getPendingReportsCount($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        if (!$userId || !$departmentId) {
            return 0;
        }

        // เฉพาะผู้ที่มีสิทธิ์รายงาน
        if (!hasRole('Reporter') && !hasRole('Approver') && !hasRole('Admin')) {
            return 0;
        }

        $db = \Config\Database::connect();

        // นับ Key Results ที่:
        // 1. หน่วยงานเป็น Leader
        // 2. ยังไม่มีรายงาน หรือ รายงานล่าสุดเป็น approved/rejected (ต้องรายงานรอบใหม่)
        $builder = $db->table('key_results kr')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
            ->join('reporting_periods rp', 'rp.is_active = 1', 'left')
            ->where('krd.department_id', $departmentId)
            ->where('krd.role', 'Leader')
            ->where('kr.key_result_year', '2568');

        // ตรวจสอบว่ามีรายงานล่าสุดหรือไม่
        $results = $builder->get()->getResultArray();

        $pendingCount = 0;
        foreach ($results as $keyResult) {
            // ตรวจสอบว่ามี progress ล่าสุดหรือไม่
            $latestProgress = $db->table('key_result_progress')
                ->where('key_result_id', $keyResult['id'])
                ->orderBy('version', 'DESC')
                ->orderBy('created_date', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            // ถ้าไม่มีรายงาน หรือ รายงานล่าสุดเป็น approved/rejected
            if (!$latestProgress || in_array($latestProgress['status'], ['approved', 'rejected'])) {
                $pendingCount++;
            }
        }

        return $pendingCount;
    }
}

if (!function_exists('isDepartmentMember')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็นสมาชิกของหน่วยงานหรือไม่
     */
    function isDepartmentMember($departmentId, $userId = null)
    {
        $userId = $userId ?? session('user_id');

        if (!$userId || !$departmentId) {
            return false;
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        return $user && $user['department_id'] == $departmentId;
    }
}

if (!function_exists('getKeyResultRole')) {
    /**
     * ดึงบทบาทของหน่วยงานใน KKey Result
     */
    function getKeyResultRole($keyResultId, $departmentId = null)
    {
        $departmentId = $departmentId ?? session('department');

        if (!$keyResultId || !$departmentId) {
            return null;
        }

        $db = \Config\Database::connect();

        $result = $db->table('key_result_departments')
            ->select('role')
            ->where('key_result_id', $keyResultId)
            ->where('department_id', $departmentId)
            ->get()
            ->getRowArray();

        return $result ? $result['role'] : null;
    }
}

if (!function_exists('canViewKeyResult')) {
    /**
     * ตรวจสอบว่าผู้ใช้สามารถดู Key Result ได้หรือไม่
     */
    function canViewKeyResult($keyResultId, $userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        // Admin ดูได้ทุก Key Result
        if (isAdmin($userId, $departmentId)) {
            return true;
        }

        // StrategicViewer ดูได้ทุก Key Result
        if (isStrategicViewer($userId, $departmentId)) {
            return true;
        }

        $db = \Config\Database::connect();

        // ตรวจสอบว่าหน่วยงานมีส่วนเกี่ยวข้องกับ Key Result นี้หรือไม่
        $result = $db->table('key_result_departments krd')
            ->where('krd.key_result_id', $keyResultId)
            ->where('krd.department_id', $departmentId)
            ->get()
            ->getRowArray();

        return !empty($result);
    }
}

if (!function_exists('checkPermissionOrFail')) {
    /**
     * ตรวจสอบสิทธิ์ หรือ throw exception
     */
    function checkPermissionOrFail($condition, $message = 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้')
    {
        if (!$condition) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($message);
        }
    }
}

if (!function_exists('getUserRoleNames')) {
    /**
     * แปลง role เป็นชื่อภาษาไทย
     */
    function getUserRoleNames($roles)
    {
        if (empty($roles)) return '-';

        $roleMap = [
            'Admin' => 'ผู้ดูแลระบบ',
            'Approver' => 'ผู้อนุมัติ',
            'Reporter' => 'ผู้รายงาน'
        ];

        $roleArray = explode(',', $roles);
        $translatedRoles = [];

        foreach ($roleArray as $role) {
            $translatedRoles[] = $roleMap[trim($role)] ?? trim($role);
        }

        return implode(', ', $translatedRoles);
    }
}

if (!function_exists('hasRole')) {
    /**
     * ตรวจสอบสิทธิ์จาก session (เร็วกว่า)
     */
    function hasRole($role)
    {
        $userRoles = session('user_roles') ?? [];
        return in_array($role, $userRoles);
    }
}

if (!function_exists('isAdminFast')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Admin หรือไม่ (จาก session)
     */
    function isAdminFast()
    {
        return session('is_admin') ?? false;
    }
}

if (!function_exists('isApproverFast')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Approver หรือไม่ (จาก session)
     */
    function isApproverFast()
    {
        return session('is_approver') ?? false;
    }
}

if (!function_exists('isReporterFast')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Reporter หรือไม่ (จาก session)
     */
    function isReporterFast()
    {
        return session('is_reporter') ?? false;
    }
}

if (!function_exists('refreshUserPermissions')) {
    /**
     * รีเฟรชข้อมูลสิทธิ์ใน session
     */
    function refreshUserPermissions($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        if (!$userId || !$departmentId) {
            return false;
        }

        $roles = getUserRoles($userId, $departmentId);

        // อัพเดท session
        session()->set([
            'user_roles' => $roles,
            'is_admin' => in_array('Admin', $roles),
            'is_approver' => in_array('Approver', $roles),
            'is_reporter' => in_array('Reporter', $roles)
        ]);

        return true;
    }
}


if (!function_exists('isStrategicViewer')) {
    /**
     * ตรวจสอบว่าผู้ใช้เป็น Strategic Viewer หรือไม่
     */
    function isStrategicViewer($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        if (!$userId || !$departmentId) {
            return false;
        }

        $db = \Config\Database::connect();

        $result = $db->table('department_user_roles')
            ->where('user_id', $userId)
            ->where('department_id', $departmentId)
            ->where('role_type', 'StrategicViewer')
            ->get()
            ->getRowArray();

        return !empty($result);
    }
}

if (!function_exists('canSeeDashboardMenu')) {
    /**
     * ตรวจสอบว่าสามารถเห็น Dashboard menu ได้หรือไม่
     * เฉพาะ Admin และ StrategicViewer เท่านั้น
     */
    function canSeeDashboardMenu($userId = null, $departmentId = null)
    {
        $userId = $userId ?? session('user_id');
        $departmentId = $departmentId ?? session('department');

        $roles = getUserRoles($userId, $departmentId);

        return (in_array('Admin', $roles) || in_array('StrategicViewer', $roles));
    }
}

    if (!function_exists('isOnlyReporter')) {
        /**
         * ตรวจสอบว่าควรไป Key Results หรือไม่
         * Reporter (ไม่ว่าจะมี Approver ผสมหรือไม่) แต่ไม่ใช่ Admin/StrategicViewer
         */
        function shouldGoToKeyResults($userId = null, $departmentId = null)
        {
            $userId = $userId ?? session('user_id');
            $departmentId = $departmentId ?? session('department');

            $roles = getUserRoles($userId, $departmentId);

            // ถ้าเป็น Admin หรือ StrategicViewer → ไป Dashboard
            if (in_array('Admin', $roles) || in_array('StrategicViewer', $roles)) {
                return false;
            }

            // ถ้ามี Reporter → ไป Key Results
            return in_array('Reporter', $roles);
        }
    }

if (!function_exists('canViewStrategicDashboard')) {
    /**
     * ตรวจสอบว่าสามารถเข้าถึง Strategic Dashboard ได้หรือไม่
     * เงื่อนไข: Strategic Viewer หรือ Admin
     */
    function canViewStrategicDashboard($userId = null)
    {
        $userId = $userId ?? session('user_id');

        // Admin สามารถเข้าถึงได้เสมอ
        if (hasRole('Admin')) {
            return true;
        }

        // ตรวจสอบ Strategic Viewer role
        return isStrategicViewer($userId);
    }
}

if (!function_exists('getStrategicViewPermissions')) {
    /**
     * ดึงข้อมูลสิทธิ์การดู Strategic Dashboard
     */
    function getStrategicViewPermissions($userId = null)
    {
        $userId = $userId ?? session('user_id');

        return [
            'can_view_all_departments' => canViewStrategicDashboard($userId),
            'can_view_draft_reports' => hasRole('Admin'), // เฉพาะ Admin เท่านั้น
            'can_export_data' => canViewStrategicDashboard($userId),
            'can_view_detailed_progress' => true, // ดูได้แต่เฉพาะที่ approved
            'is_strategic_viewer' => isStrategicViewer($userId),
            'is_admin' => hasRole('Admin')
        ];
    }
}

if (!function_exists('grantStrategicViewerRole')) {
    /**
     * เพิ่มสิทธิ์ Strategic Viewer ให้ผู้ใช้
     */
    function grantStrategicViewerRole($userId, $departmentId, $grantedBy = null)
    {
        return grantDepartmentRole($userId, $departmentId, 'StrategicViewer', $grantedBy);
    }
}

if (!function_exists('revokeStrategicViewerRole')) {
    /**
     * เพิกถอนสิทธิ์ Strategic Viewer
     */
    function revokeStrategicViewerRole($userId, $departmentId)
    {
        return revokeDepartmentRole($userId, $departmentId, 'StrategicViewer');
    }
}