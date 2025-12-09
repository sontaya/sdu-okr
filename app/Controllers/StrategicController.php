<?php
namespace App\Controllers;

use App\Models\KeyresultModel;
use App\Models\ProgressModel;
use App\Models\UserModel;

class StrategicController extends TemplateController
{
    protected $allowed = [];
    private $keyresultModel;
    private $progressModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        // ตรวจสอบสิทธิ์ Strategic Viewer
        if (!canViewStrategicDashboard()) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $this->keyresultModel = new KeyresultModel();
        $this->progressModel = new ProgressModel();

        helper(['permission']);
    }

    /**
     * หน้าแรก Strategic Dashboard - แสดงภาพรวมระดับมหาวิทยาลัย
     */
    public function index()
    {
        return $this->overview();
    }

    /**
     * Strategic Overview - แสดง Key Results ทั้งหมดพร้อม Filter
     */
public function overview()
    {
        // รับ parameters จาก URL
        $filters = [
            'year' => $this->request->getGet('year') ?? '2568',
            'objective_group_id' => $this->request->getGet('group') ?? null,
            'department_id' => $this->request->getGet('dept') ?? null,
            'progress_status' => $this->request->getGet('status') ?? null,
            'role_type' => $this->request->getGet('role') ?? null,
            'reporting_period_id' => $this->request->getGet('period') ?? null,
            'keyword' => $this->request->getGet('search') ?? null
        ];

        // ✅ ดึงข้อมูล Key Results จาก Model
        $keyresults = $this->keyresultModel->getStrategicViewKeyResults($filters);

        // (Optional) หากยังต้องการกรอง draft reports ออกสำหรับ StrategicViewer ที่ไม่ใช่ Admin
        $permissions = getStrategicViewPermissions();
        if (!$permissions['can_view_draft_reports']) {
            $keyresults = array_filter($keyresults, function($item) {
                return $item['progress_status'] !== 'draft';
            });
        }

        // สร้าง Summary Statistics
        $stats = $this->generateStrategicStats($keyresults, $filters);

        // ดึงข้อมูลสำหรับ Filter Options
        $filterOptions = $this->keyresultModel->getStrategicFilterOptions();

        // ข้อมูลสำหรับ View
        $this->data = array_merge($this->data, [
            'title' => 'Strategic Overview - OKR Dashboard',
            'keyresults' => array_values($keyresults), // re-index array after filter
            'stats' => $stats,
            'filters' => $filters,
            'filter_options' => $filterOptions,
            'strategic_permissions' => $permissions,
            'cssSrc' => [
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css',
                'assets/css/strategic-dashboard.css'
            ],
            'jsSrc' => [
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
                'assets/js/strategic/overview.js'
            ]
        ]);

        $this->contentTemplate = 'strategic/overview';
        return $this->render();
    }


    /**
     * สร้าง Summary Statistics สำหรับ Strategic Dashboard
     */
    private function generateStrategicStats($keyresults, $filters)
    {
        $stats = [
            'total_key_results' => count($keyresults),
            'by_objective_group' => [],
            'by_department' => [],
            'by_status' => [
                'no_report' => 0,
                'draft' => 0,
                'submitted' => 0,
                'approved' => 0,
                'rejected' => 0
            ],
            'by_role' => [
                'Leader' => 0,
                'CoWorking' => 0
            ],
            'progress_summary' => [
                'avg_progress' => 0,
                'on_track' => 0,    // >= 75%
                'at_risk' => 0,     // 50-74%
                'behind' => 0,      // < 50%
                'total_with_progress' => 0
            ],
            'reporting_activity' => [
                'last_7_days' => 0,
                'last_30_days' => 0,
                'overdue_reports' => 0
            ]
        ];

        $totalProgress = 0;
        $progressCount = 0;

        foreach ($keyresults as $item) {
            // Count by Objective Group
            $groupName = $item['og_name'];
            if (!isset($stats['by_objective_group'][$groupName])) {
                $stats['by_objective_group'][$groupName] = 0;
            }
            $stats['by_objective_group'][$groupName]++;

            // Count by Department
            if (!empty($item['departments'])) {
                foreach ($item['departments'] as $dept) {
                    $deptName = $dept['short_name'];
                    if (!isset($stats['by_department'][$deptName])) {
                        $stats['by_department'][$deptName] = 0;
                    }
                    $stats['by_department'][$deptName]++;
                }
            }

            // Count by Status
            $status = $item['progress_status'];
            if (isset($stats['by_status'][$status])) {
                $stats['by_status'][$status]++;
            }

            // Count by Role
            $role = $item['key_result_dep_role'];
            if (isset($stats['by_role'][$role])) {
                $stats['by_role'][$role]++;
            }

            // Progress Summary
            if ($item['progress_percentage'] !== null) {
                $progressCount++;
                $totalProgress += $item['progress_percentage'];
                $stats['progress_summary']['total_with_progress']++;

                $percentage = $item['progress_percentage'];
                if ($percentage >= 75) {
                    $stats['progress_summary']['on_track']++;
                } elseif ($percentage >= 50) {
                    $stats['progress_summary']['at_risk']++;
                } else {
                    $stats['progress_summary']['behind']++;
                }
            }

            // Reporting Activity
            if ($item['progress_updated_date']) {
                $daysSince = $this->calculateDaysSince($item['progress_updated_date']);
                if ($daysSince <= 7) {
                    $stats['reporting_activity']['last_7_days']++;
                }
                if ($daysSince <= 30) {
                    $stats['reporting_activity']['last_30_days']++;
                }
            }
        }

        // คำนวณ Average Progress
        $stats['progress_summary']['avg_progress'] = $progressCount > 0 ? round($totalProgress / $progressCount, 1) : 0;

        return $stats;
    }



    /**
     * คำนวณจำนวนวันที่ผ่านมา
     */
    private function calculateDaysSince($date)
    {
        $then = new \DateTime($date);
        $now = new \DateTime();
        return $now->diff($then)->days;
    }

    /**
     * API: ส่งออกข้อมูลเป็น Excel/PDF
     */
    public function export()
    {
        if (!getStrategicViewPermissions()['can_export_data']) {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'คุณไม่มีสิทธิ์ในการส่งออกข้อมูล']);
        }

        $format = $this->request->getGet('format') ?? 'excel';
        $filters = [
            'year' => $this->request->getGet('year') ?? '2568',
            'objective_group_id' => $this->request->getGet('group') ?? null,
            'department_id' => $this->request->getGet('dept') ?? null,
            'progress_status' => $this->request->getGet('status') ?? null
        ];

        // ✅ เรียกใช้ Method เดียวกันกับหน้า Overview
        $keyresults = $this->keyresultModel->getStrategicViewKeyResults($filters);

        // สร้างไฟล์สำหรับดาวน์โหลด
        $filename = 'strategic_overview_' . date('Y-m-d_His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');

        // TODO: Implement actual export logic
        return $this->response->setJSON([
            'success' => true,
            'message' => 'กำลังเตรียมไฟล์สำหรับดาวน์โหลด...',
            'filename' => $filename
        ]);
    }

    /**
     * API: ดึงข้อมูลสำหรับ AJAX refresh
     */
    public function api()
    {
        $action = $this->request->getGet('action');

        switch ($action) {
            case 'refresh_stats':
                return $this->apiRefreshStats();
            case 'get_department_details':
                return $this->apiGetDepartmentDetails();
            default:
                return $this->response->setStatusCode(400)
                    ->setJSON(['error' => 'Invalid action']);
        }
    }

    private function apiRefreshStats()
    {
        $filters = [
            'year' => $this->request->getGet('year') ?? '2568',
            'objective_group_id' => $this->request->getGet('group') ?? null,
            'department_id' => $this->request->getGet('dept') ?? null,
            'progress_status' => $this->request->getGet('status') ?? null
        ];

        $keyresults = $this->keyresultModel->getStrategicViewKeyResults($filters);
        $stats = $this->generateStrategicStats($keyresults, $filters);

        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats,
            'total_results' => count($keyresults)
        ]);
    }

    private function apiGetDepartmentDetails()
    {
        $departmentId = $this->request->getGet('department_id');

        if (!$departmentId) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'Department ID required']);
        }

        $filters = ['department_id' => $departmentId, 'year' => '2568'];
        $keyresults = $this->keyresultModel->getStrategicViewKeyResults($filters);
        $stats = $this->generateStrategicStats($keyresults, $filters);

        return $this->response->setJSON([
            'success' => true,
            'department_stats' => $stats,
            'key_results' => array_slice($keyresults, 0, 10) // จำกัดแค่ 10 รายการ
        ]);
    }
}