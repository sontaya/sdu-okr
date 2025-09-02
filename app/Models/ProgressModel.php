<?php
// app/Models/ProgressModel.php
namespace App\Models;


use CodeIgniter\Model;
use App\Models\ProgressHistoryModel;

class ProgressModel extends Model
{
    protected $table = 'key_result_progress';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'key_result_id', 'reporting_period_id', 'progress_value', 'progress_percentage',
        'status', 'progress_description', 'challenges', 'solutions', 'next_actions',
        'submitted_by', 'submitted_date', 'approved_by', 'approved_date', 'version',
        'created_by', 'created_date', 'updated_by', 'updated_date'
    ];
    protected $useTimestamps = false;

    public function getKeyResults($params = [])
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


        if (!empty($params['conditions']['department_id'])) {
            $builder->where('kd.department_id', $params['conditions']['department_id']);
        }

        if (!empty($params['conditions']['year'])) {
            $builder->where('kr.key_result_year', $params['conditions']['year']);
        }

        return $builder->get()->getResultArray();

    }



    public function getLatestProgress($keyResultId, $reportingPeriodId = null)
    {
        $builder = $this->db->table($this->table . ' p')
            ->select('p.*, rp.quarter_name, rp.year')
            ->join('reporting_periods rp', 'p.reporting_period_id = rp.id')
            ->where('p.key_result_id', $keyResultId);

        if ($reportingPeriodId) {
            $builder->where('p.reporting_period_id', $reportingPeriodId);
        }

        return $builder->orderBy('p.version', 'DESC')
                      ->orderBy('p.created_date', 'DESC')
                      ->get()
                      ->getRowArray();
    }

    public function getProgressHistory($keyResultId)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, rp.quarter_name, rp.year, rp.start_date, rp.end_date')
            ->join('reporting_periods rp', 'p.reporting_period_id = rp.id')
            ->where('p.key_result_id', $keyResultId)
            ->orderBy('rp.year', 'DESC')
            ->orderBy('rp.quarter', 'DESC')
            ->orderBy('p.version', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getProgressById($progressId)
    {
        return $this->db->table($this->table . ' p')
            ->select('p.*, rp.quarter_name, rp.year, rp.start_date, rp.end_date, kr.name as key_result_name, kr.target_value, kr.target_unit')
            ->join('reporting_periods rp', 'p.reporting_period_id = rp.id')
            ->join('key_results kr', 'p.key_result_id = kr.id')
            ->where('p.id', $progressId)
            ->get()
            ->getRowArray();
    }

    public function getNextVersion($keyResultId, $reportingPeriodId)
    {
        $lastVersion = $this->db->table($this->table)
            ->selectMax('version')
            ->where('key_result_id', $keyResultId)
            ->where('reporting_period_id', $reportingPeriodId)
            ->get()
            ->getRow();

        return ($lastVersion->version ?? 0) + 1;
    }

    public function insertHistory($progressId, $action, $notes = null, $createdBy = null, $oldValue = null, $newValue = null)
    {
        $historyModel = new ProgressHistoryModel();
        return $historyModel->insert([
            'progress_id' => $progressId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'notes' => $notes,
            'created_by' => $createdBy,
            'created_date' => date('Y-m-d H:i:s')
        ]);
    }

    public function getKeyResultsForProgress($departmentId = null)
    {
        $builder = $this->db->table('objective_groups og')
            ->select('
                og.id AS og_id,
                og.name AS og_name,
                obj.id AS objective_id,
                obj.sequence_no AS objective_sequence,
                concat(obj.sequence_no,". ", obj.name) AS objective_name,
                kt.id AS key_result_template_id,
                kt.sequence_no AS key_result_template_sequence,
                concat(obj.sequence_no,".",kt.sequence_no," ", kt.name) AS key_result_template_name,
                kr.id AS key_result_id,
                kr.key_result_year,
                kr.sequence_no AS key_result_sequence,
                concat(kr.sequence_no,". ", kr.name) AS key_result_name,
                kr.target_value,
                kr.target_unit,
                COALESCE(kd.role, "ไม่ระบุ") as key_result_dep_role,
                COALESCE(d.short_name, "ไม่ระบุหน่วยงาน") as leader_department
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates kt', 'kt.objective_id = obj.id')
            ->join('key_results kr', 'kr.key_result_template_id = kt.id')
            ->join('key_result_departments kd', 'kr.id = kd.key_result_id', 'left')
            ->join('departments d', 'kd.department_id = d.id', 'left')
            ->where('kr.key_result_year', '2568');

        // Filter by department if specified
        if ($departmentId) {
            $builder->where('kd.department_id', $departmentId);
        }

        // เรียงลำดับ
        $builder->orderBy('og.id')
                ->orderBy('obj.sequence_no')
                ->orderBy('kt.sequence_no')
                ->orderBy('kr.sequence_no');

        return $builder->get()->getResultArray();
    }

    // ✅ เพิ่ม method สำหรับดึงข้อมูล Key Result เดี่ยว
    public function getKeyResultById($keyResultId)
    {
        return $this->db->table('objective_groups og')
            ->select('
                og.id AS og_id,
                og.name AS og_name,
                obj.id AS objective_id,
                obj.sequence_no AS objective_sequence,
                concat(obj.sequence_no,". ", obj.name) AS objective_name,
                kt.id AS key_result_template_id,
                kt.sequence_no AS key_result_template_sequence,
                concat(obj.sequence_no,".",kt.sequence_no," ", kt.name) AS key_result_template_name,
                kr.id AS key_result_id,
                kr.key_result_year,
                kr.sequence_no AS key_result_sequence,
                concat(kr.sequence_no,". ", kr.name) AS key_result_name,
                kr.target_value,
                kr.target_unit,
                COALESCE(kd.role, "ไม่ระบุ") as key_result_dep_role,
                COALESCE(d.short_name, "ไม่ระบุหน่วยงาน") as leader_department,
                COUNT(kd2.id) as coworking_count
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates kt', 'kt.objective_id = obj.id')
            ->join('key_results kr', 'kr.key_result_template_id = kt.id')
            ->join('key_result_departments kd', 'kr.id = kd.key_result_id AND kd.role = "Leader"', 'left')
            ->join('departments d', 'kd.department_id = d.id', 'left')
            ->join('key_result_departments kd2', 'kr.id = kd2.key_result_id AND kd2.role = "CoWorking"', 'left')
            ->where('kr.id', $keyResultId)
            ->groupBy('kr.id')
            ->get()
            ->getRowArray();
    }
}


