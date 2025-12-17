<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
{
    protected $table = 'key_results';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * 1. ข้อมูลภาพรวมทั้งองค์กร (Organizational Overview)
     */
    public function getOrganizationOverview($year, $quarter = null)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "org_overview_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_v1";

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        $builder = $this->db->table('key_results kr')
            ->select('
                COUNT(*) as total_key_results,
                COUNT(DISTINCT krd.department_id) as total_departments,
                AVG(CASE WHEN krp.progress_percentage IS NOT NULL THEN krp.progress_percentage ELSE 0 END) as overall_progress
            ')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id', 'left')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        $overview = $builder->get()->getRowArray();

        // Status breakdown
        $statusBuilder = $this->db->table('key_results kr')
            ->select('
                SUM(CASE WHEN krp.progress_percentage >= 80 THEN 1 ELSE 0 END) as on_track,
                SUM(CASE WHEN krp.progress_percentage >= 60 AND krp.progress_percentage < 80 THEN 1 ELSE 0 END) as at_risk,
                SUM(CASE WHEN krp.progress_percentage >= 1 AND krp.progress_percentage < 60 THEN 1 ELSE 0 END) as behind,
                SUM(CASE WHEN krp.progress_percentage IS NULL OR krp.progress_percentage = 0 THEN 1 ELSE 0 END) as not_started
            ')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $statusBuilder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                         ->where('rp.quarter', $quarter)
                         ->where('rp.year', $year);
        }

        $statusData = $statusBuilder->get()->getRowArray();

        // Reporting rate
        $reportingBuilder = $this->db->table('key_results kr')
            ->select('
                COUNT(*) as total_expected,
                COUNT(krp.id) as total_reported
            ')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id', 'left')
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $reportingBuilder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                           ->where('rp.quarter', $quarter)
                           ->where('rp.year', $year);
        }

        $reportingData = $reportingBuilder->get()->getRowArray();

        $result = array_merge($overview, $statusData, [
            'reporting_rate' => $reportingData['total_expected'] > 0
                ? round(($reportingData['total_reported'] / $reportingData['total_expected']) * 100, 1)
                : 0
        ]);

        $cache->save($cacheKey, $result, 600); // Cache 10 minutes
        return $result;
    }

    /**
     * 2. ข้อมูลตาม Strategic Goals (Objective Groups)
     */
    public function getStrategicGoalsProgress($year, $quarter = null)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "strategic_goals_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_v1";

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        $builder = $this->db->table('objective_groups og')
            ->select('
                og.id,
                og.name,
                COUNT(kr.id) as total_key_results,
                AVG(CASE WHEN krp.progress_percentage IS NOT NULL THEN krp.progress_percentage ELSE 0 END) as avg_progress,
                SUM(CASE WHEN krp.progress_percentage >= 80 THEN 1 ELSE 0 END) as on_track_count,
                SUM(CASE WHEN krp.progress_percentage >= 60 AND krp.progress_percentage < 80 THEN 1 ELSE 0 END) as at_risk_count,
                SUM(CASE WHEN krp.progress_percentage < 60 AND krp.progress_percentage > 0 THEN 1 ELSE 0 END) as behind_count
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates krt', 'obj.id = krt.objective_id')
            ->join('key_results kr', 'krt.id = kr.key_result_template_id')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        $builder->groupBy('og.id, og.name')
               ->orderBy('og.id');

        $result = $builder->get()->getResultArray();

        $cache->save($cacheKey, $result, 900); // Cache 15 minutes
        return $result;
    }

    /**
     * 2.1 ข้อมูลทุก Strategic Goals (รวมที่ไม่มี KR) สำหรับ Mockup
     */
    public function getAllStrategicGoalsProgress($year, $quarter = null)
    {
        $builder = $this->db->table('objective_groups og')
            ->select('
                og.id,
                og.name,
                COUNT(CASE WHEN kr.key_result_year = ' . $this->db->escape($year) . ' THEN kr.id END) as total_key_results,
                AVG(CASE WHEN kr.key_result_year = ' . $this->db->escape($year) . ' AND krp.progress_percentage IS NOT NULL THEN krp.progress_percentage ELSE NULL END) as avg_progress,
                SUM(CASE WHEN kr.key_result_year = ' . $this->db->escape($year) . ' AND krp.progress_percentage >= 80 THEN 1 ELSE 0 END) as on_track_count,
                SUM(CASE WHEN kr.key_result_year = ' . $this->db->escape($year) . ' AND krp.progress_percentage >= 60 AND krp.progress_percentage < 80 THEN 1 ELSE 0 END) as at_risk_count,
                SUM(CASE WHEN kr.key_result_year = ' . $this->db->escape($year) . ' AND krp.progress_percentage < 60 AND krp.progress_percentage > 0 THEN 1 ELSE 0 END) as behind_count
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id', 'left')
            ->join('key_result_templates krt', 'obj.id = krt.objective_id', 'left')
            ->join('key_results kr', 'krt.id = kr.key_result_template_id', 'left')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left');

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('(rp.quarter IS NULL OR rp.quarter = ' . $this->db->escape($quarter) . ')');
        }

        $builder->groupBy('og.id, og.name')
               ->orderBy('og.id');

        $result = $builder->get()->getResultArray();

        // Post-processing for clean data
        foreach ($result as &$row) {
            $row['avg_progress'] = $row['avg_progress'] ?? 0;
        }

        return $result;
    }

    /**
     * 3. ข้อมูลตามหน่วยงาน (Department Performance)
     */
    public function getDepartmentProgress($year, $quarter = null, $options = [])
    {
        $page = $options['page'] ?? 1;
        $limit = $options['limit'] ?? 20;
        $sort = $options['sort'] ?? 'progress_desc';
        $offset = ($page - 1) * $limit;

        $builder = $this->db->table('departments d')
            ->select('
                d.id,
                d.short_name,
                d.name,
                COUNT(DISTINCT kr.id) as total_key_results,
                COUNT(DISTINCT CASE WHEN krd.role = "Leader" THEN kr.id END) as leader_count,
                COUNT(DISTINCT CASE WHEN krd.role = "CoWorking" THEN kr.id END) as coworking_count,
                AVG(CASE WHEN krp.progress_percentage IS NOT NULL THEN krp.progress_percentage ELSE 0 END) as avg_progress,
                MAX(krp.updated_date) as last_update
            ')
            ->join('key_result_departments krd', 'd.id = krd.department_id')
            ->join('key_results kr', 'krd.key_result_id = kr.id')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        $builder->groupBy('d.id, d.short_name, d.name');

        // Sorting
        switch ($sort) {
            case 'progress_desc':
                $builder->orderBy('avg_progress', 'DESC');
                break;
            case 'progress_asc':
                $builder->orderBy('avg_progress', 'ASC');
                break;
            case 'name_asc':
                $builder->orderBy('d.short_name', 'ASC');
                break;
            case 'last_update_desc':
                $builder->orderBy('last_update', 'DESC');
                break;
        }

        // Count total for pagination
        $totalBuilder = clone $builder;
        $total = $totalBuilder->countAllResults(false);

        // Get paginated results
        $departments = $builder->limit($limit, $offset)->get()->getResultArray();

        return [
            'departments' => $departments,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * 3.1 ข้อมูลทุกหน่วยงาน (รวมที่ไม่มี KR) สำหรับ Mockup
     */
    public function getAllDepartmentsProgress($year, $quarter = null, $options = [])
    {
        $page = $options['page'] ?? 1;
        $limit = $options['limit'] ?? 1000;

        $escapedYear = $this->db->escape($year);

        // Subqueries ensures accurate counts independent of main join
        $subQueryTotal = "(SELECT COUNT(DISTINCT kr.id)
                           FROM key_result_departments krd
                           JOIN key_results kr ON krd.key_result_id = kr.id
                           WHERE krd.department_id = d.id
                           AND kr.key_result_year = $escapedYear)";

        $subQueryLeader = "(SELECT COUNT(DISTINCT kr.id)
                            FROM key_result_departments krd
                            JOIN key_results kr ON krd.key_result_id = kr.id
                            WHERE krd.department_id = d.id
                            AND krd.role = 'Leader'
                            AND kr.key_result_year = $escapedYear)";

        $subQueryCoWork = "(SELECT COUNT(DISTINCT kr.id)
                            FROM key_result_departments krd
                            JOIN key_results kr ON krd.key_result_id = kr.id
                            WHERE krd.department_id = d.id
                            AND krd.role = 'CoWorking'
                            AND kr.key_result_year = $escapedYear)";

        $builder = $this->db->table('departments d')
            ->select("
                d.id,
                d.short_name,
                d.name,
                $subQueryTotal as total_key_results,
                $subQueryLeader as leader_count,
                $subQueryCoWork as coworking_count,
                AVG(CASE WHEN kr.key_result_year = $escapedYear AND krp.progress_percentage IS NOT NULL THEN krp.progress_percentage ELSE NULL END) as avg_progress
            ")
            // Keep joins for Average Progress calculation
            ->join('key_result_departments krd', 'd.id = krd.department_id', 'left')
            ->join('key_results kr', 'krd.key_result_id = kr.id', 'left')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left');

        if ($quarter) {
             $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                    ->where('(rp.quarter IS NULL OR rp.quarter = ' . $this->db->escape($quarter) . ')');
        }

        $builder->groupBy('d.id, d.short_name, d.name');

        // Get results
        $departments = $builder->get()->getResultArray();

        // Post-processing for clean data
        foreach ($departments as &$dept) {
            $dept['avg_progress'] = $dept['avg_progress'] ?? 0;
            // Cast to int as subqueries might return string
            $dept['total_key_results'] = (int)$dept['total_key_results'];
            $dept['leader_count'] = (int)$dept['leader_count'];
            $dept['coworking_count'] = (int)$dept['coworking_count'];
        }

        return [
            'departments' => $departments
        ];
    }

    /**
     * 4. เทรนด์ข้ามเวลา (Temporal Analysis)
     */
    public function getProgressTrends($year, $type = 'quarterly')
    {
        $cache = \Config\Services::cache();
        $cacheKey = "trends_{$year}_{$type}_v1";

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        if ($type === 'quarterly') {
            $builder = $this->db->table('reporting_periods rp')
                ->select('
                    rp.year,
                    rp.quarter,
                    rp.quarter_name,
                    og.name as objective_group_name,
                    AVG(krp.progress_percentage) as avg_progress
                ')
                ->join('key_result_progress krp', 'rp.id = krp.reporting_period_id AND krp.status = "approved"')
                ->join('key_results kr', 'krp.key_result_id = kr.id')
                ->join('key_result_templates krt', 'kr.key_result_template_id = krt.id')
                ->join('objectives obj', 'krt.objective_id = obj.id')
                ->join('objective_groups og', 'obj.objective_group_id = og.id')
                ->where('rp.year >=', $year - 1)
                ->where('rp.year <=', $year)
                ->groupBy('rp.year, rp.quarter, og.id')
                ->orderBy('rp.year, rp.quarter, og.id');

            $result = $builder->get()->getResultArray();
        }

        $cache->save($cacheKey, $result, 1800); // Cache 30 minutes
        return $result;
    }

    /**
     * 5. ข้อมูลความเสี่ยงและการแจ้งเตือน
     */
    public function getRiskAnalysis($year, $quarter = null)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "risk_analysis_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_v1";

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        // Critical Key Results (< 50% progress)
        $criticalBuilder = $this->db->table('key_results kr')
            ->select('
                kr.id,
                kr.name,
                krp.progress_percentage,
                d.short_name as department,
                og.name as objective_group
            ')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id AND krd.role = "Leader"')
            ->join('departments d', 'krd.department_id = d.id')
            ->join('key_result_templates krt', 'kr.key_result_template_id = krt.id')
            ->join('objectives obj', 'krt.objective_id = obj.id')
            ->join('objective_groups og', 'obj.objective_group_id = og.id')
            ->where('kr.key_result_year', $year)
            ->where('krp.progress_percentage <', 50)
            ->orderBy('krp.progress_percentage', 'ASC')
            ->limit(10);

        if ($quarter) {
            $criticalBuilder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id')
                          ->where('rp.quarter', $quarter)
                          ->where('rp.year', $year);
        }

        $criticalKeyResults = $criticalBuilder->get()->getResultArray();

        // Get reported key result IDs first (แก้ไขส่วนนี้)
        $reportedKeyResults = $this->db->table('key_result_progress krp')
            ->select('DISTINCT krp.key_result_id')
            ->join('reporting_periods rp2', 'krp.reporting_period_id = rp2.id')
            ->where('rp2.year', $year)
            ->where('krp.status !=', 'draft');

        if ($quarter) {
            $reportedKeyResults->where('rp2.quarter', $quarter);
        }

        $reportedIds = $reportedKeyResults->get()->getResultArray();
        $reportedKeyResultIds = array_column($reportedIds, 'key_result_id');

        // Overdue reports - Key Results that haven't been reported
        $overdueBuilder = $this->db->table('key_results kr')
            ->select('
                kr.id,
                kr.name,
                d.short_name as department,
                rp.end_date,
                DATEDIFF(NOW(), rp.end_date) as days_overdue
            ')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id AND krd.role = "Leader"')
            ->join('departments d', 'krd.department_id = d.id')
            ->join('reporting_periods rp', 'rp.year = kr.key_result_year')
            ->where('kr.key_result_year', $year)
            ->where('rp.end_date <', date('Y-m-d'));

        if (!empty($reportedKeyResultIds)) {
            $overdueBuilder->whereNotIn('kr.id', $reportedKeyResultIds);
        }

        if ($quarter) {
            $overdueBuilder->where('rp.quarter', $quarter);
        }

        $overdueReports = $overdueBuilder->orderBy('days_overdue', 'DESC')
                                        ->limit(10)
                                        ->get()
                                        ->getResultArray();

        $result = [
            'critical_key_results' => $criticalKeyResults,
            'overdue_reports' => $overdueReports,
            'summary' => [
                'critical_count' => count($criticalKeyResults),
                'overdue_count' => count($overdueReports)
            ]
        ];

        $cache->save($cacheKey, $result, 300); // Cache 5 minutes
        return $result;
    }

    /**
     * 6. การวิเคราะห์เชิงลึก
     */
    public function getDeepAnalytics($year, $quarter = null)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "deep_analytics_{$year}" . ($quarter ? "_Q{$quarter}" : '') . "_v1";

        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        // Reporting statistics
        $reportingStats = $this->db->table('key_results kr')
            ->select('
                COUNT(DISTINCT kr.id) as total_key_results,
                COUNT(DISTINCT krp.id) as total_reports,
                AVG(krp.version) as avg_updates_per_kr,
                AVG(DATEDIFF(krp.submitted_date, rp.start_date)) as avg_reporting_delay
            ')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id', 'left')
            ->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
            ->where('kr.key_result_year', $year)
            ->get()
            ->getRowArray();

        // Best performing objective groups
        $bestPerformers = $this->db->table('objective_groups og')
            ->select('
                og.name,
                AVG(krp.progress_percentage) as avg_progress,
                COUNT(kr.id) as total_key_results
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates krt', 'obj.id = krt.objective_id')
            ->join('key_results kr', 'krt.id = kr.key_result_template_id')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"')
            ->where('kr.key_result_year', $year)
            ->groupBy('og.id')
            ->orderBy('avg_progress', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        // Insights generation
        $insights = $this->generateInsights($year, $quarter);

        $result = [
            'reporting_stats' => $reportingStats,
            'best_performers' => $bestPerformers,
            'insights' => $insights
        ];

        $cache->save($cacheKey, $result, 1200); // Cache 20 minutes
        return $result;
    }

    /**
     * Helper Methods
     */

    private function generateInsights($year, $quarter = null)
    {
        $insights = [];

        // Strategic goal performance analysis
        $strategicData = $this->getStrategicGoalsProgress($year, $quarter);
        $bestGroup = array_reduce($strategicData, function($best, $current) {
            return (!$best || $current['avg_progress'] > $best['avg_progress']) ? $current : $best;
        });

        if ($bestGroup) {
            $insights[] = [
                'type' => 'success',
                'icon' => '🏆',
                'message' => "Strategic Goal \"{$bestGroup['name']}\" มีประสิทธิภาพสูงสุด ({$bestGroup['avg_progress']}%)"
            ];
        }

        // Department performance analysis
        $deptData = $this->getDepartmentProgress($year, $quarter, ['limit' => 5, 'sort' => 'progress_desc']);
        if (!empty($deptData['departments'])) {
            $topDept = $deptData['departments'][0];
            $insights[] = [
                'type' => 'info',
                'icon' => '📊',
                'message' => "หน่วยงาน {$topDept['short_name']} มีผลงานโดดเด่น ({$topDept['avg_progress']}%)"
            ];
        }

        // Risk analysis
        $riskData = $this->getRiskAnalysis($year, $quarter);
        if ($riskData['summary']['critical_count'] > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'message' => "มี {$riskData['summary']['critical_count']} Key Results ที่ต้องการการสนับสนุนเพิ่มเติม"
            ];
        }

        // Quarterly pattern analysis
        if ($quarter) {
            $insights[] = [
                'type' => 'info',
                'icon' => '⏰',
                'message' => "ช่วง Q{$quarter} มักมีการรายงานล่าช้า ควรปรับกระบวนการ"
            ];
        }

        return $insights;
    }

    /**
     * Department-specific methods
     */

    public function getDepartmentOverview($departmentId, $year, $quarter = null)
    {
        $builder = $this->db->table('key_results kr')
            ->select('
                COUNT(*) as total_key_results,
                COUNT(DISTINCT CASE WHEN krd.role = "Leader" THEN kr.id END) as leader_count,
                COUNT(DISTINCT CASE WHEN krd.role = "CoWorking" THEN kr.id END) as coworking_count,
                AVG(CASE WHEN krp.progress_percentage IS NOT NULL THEN krp.progress_percentage ELSE 0 END) as avg_progress
            ')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('krd.department_id', $departmentId)
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        return $builder->get()->getRowArray();
    }

    public function getDepartmentKeyResults($departmentId, $year, $quarter = null)
    {
        $builder = $this->db->table('key_results kr')
            ->select('
                kr.id,
                kr.name,
                krd.role,
                krp.progress_percentage,
                krp.updated_date,
                og.name as objective_group_name
            ')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->join('key_result_templates krt', 'kr.key_result_template_id = krt.id')
            ->join('objectives obj', 'krt.objective_id = obj.id')
            ->join('objective_groups og', 'obj.objective_group_id = og.id')
            ->where('krd.department_id', $departmentId)
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        return $builder->orderBy('og.id')
                      ->orderBy('kr.sequence_no')
                      ->get()
                      ->getResultArray();
    }

    public function getDepartmentProgressHistory($departmentId, $year)
    {
        return $this->db->table('key_result_progress krp')
            ->select('
                rp.quarter_name,
                AVG(krp.progress_percentage) as avg_progress,
                COUNT(krp.id) as report_count
            ')
            ->join('reporting_periods rp', 'krp.reporting_period_id = rp.id')
            ->join('key_results kr', 'krp.key_result_id = kr.id')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
            ->where('krd.department_id', $departmentId)
            ->where('rp.year', $year)
            ->where('krp.status', 'approved')
            ->groupBy('rp.quarter')
            ->orderBy('rp.quarter')
            ->get()
            ->getResultArray();
    }

    public function getDepartmentActionItems($departmentId, $year, $quarter = null)
    {
        // Get key results that need attention
        $builder = $this->db->table('key_results kr')
            ->select('
                kr.id,
                kr.name,
                krp.progress_percentage,
                "low_progress" as action_type,
                "ความคืบหน้าต่ำกว่าเป้าหมาย" as action_reason
            ')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id AND krd.role = "Leader"')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('krd.department_id', $departmentId)
            ->where('kr.key_result_year', $year)
            ->where('krp.progress_percentage <', 60);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        return $builder->orderBy('krp.progress_percentage', 'ASC')
                      ->limit(10)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Real-time and Activity methods
     */

    public function getRealTimeProgress($year, $quarter = null, $objectiveGroupId = null)
    {
        $builder = $this->db->table('key_results kr')
            ->select('
                COUNT(*) as total,
                AVG(krp.progress_percentage) as avg_progress,
                MAX(krp.updated_date) as last_update
            ')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('kr.key_result_year', $year);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        if ($objectiveGroupId) {
            $builder->join('key_result_templates krt', 'kr.key_result_template_id = krt.id')
                   ->join('objectives obj', 'krt.objective_id = obj.id')
                   ->where('obj.objective_group_id', $objectiveGroupId);
        }

        return $builder->get()->getRowArray();
    }

    public function getRecentActivities($limit = 20)
    {
        return $this->db->table('key_result_progress krp')
            ->select('
                kr.name as key_result_name,
                d.short_name as department,
                krp.progress_percentage,
                krp.updated_date,
                "progress_update" as activity_type
            ')
            ->join('key_results kr', 'krp.key_result_id = kr.id')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id AND krd.role = "Leader"')
            ->join('departments d', 'krd.department_id = d.id')
            ->where('krp.updated_date >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->orderBy('krp.updated_date', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getUpcomingDeadlines($year, $quarter = null)
    {
        $builder = $this->db->table('reporting_periods rp')
            ->select('
                rp.quarter_name,
                rp.end_date,
                DATEDIFF(rp.end_date, NOW()) as days_remaining,
                COUNT(kr.id) as pending_reports
            ')
            ->join('key_results kr', 'rp.year = kr.key_result_year', 'left')
            ->where('rp.year', $year)
            ->where('rp.end_date >=', date('Y-m-d'))
            ->groupBy('rp.id')
            ->orderBy('rp.end_date', 'ASC');

        if ($quarter) {
            $builder->where('rp.quarter', $quarter);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Settings and Export methods
     */

    public function saveDashboardSettings($userId, $settings)
    {
        // Implementation สำหรับบันทึกการตั้งค่า Dashboard ของผู้ใช้
        // อาจสร้างตาราง user_dashboard_settings

        $data = [
            'user_id' => $userId,
            'settings' => json_encode($settings),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('user_dashboard_settings')
                       ->replace($data); // INSERT ... ON DUPLICATE KEY UPDATE
    }

    public function getDashboardSettings($userId)
    {
        $result = $this->db->table('user_dashboard_settings')
                          ->where('user_id', $userId)
                          ->get()
                          ->getRowArray();

        return $result ? json_decode($result['settings'], true) : null;
    }

    public function getExportData($year, $quarter = null, $scope = 'overview')
    {
        $data = [];

        switch ($scope) {
            case 'overview':
                $data['overview'] = $this->getOrganizationOverview($year, $quarter);
                break;
            case 'strategic':
                $data['strategic_goals'] = $this->getStrategicGoalsProgress($year, $quarter);
                break;
            case 'department':
                $data['departments'] = $this->getDepartmentProgress($year, $quarter, ['limit' => 100]);
                break;
            case 'all':
                $data['overview'] = $this->getOrganizationOverview($year, $quarter);
                $data['strategic_goals'] = $this->getStrategicGoalsProgress($year, $quarter);
                $data['departments'] = $this->getDepartmentProgress($year, $quarter, ['limit' => 100]);
                $data['risks'] = $this->getRiskAnalysis($year, $quarter);
                break;
        }

        return $data;
    }

    public function getKeyResultDetail($keyResultId, $year, $quarter = null)
    {
        $builder = $this->db->table('key_results kr')
            ->select('
                kr.*,
                krt.name as template_name,
                obj.name as objective_name,
                og.name as objective_group_name,
                krp.progress_percentage,
                krp.progress_description,
                krp.updated_date as last_progress_update
            ')
            ->join('key_result_templates krt', 'kr.key_result_template_id = krt.id')
            ->join('objectives obj', 'krt.objective_id = obj.id')
            ->join('objective_groups og', 'obj.objective_group_id = og.id')
            ->join('key_result_progress krp', 'kr.id = krp.key_result_id AND krp.status = "approved"', 'left')
            ->where('kr.id', $keyResultId);

        if ($quarter) {
            $builder->join('reporting_periods rp', 'krp.reporting_period_id = rp.id', 'left')
                   ->where('rp.quarter', $quarter)
                   ->where('rp.year', $year);
        }

        $keyResult = $builder->get()->getRowArray();

        if ($keyResult) {
            // Get departments
            $keyResult['departments'] = $this->getDepartmentsByKeyResult($keyResultId);

            // Get progress history
            $keyResult['progress_history'] = $this->getKeyResultProgressHistory($keyResultId, $year);
        }

        return $keyResult;
    }

    private function getDepartmentsByKeyResult($keyResultId)
    {
        return $this->db->table('key_result_departments krd')
            ->select('krd.role, d.short_name, d.name')
            ->join('departments d', 'krd.department_id = d.id')
            ->where('krd.key_result_id', $keyResultId)
            ->get()
            ->getResultArray();
    }

    private function getKeyResultProgressHistory($keyResultId, $year)
    {
        return $this->db->table('key_result_progress krp')
            ->select('
                rp.quarter_name,
                krp.progress_percentage,
                krp.updated_date
            ')
            ->join('reporting_periods rp', 'krp.reporting_period_id = rp.id')
            ->where('krp.key_result_id', $keyResultId)
            ->where('rp.year', $year)
            ->where('krp.status', 'approved')
            ->orderBy('rp.quarter')
            ->get()
            ->getResultArray();
    }
}