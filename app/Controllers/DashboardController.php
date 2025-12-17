<?php

namespace App\Controllers;

use App\Models\KeyresultModel;
use App\Models\DashboardModel;
use App\Models\KeyResultEntryModel;
use CodeIgniter\Controller;

class DashboardController extends TemplateController
{
    protected $allowed = [];
    protected $dashboardModel;
    protected $keyresultModel;

    public function initController($request, $response, $logger)
    {
        // ✅ เรียก parent initController ก่อน
        parent::initController($request, $response, $logger);

        // ✅ ย้าย model initialization มาไว้ใน initController แทน
        $this->dashboardModel = new DashboardModel();
        $this->keyresultModel = new KeyresultModel();
    }

    /**
     * Executive Dashboard - หน้าหลักสำหรับผู้บริหาร (Mock Data Version)
     */
    public function index()
    {

        $year = '2568'; // Fixed year
        $quarter = '4'; // Fixed quarter - Q4 only

        // 1. Try to get real data for Overview to check if system has data
        $overview = $this->dashboardModel->getOrganizationOverview($year, $quarter);

        // 2. Decide whether to use Real Data or Mock Data
        // Enable mock mode via URL ?mode=mock or if DB is empty
        $forceMock = $this->request->getGet('mode') === 'mock';
        $useMock = $forceMock || ($overview['total_key_results'] ?? 0) == 0;

        if ($useMock) {
            // Hybrid Mock: Use Real Structure + Mock Metrics
            $dashboardData = [
                'overview' => $this->injectMockOverview($overview),
                'strategic_goals' => $this->injectMockStrategic($this->dashboardModel->getAllStrategicGoalsProgress($year, $quarter)),
                'departments' => $this->injectMockDepartments($this->dashboardModel->getAllDepartmentsProgress($year, $quarter)['departments']),
                'trends' => [],
                'risks' => [],
                'analytics' => [],
                'recent_activities' => [],
                'upcoming_deadlines' => []
            ];

            // Format departments for view (needs pagination structure)
            $dashboardData['departments'] = [
                'departments' => $dashboardData['departments'],
                'pagination' => []
            ];
        } else {
            $dashboardData = [
                'overview' => $overview,
                'strategic_goals' => $this->dashboardModel->getStrategicGoalsProgress($year, $quarter),
                'departments' => $this->dashboardModel->getDepartmentProgress($year, $quarter, ['limit' => 100]),
                // Keep these generic/empty as they are removed from View,
                // but kept here to prevent undefined index error if referenced elsewhere
                'trends' => [],
                'risks' => [],
                'analytics' => [],
                'recent_activities' => [],
                'upcoming_deadlines' => []
            ];
        }

        $this->data = array_merge($this->data, [
            'title' => 'OKR Executive Dashboard',
            'year' => $year,
            'quarter' => $quarter,
            'overview' => $dashboardData['overview'],
            'strategic_goals' => $dashboardData['strategic_goals'],
            'departments' => $dashboardData['departments'],
            'trends' => $dashboardData['trends'],
            'risks' => $dashboardData['risks'],
            'analytics' => $dashboardData['analytics'],
            'recent_activities' => $dashboardData['recent_activities'],
            'upcoming_deadlines' => $dashboardData['upcoming_deadlines'],
            'cssSrc' => [
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css',
                'assets/themes/metronic38/assets/plugins/global/plugins.bundle.css'
            ],
            'jsSrc' => [
                'assets/themes/metronic38/assets/plugins/global/plugins.bundle.js',
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
                'assets/js/dashboard/executive.js'
            ]
        ]);

        $this->contentTemplate = 'dashboard/executive';
        return $this->render();
    }

    /**
     * สร้างข้อมูลจำลองสำหรับ Dashboard
     */
    private function getMockDashboardData()
    {
        return [
            // 1. ภาพรวมองค์กร
            'overview' => [
                'total_key_results' => 64,
                'total_departments' => 30,
                'overall_progress' => 74.2,
                'on_track' => 32,
                'at_risk' => 18,
                'behind' => 9,
                'not_started' => 5,
                'reporting_rate' => 87.5
            ],

            // 2. Strategic Goals Progress
            'strategic_goals' => [
                [
                    'id' => 1,
                    'name' => 'Student Alumni',
                    'total_key_results' => 12,
                    'avg_progress' => 82.1,
                    'on_track_count' => 8,
                    'at_risk_count' => 3,
                    'behind_count' => 1
                ],
                [
                    'id' => 2,
                    'name' => 'Academic Excellence',
                    'total_key_results' => 16,
                    'avg_progress' => 71.4,
                    'on_track_count' => 9,
                    'at_risk_count' => 5,
                    'behind_count' => 2
                ],
                [
                    'id' => 3,
                    'name' => 'Research Innovation',
                    'total_key_results' => 14,
                    'avg_progress' => 65.8,
                    'on_track_count' => 6,
                    'at_risk_count' => 5,
                    'behind_count' => 3
                ],
                [
                    'id' => 4,
                    'name' => 'Community Engagement',
                    'total_key_results' => 12,
                    'avg_progress' => 78.3,
                    'on_track_count' => 7,
                    'at_risk_count' => 3,
                    'behind_count' => 2
                ],
                [
                    'id' => 5,
                    'name' => 'Next Learning Ecosystem',
                    'total_key_results' => 10,
                    'avg_progress' => 58.9,
                    'on_track_count' => 2,
                    'at_risk_count' => 2,
                    'behind_count' => 6
                ]
            ],

            // 3. Department Progress
            'departments' => [
                'departments' => [
                    ['id' => 18, 'short_name' => 'สทส', 'name' => 'วิทยาลัยเทคโนโลยีสารสนเทศ', 'total_key_results' => 8, 'leader_count' => 3, 'coworking_count' => 5, 'avg_progress' => 87.2, 'last_update' => '2025-06-08 14:30:00'],
                    ['id' => 20, 'short_name' => 'สวท', 'name' => 'วิทยาลัยวิทยาศาสตร์และเทคโนโลยี', 'total_key_results' => 6, 'leader_count' => 2, 'coworking_count' => 4, 'avg_progress' => 84.1, 'last_update' => '2025-06-08 11:20:00'],
                    ['id' => 16, 'short_name' => 'สนม', 'name' => 'วิทยาลัยนานาชาติการท่องเที่ยว', 'total_key_results' => 5, 'leader_count' => 2, 'coworking_count' => 3, 'avg_progress' => 81.7, 'last_update' => '2025-06-07 16:45:00'],
                    ['id' => 17, 'short_name' => 'สภศ', 'name' => 'วิทยาลัยพยาบาลบรมราชชนนี', 'total_key_results' => 7, 'leader_count' => 3, 'coworking_count' => 4, 'avg_progress' => 79.4, 'last_update' => '2025-06-08 09:15:00'],
                    ['id' => 19, 'short_name' => 'สวพ', 'name' => 'วิทยาลัยสวนพฤกษศาสตร์', 'total_key_results' => 4, 'leader_count' => 1, 'coworking_count' => 3, 'avg_progress' => 76.8, 'last_update' => '2025-06-07 13:20:00'],
                    ['id' => 1, 'short_name' => 'คทท', 'name' => 'คณะเทคโนโลยีการท่องเที่ยว', 'total_key_results' => 6, 'leader_count' => 2, 'coworking_count' => 4, 'avg_progress' => 74.2, 'last_update' => '2025-06-08 10:30:00'],
                    ['id' => 11, 'short_name' => 'สวส', 'name' => 'วิทยาลัยวิทยาศาสตร์สุขภาพ', 'total_key_results' => 5, 'leader_count' => 2, 'coworking_count' => 3, 'avg_progress' => 72.1, 'last_update' => '2025-06-07 15:10:00'],
                    ['id' => 22, 'short_name' => 'สนช', 'name' => 'สำนักงานวิเทศสัมพันธ์', 'total_key_results' => 9, 'leader_count' => 4, 'coworking_count' => 5, 'avg_progress' => 69.7, 'last_update' => '2025-06-08 08:45:00'],
                    ['id' => 12, 'short_name' => 'ศหห', 'name' => 'วิทยาลัยศิลปศาสตร์', 'total_key_results' => 4, 'leader_count' => 1, 'coworking_count' => 3, 'avg_progress' => 67.3, 'last_update' => '2025-06-07 12:00:00'],
                    ['id' => 15, 'short_name' => 'ศลป', 'name' => 'วิทยาลัยดุรดิลาศิลป์', 'total_key_results' => 3, 'leader_count' => 1, 'coworking_count' => 2, 'avg_progress' => 64.8, 'last_update' => '2025-06-06 14:20:00']
                ],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total' => 30,
                    'total_pages' => 3
                ]
            ],

            // 4. Trend Data (Q1-Q4 2568)
            'trends' => [
                ['period' => 'Q1 2568', 'Student Alumni' => 65, 'Academic Excellence' => 60, 'Research Innovation' => 55, 'Community Engagement' => 70, 'Next Learning Ecosystem' => 45],
                ['period' => 'Q2 2568', 'Student Alumni' => 72, 'Academic Excellence' => 65, 'Research Innovation' => 58, 'Community Engagement' => 74, 'Next Learning Ecosystem' => 48],
                ['period' => 'Q3 2568', 'Student Alumni' => 78, 'Academic Excellence' => 68, 'Research Innovation' => 62, 'Community Engagement' => 76, 'Next Learning Ecosystem' => 52],
                ['period' => 'Q4 2568', 'Student Alumni' => 82, 'Academic Excellence' => 71, 'Research Innovation' => 66, 'Community Engagement' => 78, 'Next Learning Ecosystem' => 59]
            ],

            // 5. Risk Analysis
            'risks' => [
                'critical_key_results' => [
                    ['id' => 34, 'name' => 'ระบบฐานข้อมูลโจทย์วิจัยของมหาวิทยาลัย', 'progress_percentage' => 34.2, 'department' => 'สนช', 'objective_group' => 'Research Innovation'],
                    ['id' => 28, 'name' => 'แผนการพัฒนาเครือข่ายการวิจัยและนวัตกรรม', 'progress_percentage' => 42.1, 'department' => 'สวท', 'objective_group' => 'Research Innovation'],
                    ['id' => 33, 'name' => 'การร่วมลงทุนของภาคธุรกิจในงานวิจัย', 'progress_percentage' => 28.7, 'department' => 'สนช', 'objective_group' => 'Research Innovation'],
                    ['id' => 38, 'name' => 'วารสารที่ได้รับการรับรองตามมาตรฐานระดับสากล', 'progress_percentage' => 51.3, 'department' => 'สนช', 'objective_group' => 'Academic Excellence'],
                    ['id' => 61, 'name' => 'แพลตฟอร์มการเรียนรู้ตลอดชีวิต', 'progress_percentage' => 45.8, 'department' => 'สทส', 'objective_group' => 'Next Learning Ecosystem']
                ],
                'overdue_reports' => [
                    ['id' => 25, 'name' => 'แผนและคู่มือการประเมินผลตอบแทนทางสังคม', 'department' => 'สวพ', 'end_date' => '2025-06-01', 'days_overdue' => 7],
                    ['id' => 52, 'name' => 'แผนการพัฒนาแหล่งเรียนรู้สวนพฤกษศาสตร์', 'department' => 'สวพ', 'end_date' => '2025-06-03', 'days_overdue' => 5],
                    ['id' => 41, 'name' => 'แผนการดำเนินงานด้านธุรกิจวิชาการ', 'department' => 'สนช', 'end_date' => '2025-06-05', 'days_overdue' => 3]
                ],
                'summary' => [
                    'critical_count' => 5,
                    'overdue_count' => 3
                ]
            ],

            // 6. Analytics
            'analytics' => [
                'reporting_stats' => [
                    'total_key_results' => 64,
                    'total_reports' => 56,
                    'avg_updates_per_kr' => 3.2,
                    'avg_reporting_delay' => 2.1
                ],
                'best_performers' => [
                    ['name' => 'Student Alumni', 'avg_progress' => 82.1, 'total_key_results' => 12],
                    ['name' => 'Community Engagement', 'avg_progress' => 78.3, 'total_key_results' => 12],
                    ['name' => 'Academic Excellence', 'avg_progress' => 71.4, 'total_key_results' => 16]
                ],
                'insights' => [
                    ['type' => 'success', 'icon' => '🏆', 'message' => 'Strategic Goal "Student Alumni" มีประสิทธิภาพสูงสุด (82.1%)'],
                    ['type' => 'info', 'icon' => '📊', 'message' => 'หน่วยงาน สทส มีผลงานโดดเด่น (87.2%)'],
                    ['type' => 'warning', 'icon' => '⚠️', 'message' => '"Next Learning Ecosystem" ต้องการการสนับสนุนเพิ่มเติม'],
                    ['type' => 'info', 'icon' => '⏰', 'message' => 'ช่วง Q4 มีการรายงานสูงขึ้น 15% จากไตรมาสที่แล้ว']
                ]
            ],

            // 7. Recent Activities
            'recent_activities' => [
                ['key_result_name' => 'มีแผนพัฒนาผู้เรียนให้มีทักษะด้านเทคโนโลยี', 'department' => 'สทส', 'progress_percentage' => 87.2, 'updated_date' => '2025-06-08 14:30:00', 'activity_type' => 'progress_update'],
                ['key_result_name' => 'บัณฑิตมีทักษะดิจิทัลสำหรับการทำงาน', 'department' => 'สวท', 'progress_percentage' => 84.1, 'updated_date' => '2025-06-08 11:20:00', 'activity_type' => 'progress_update'],
                ['key_result_name' => 'หลักสูตรที่เกี่ยวข้องกับอัตลักษณ์', 'department' => 'สนม', 'progress_percentage' => 81.7, 'updated_date' => '2025-06-07 16:45:00', 'activity_type' => 'progress_update'],
                ['key_result_name' => 'ผู้เรียนมีส่วนร่วมในการบริการชุมชน', 'department' => 'สภศ', 'progress_percentage' => 79.4, 'updated_date' => '2025-06-08 09:15:00', 'activity_type' => 'progress_update'],
                ['key_result_name' => 'การวิจัยและพัฒนาที่ช่วยเหลือชุมชน', 'department' => 'สวพ', 'progress_percentage' => 76.8, 'updated_date' => '2025-06-07 13:20:00', 'activity_type' => 'progress_update'],
            ],

            // 8. Upcoming Deadlines
            'upcoming_deadlines' => [
                ['quarter_name' => 'Q4 2568', 'end_date' => '2025-09-30', 'days_remaining' => 115, 'pending_reports' => 8],
                ['quarter_name' => 'Q1 2569', 'end_date' => '2025-12-31', 'days_remaining' => 207, 'pending_reports' => 64]
            ]
        ];
    }

    /**
     * Department Dashboard - หน้าสำหรับหัวหน้าหน่วยงาน
     */
    public function department()
    {
        $year = $this->request->getGet('year') ?? '2568';
        $quarter = $this->request->getGet('quarter') ?? null;
        $departmentId = session('department');

        // ข้อมูลเฉพาะหน่วยงาน
        $departmentOverview = $this->dashboardModel->getDepartmentOverview($departmentId, $year, $quarter);
        $keyResultDetails = $this->dashboardModel->getDepartmentKeyResults($departmentId, $year, $quarter);
        $progressHistory = $this->dashboardModel->getDepartmentProgressHistory($departmentId, $year);
        $actionItems = $this->dashboardModel->getDepartmentActionItems($departmentId, $year, $quarter);

        $this->data = array_merge($this->data, [
            'title' => 'Department Dashboard',
            'year' => $year,
            'quarter' => $quarter,
            'department_id' => $departmentId,
            'overview' => $departmentOverview,
            'key_results' => $keyResultDetails,
            'progress_history' => $progressHistory,
            'action_items' => $actionItems,
            'cssSrc' => [
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'
            ],
            'jsSrc' => [
                'assets/themes/metronic38/assets/plugins/global/plugins.bundle.js',
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
                'assets/js/dashboard/department.js'
            ]
        ]);

        $this->contentTemplate = 'dashboard/department';
        return $this->render();
    }

    /**
     * Progress Dashboard - หน้าติดตามความคืบหน้าแบบ Real-time
     */
    public function progress()
    {
        $year = $this->request->getGet('year') ?? '2568';
        $quarter = $this->request->getGet('quarter') ?? null;
        $objectiveGroupId = $this->request->getGet('group_id') ?? null;

        // Real-time progress data
        $realTimeData = $this->dashboardModel->getRealTimeProgress($year, $quarter, $objectiveGroupId);
        $recentActivities = $this->dashboardModel->getRecentActivities(20);
        $upcomingDeadlines = $this->dashboardModel->getUpcomingDeadlines($year, $quarter);

        $this->data = array_merge($this->data, [
            'title' => 'Progress Tracking Dashboard',
            'year' => $year,
            'quarter' => $quarter,
            'group_id' => $objectiveGroupId,
            'realtime_data' => $realTimeData,
            'recent_activities' => $recentActivities,
            'upcoming_deadlines' => $upcomingDeadlines,
            'cssSrc' => [
                'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'
            ],
            'jsSrc' => [
                'assets/themes/metronic38/assets/plugins/global/plugins.bundle.js',
                'assets/js/dashboard/progress.js'
            ]
        ]);

        $this->contentTemplate = 'dashboard/progress';
        return $this->render();
    }

    /**
     * API สำหรับ AJAX calls
     */

    /**
     * API: ดึงข้อมูลภาพรวมแบบ Real-time (Mock Version)
     */
    public function apiOverview()
    {
        $year = '2568';
        $quarter = '4';

        // Try to get real data
        $overview = $this->dashboardModel->getOrganizationOverview($year, $quarter);

        // Fallback to mock if empty or forced
        $forceMock = $this->request->getGet('mode') === 'mock';
        if ($forceMock || ($overview['total_key_results'] ?? 0) == 0) {
            $overview = $this->injectMockOverview($overview);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $overview,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * API: ดึงข้อมูลเทรนด์สำหรับ Chart (Mock Version)
     */
    public function apiTrends()
    {
        $type = $this->request->getGet('type') ?? 'quarterly';
        $mockData = $this->getMockDashboardData();

        // Transform data for chart format
        $chartData = [];
        foreach ($mockData['trends'] as $trend) {
            $chartData[] = [
                'period' => $trend['period'],
                'objective_group_name' => 'Student Alumni',
                'avg_progress' => $trend['Student Alumni']
            ];
            $chartData[] = [
                'period' => $trend['period'],
                'objective_group_name' => 'Academic Excellence',
                'avg_progress' => $trend['Academic Excellence']
            ];
            $chartData[] = [
                'period' => $trend['period'],
                'objective_group_name' => 'Research Innovation',
                'avg_progress' => $trend['Research Innovation']
            ];
            $chartData[] = [
                'period' => $trend['period'],
                'objective_group_name' => 'Community Engagement',
                'avg_progress' => $trend['Community Engagement']
            ];
            $chartData[] = [
                'period' => $trend['period'],
                'objective_group_name' => 'Next Learning Ecosystem',
                'avg_progress' => $trend['Next Learning Ecosystem']
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData,
            'type' => $type
        ]);
    }

    /**
     * API: ดึงข้อมูลหน่วยงานแบบ Paginated (Mock Version)
     */
    public function apiDepartments()
    {
        $year = '2568';
        $quarter = '4';

        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 10;
        $sortBy = $this->request->getGet('sort') ?? 'progress_desc';

        // Check if we need to use mock data
        $overview = $this->dashboardModel->getOrganizationOverview($year, $quarter);

        $forceMock = $this->request->getGet('mode') === 'mock';
        $useMock = $forceMock || ($overview['total_key_results'] ?? 0) == 0;

        if ($useMock) {
            // Get REAL departments but without limit first to sort correctly
            $allDepartments = $this->dashboardModel->getAllDepartmentsProgress($year, $quarter); // Get ALL departments including empty ones
            $departments = $this->injectMockDepartments($allDepartments['departments']);

            // Sort logic (Must re-sort because progress changed)
            if ($sortBy === 'progress_desc') {
                usort($departments, function($a, $b) {
                    return $b['avg_progress'] <=> $a['avg_progress'];
                });
            } elseif ($sortBy === 'name_asc') {
                usort($departments, function($a, $b) {
                    return strcasecmp($a['short_name'], $b['short_name']);
                });
            }

            // Paginate manually
            $offset = ($page - 1) * $limit;
            $paginatedDepartments = array_slice($departments, $offset, $limit);

            $result = [
                'departments' => $paginatedDepartments,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => count($departments),
                    'total_pages' => ceil(count($departments) / $limit)
                ]
            ];
        } else {
            // Real Data Logic (Database handles sort/limit)
            $result = $this->dashboardModel->getDepartmentProgress($year, $quarter, [
                'page' => $page,
                'limit' => $limit,
                'sort' => $sortBy
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $result['departments'],
            'pagination' => $result['pagination']
        ]);
    }

    /**
     * API: ดึงรายละเอียด Key Result แบบ Modal (Mock Version)
     */
    public function apiKeyResultDetail($keyResultId)
    {
        // Mock key result detail data
        $mockKeyResult = [
            'id' => $keyResultId,
            'name' => 'ระบบฐานข้อมูลโจทย์วิจัยของมหาวิทยาลัย/ความเชี่ยวชาญของอาจารย์นักวิจัย',
            'template_name' => 'แพลตฟอร์มพัฒนาธุรกิจนวัตกรรม',
            'objective_name' => 'ส่งเสริมธุรกิจนวัตกรรม',
            'objective_group_name' => 'Research Innovation',
            'target_value' => 1,
            'target_unit' => 'ฐานข้อมูล',
            'progress_percentage' => 34.2,
            'progress_description' => 'ดำเนินการจัดเก็บข้อมูลโจทย์วิจัยจากทุกหน่วยงาน อยู่ในขั้นตอนการพัฒนาระบบฐานข้อมูล',
            'last_progress_update' => '2025-06-05 14:30:00',
            'departments' => [
                ['role' => 'Leader', 'short_name' => 'สนช', 'name' => 'สำนักงานวิเทศสัมพันธ์'],
                ['role' => 'CoWorking', 'short_name' => 'สทส', 'name' => 'วิทยาลัยเทคโนโลยีสารสนเทศ'],
                ['role' => 'CoWorking', 'short_name' => 'สวท', 'name' => 'วิทยาลัยวิทยาศาสตร์และเทคโนโลยี']
            ],
            'progress_history' => [
                ['quarter_name' => 'Q1 2568', 'progress_percentage' => 15.0, 'updated_date' => '2024-12-30 10:00:00'],
                ['quarter_name' => 'Q2 2568', 'progress_percentage' => 22.5, 'updated_date' => '2025-03-30 14:30:00'],
                ['quarter_name' => 'Q3 2568', 'progress_percentage' => 28.7, 'updated_date' => '2025-06-30 16:45:00'],
                ['quarter_name' => 'Q4 2568', 'progress_percentage' => 34.2, 'updated_date' => '2025-06-05 14:30:00']
            ]
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $mockKeyResult
        ]);
    }

    /**
     * API: Export Dashboard Data (Mock Version)
     */
    public function apiExport()
    {
        $type = $this->request->getGet('type') ?? 'excel';
        $scope = $this->request->getGet('scope') ?? 'overview';

        // Simulate file generation
        $filename = "okr_dashboard_{$scope}_2568_Q4_" . date('Ymd_His') . ".{$type}";

        return $this->response->setJSON([
            'success' => true,
            'message' => 'กำลังเตรียมไฟล์สำหรับดาวน์โลด...',
            'filename' => $filename,
            'download_url' => '/mock-download/' . $filename
        ]);
    }

    /**
     * API: Dashboard Settings
     */
    public function apiSettings()
    {
        if ($this->request->getMethod() === 'POST') {
            // Save settings
            $settings = $this->request->getJSON(true);
            $userId = session('user_id');

            $saved = $this->dashboardModel->saveDashboardSettings($userId, $settings);

            return $this->response->setJSON([
                'success' => $saved,
                'message' => $saved ? 'บันทึกการตั้งค่าสำเร็จ' : 'เกิดข้อผิดพลาดในการบันทึก'
            ]);
        } else {
            // Get settings
            $userId = session('user_id');
            $settings = $this->dashboardModel->getDashboardSettings($userId);

            return $this->response->setJSON([
                'success' => true,
                'data' => $settings
            ]);
        }
    }

    /**
     * Private Methods สำหรับการ Export
     */

    private function exportToExcel($data, $year, $quarter, $scope)
    {
        // Implementation สำหรับ Excel Export
        // ใช้ PhpSpreadsheet หรือ library อื่นๆ

        $filename = "okr_dashboard_{$scope}_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_" . date('Ymd_His') . ".xlsx";

        // สร้างไฟล์ Excel จาก $data
        // ...

        return $this->response->download($filename, null);
    }

    private function exportToPDF($data, $year, $quarter, $scope)
    {
        // Implementation สำหรับ PDF Export
        // ใช้ TCPDF หรือ DomPDF

        $filename = "okr_dashboard_{$scope}_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_" . date('Ymd_His') . ".pdf";

        // สร้างไฟล์ PDF จาก $data
        // ...

        return $this->response->download($filename, null);
    }

    private function exportToCSV($data, $year, $quarter, $scope)
    {
        // Implementation สำหรับ CSV Export

        $filename = "okr_dashboard_{$scope}_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_" . date('Ymd_His') . ".csv";

        // สร้างไฟล์ CSV จาก $data
        // ...

        return $this->response->download($filename, null);
    }
    private function injectMockOverview($overview)
    {
        $total = $overview['total_key_results'] ?? 0;

        if ($total == 0) {
            // Totally empty DB, use full mock
            return $this->getMockDashboardData()['overview'];
        }

        // Generate random status breakdown that sums up to total
        $onTrack = rand(floor($total * 0.4), floor($total * 0.7));
        $remaining = $total - $onTrack;

        $atRisk = rand(floor($remaining * 0.3), floor($remaining * 0.6));
        $remaining = $remaining - $atRisk;

        $behind = rand(0, $remaining);
        $notStarted = $remaining - $behind;

        $overview['overall_progress'] = rand(65, 85) + (rand(0, 9) / 10); // 65.0 - 85.9%
        $overview['on_track'] = $onTrack;
        $overview['at_risk'] = $atRisk;
        $overview['behind'] = $behind;
        $overview['not_started'] = $notStarted;
        $overview['reporting_rate'] = rand(85, 98);

        return $overview;
    }

    private function injectMockStrategic($goals)
    {
        foreach ($goals as &$goal) {
            // Requirement: "Use Real KRs", "Mock Progress"
            if (($goal['total_key_results'] ?? 0) > 0) {
                $goal['avg_progress'] = rand(40, 95) + (rand(0, 9) / 10);

                // Approximate counts based on progress
                $total = $goal['total_key_results'];
                if ($goal['avg_progress'] >= 80) {
                    $goal['on_track_count'] = floor($total * 0.8);
                } else {
                    $goal['on_track_count'] = floor($total * 0.5);
                }
            } else {
                $goal['avg_progress'] = 0;
                $goal['on_track_count'] = 0;
            }
        }
        return $goals;
    }

    private function injectMockDepartments($departments)
    {
        foreach ($departments as &$dept) {
            $total = (int)($dept['total_key_results'] ?? 0);

            // Only randomize if there are actual KRs
            if ($total > 0) {
                 $dept['avg_progress'] = rand(50, 98) + (rand(0, 9) / 10);

                 // Mock Role Distribution if Leader count is suspiciously 0
                 // This ensures the dashboard looks "active" in mock mode
                 if (($dept['leader_count'] ?? 0) == 0) {
                     $mockLeader = max(1, ceil($total * 0.3)); // Ensure at least 1 leader if total > 0
                     $dept['leader_count'] = $mockLeader;
                     $dept['coworking_count'] = max(0, $total - $mockLeader);
                 }
            } else {
                 $dept['avg_progress'] = 0;
            }
        }
        return $departments;
    }
}