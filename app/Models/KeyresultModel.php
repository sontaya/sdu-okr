<?php
namespace App\Models;

use CodeIgniter\Model;

class KeyresultModel extends Model
{
    protected $table = 'key_results'; // ใช้ table หลักไว้ก่อน
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // ไม่ต้องกำหนด allowedFields ถ้าใช้เฉพาะ get/query
    // protected $allowedFields = [...];

    public function getKeyResults($params = [])
    {
        $results = $this->executeQuery($params);
        return $results;
    }

    public function getKeyResultsDebug($params = [])
    {
        echo "<div style='background:yellow;padding:10px;'>";

        echo "❌ Cache MISS - Query database (Caching Disabled)<br>";
        $start = microtime(true);
        $results = $this->executeQuery($params);
        $end = microtime(true);

        $queryTime = ($end - $start) * 1000;
        echo "⏱️ Query เวลา: {$queryTime}ms<br>";

        echo "</div>";

        return $results;
    }

    /**
     * ✅ แยก query logic ออกมา
     */
    private function executeQuery($params)
    {
        $builder = $this->db->table('objective_groups og')
            ->select('
                og.id AS og_id, og.name AS og_name
                ,obj.id AS objective_id, obj.sequence_no AS objective_sequence, concat(obj.sequence_no,". ", obj.name) AS objective_name
                ,kt.id AS key_result_template_id, kt.sequence_no AS key_result_template_sequence
                , concat(obj.sequence_no,".",kt.sequence_no," ", kt.name) AS key_result_template_name
                ,kr.id AS key_result_id, kr.key_result_year, kr.sequence_no AS key_result_sequence
                , concat(kr.sequence_no,". ", kr.name) AS key_result_name
                , kr.target_value, kr.target_unit
                , kd.role as key_result_dep_role
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates kt', 'kt.objective_id = obj.id')
            ->join('key_results kr', 'kr.key_result_template_id = kt.id')
            ->join('key_result_departments kd', 'kr.id = kd.key_result_id');

        // เงื่อนไข dynamic
        if (!empty($params['conditions']['key_result_id'])) {
            $builder->where('kr.id', $params['conditions']['key_result_id']);
        }

        if (!empty($params['conditions']['department_id'])) {
            $builder->where('kd.department_id', $params['conditions']['department_id']);
        }

        if (!empty($params['conditions']['year'])) {
            $builder->where('kr.key_result_year', $params['conditions']['year']);
        }

        // ค้นหาด้วย keyword
        if (!empty($params['keyword'])) {
            $keyword = trim($params['keyword']);
            $builder->groupStart()
                ->like('kr.name', $keyword)
                ->orLike('kt.name', $keyword)
                ->orLike('obj.name', $keyword)
                ->groupEnd();
        }

        // นับจำนวนรายการอย่างเดียว
        if (!empty($params['count_only'])) {
            return $builder->countAllResults();
        }

        // Pagination
        if (!empty($params['limit'])) {
            $builder->limit($params['limit'], $params['offset'] ?? 0);
        }

        // เรียงลำดับ
        $builder->orderBy('og.id')
                ->orderBy('obj.sequence_no')
                ->orderBy('kt.sequence_no')
                ->orderBy('kr.sequence_no');

        return $builder->get()->getResultArray();
    }

    /**
     * ✅ ฟังก์ชัน clear cache เมื่อมีการเปลี่ยนแปลงข้อมูล
     */
    public function clearKeyResultsCache($keyResultId = null)
    {
        // Caching disabled - do nothing
    }

    public function getDepartmentsByKeyResult($key_result_id)
    {
        return $this->db->table('key_result_departments kd')
            ->select('
                kd.role,
                d.short_name,
                d.name AS full_name,
                (SELECT COUNT(*)
                 FROM key_result_entries kre
                 WHERE kre.key_result_id = kd.key_result_id
                 AND kre.department_id = kd.department_id
                 AND kre.entry_status = "published") as entry_count
            ')
            ->join('departments d', 'kd.department_id = d.id')
            ->where('kd.key_result_id', $key_result_id)
            ->get()
            ->getResultArray();
    }

    public function getWorkingDepartments($key_result_id)
    {
        return $this->db->table('key_result_departments kd')
            ->select('d.id, d.name, d.short_name, kd.role')
            ->join('departments d', 'kd.department_id = d.id')
            ->where('kd.key_result_id', $key_result_id)
            ->whereIn('kd.role', ['Leader', 'CoWorking'])
            ->orderBy('d.short_name', 'ASC') // เรียงตามชื่อย่อหน่วยงาน
            ->get()
            ->getResultArray();
    }

    public function getAllDepartments()
    {
        return $this->db->table('departments')
            ->select('id, name, short_name')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

/**
     * ✅ ดึงข้อมูล Key Results สำหรับ Strategic View
     * รวมข้อมูล Departments, Latest Progress, และ Published Entries Count
     * @param array $filters
     * @return array
     */
    public function getStrategicViewKeyResults($filters = [])
    {

        $db = \Config\Database::connect();

        // Subquery สำหรับดึง Progress ล่าสุดของแต่ละ Key Result
        $latestProgressSubquery = $db->table('key_result_progress')
            ->select('*, ROW_NUMBER() OVER(PARTITION BY key_result_id ORDER BY version DESC, created_date DESC) as rn')
            ->getCompiledSelect();

        // Main Query
        $builder = $db->table('objective_groups og')
            ->select('
                og.id AS og_id, og.name AS og_name,
                obj.id AS objective_id, CONCAT(obj.sequence_no, ". ", obj.name) AS objective_name,
                kt.id AS key_result_template_id, CONCAT(obj.sequence_no, ".", kt.sequence_no, " ", kt.name) AS key_result_template_name,
                kr.id AS key_result_id, kr.key_result_year, kr.name AS key_result_name, kr.target_value, kr.target_unit,

                lp.id as latest_progress_id, lp.progress_value, lp.progress_percentage, lp.status as progress_status,
                lp.created_date as progress_created_date, lp.updated_date as progress_updated_date,
                lp.submitted_date, lp.approved_date,
                rp.quarter_name, rp.year as reporting_year,
                u.full_name as progress_creator_name,

                (SELECT
                    CONCAT("[", GROUP_CONCAT(JSON_OBJECT(
                        "department_id", d.id,
                        "role", krd.role,
                        "short_name", d.short_name,
                        "full_name", d.name,
                        "entry_count", (SELECT COUNT(*) FROM key_result_entries kre WHERE kre.key_result_id = krd.key_result_id AND kre.department_id = krd.department_id AND kre.entry_status = "published")
                    ) ORDER BY FIELD(krd.role, "Leader", "CoWorking"), d.short_name), "]")
                FROM key_result_departments krd
                JOIN departments d ON krd.department_id = d.id
                WHERE krd.key_result_id = kr.id) AS departments_json,

                (SELECT COUNT(*) FROM key_result_entries WHERE key_result_id = kr.id AND entry_status = "published") as published_entries_count
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates kt', 'kt.objective_id = obj.id')
            ->join('key_results kr', 'kr.key_result_template_id = kt.id')
            ->join("({$latestProgressSubquery}) lp", 'lp.key_result_id = kr.id AND lp.rn = 1', 'left')
            ->join('reporting_periods rp', 'lp.reporting_period_id = rp.id', 'left')
            ->join('users u', 'lp.created_by = u.id', 'left');

        // Apply basic filters
        if (!empty($filters['year'])) {
            $builder->where('kr.key_result_year', $filters['year']);
        }
        if (!empty($filters['objective_group_id'])) {
            $builder->where('og.id', $filters['objective_group_id']);
        }
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $builder->groupStart()
                ->like('kr.name', $keyword)
                ->orLike('kt.name', $keyword)
                ->orLike('obj.name', $keyword)
                ->groupEnd();
        }

        // Apply filters ที่ต้องใช้ข้อมูล Join
        if (!empty($filters['department_id'])) {
            // ✅ Sanitize ID เป็น integer และสร้าง JSON ที่ถูกต้องสำหรับค่าตัวเลข
            $departmentId = (int) $filters['department_id'];
            $builder->having("JSON_CONTAINS(departments_json, '{\"department_id\": " . $departmentId . "}')");
        }
        if (!empty($filters['role_type'])) {
            // ✅ Escape ค่า string และสร้าง JSON ที่ถูกต้องสำหรับค่าข้อความ (ต้องมี double quote)
            $roleType = $db->escapeString($filters['role_type']);
            $builder->having("JSON_CONTAINS(departments_json, '{\"role\": \"" . $roleType . "\"}')");
        }

        if (!empty($filters['progress_status'])) {
            if ($filters['progress_status'] === 'no_report') {
                $builder->where('lp.id IS NULL');
            } else {
                $builder->where('lp.status', $filters['progress_status']);
            }
        }

        $builder->orderBy('og.id, obj.sequence_no, kt.sequence_no, kr.sequence_no');
        $results = $builder->get()->getResultArray();

        $processedResults = array_map(function ($item) {
            $item['departments'] = json_decode($item['departments_json'], true) ?? [];
            unset($item['departments_json']);

            // ตั้งค่า Default สำหรับรายการที่ยังไม่มี Progress
            $item['progress_status'] = $item['progress_status'] ?? 'no_report';

            // คำนวณวัน
            $item['days_since_update'] = $item['progress_updated_date'] ? (new \DateTime())->diff(new \DateTime($item['progress_updated_date']))->days : null;

            // สร้าง Reporting Period Text
            $item['reporting_period_text'] = ($item['quarter_name'] && $item['reporting_year']) ? "{$item['quarter_name']} {$item['reporting_year']}" : '-';

            // กำหนด Role หลัก
            $item['key_result_dep_role'] = 'ไม่ระบุ';
            if (!empty($item['departments'])) {
                foreach ($item['departments'] as $dept) {
                    if ($dept['role'] == 'Leader') {
                        $item['key_result_dep_role'] = 'Leader';
                        break;
                    }
                    $item['key_result_dep_role'] = $dept['role']; // เอาอันแรกที่เจอถ้าไม่มี Leader
                }
            }
            return $item;
        }, $results);


        return $processedResults;
    }

    /**
     * ✅ ดึงข้อมูลสำหรับสร้าง Filter ในหน้า Strategic Overview
     */
    public function getStrategicFilterOptions()
    {
        $db = \Config\Database::connect();

        $options = [
            'objective_groups' => $db->table('objective_groups')
                ->select('id, name')
                ->orderBy('id')
                ->get()
                ->getResultArray(),

            'departments' => $db->table('departments')
                ->select('id, short_name, name')
                ->orderBy('short_name')
                ->get()
                ->getResultArray(),

            'reporting_periods' => $db->table('reporting_periods')
                ->select('id, quarter_name, year, CONCAT(quarter_name, " ", year) as display_name')
                ->where('is_active', 1)
                ->orderBy('year', 'DESC')
                ->orderBy('quarter', 'DESC')
                ->get()
                ->getResultArray(),

            'years' => ['2571','2570','2569','2568'],

            'status_options' => [
                'no_report' => 'ยังไม่มีรายงาน',
                'draft' => 'ฉบับร่าง',
                'submitted' => 'รออนุมัติ',
                'approved' => 'อนุมัติแล้ว',
                'rejected' => 'ปฏิเสธ'
            ],

            'role_options' => [
                'Leader' => 'Leader',
                'CoWorking' => 'CoWorking'
            ]
        ];

        return $options;
    }


}
