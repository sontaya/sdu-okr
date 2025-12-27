<?php

namespace App\Controllers\Admin;

use App\Controllers\TemplateController;
use CodeIgniter\API\ResponseTrait;
use App\Libraries\ActivityLogger;

class KeyResultMasterController extends TemplateController
{
    use ResponseTrait;

    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // Check Admin permission
        if (!isAdminFast()) {
            return redirect()->to(base_url('login'));
        }

        $this->data['title'] = 'จัดการข้อมูล Key Results (Master)';
        $this->data['cssSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css',
            'assets/css/okr-custom.css'
        ];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js'
        ];

        // Filters
        $filters = [
            'year' => $this->request->getGet('year'),
            'objective_group_id' => $this->request->getGet('objective_group_id'),
            'search' => $this->request->getGet('search'),
        ];

        // Filter Options
        $this->data['filter_options'] = [
            'years' => ['2571','2570','2569','2568'],
            'objective_groups' => $this->db->table('objective_groups')->select('id, name')->orderBy('id')->get()->getResultArray()
        ];
        $this->data['current_filters'] = $filters;


        // Get all Key Results
        $builder = $this->db->table('key_results kr')
            ->select('kr.*, kt.name as template_name, obj.name as objective_name, og.name as objective_group_name, og.id as og_id, og.name as og_name')
            ->join('key_result_templates kt', 'kr.key_result_template_id = kt.id', 'left')
            ->join('objectives obj', 'kt.objective_id = obj.id', 'left')
            ->join('objective_groups og', 'obj.objective_group_id = og.id', 'left');

        // Apply Filters
        if ($filters['year']) {
            $builder->where('kr.key_result_year', $filters['year']);
        }
        if ($filters['objective_group_id']) {
            $builder->where('og.id', $filters['objective_group_id']);
        }

        // Search logic might be handled by DataTables client-side usually,
        // but if server-side filtering is desired for text search:
        // if ($filters['search']) { ... }

        $builder->orderBy('kr.key_result_year', 'DESC')
            ->orderBy('kr.sequence_no', 'ASC');

        $key_results = $builder->get()->getResultArray();

        // Get Departments for these Key Results
        if (!empty($key_results)) {
            $krIds = array_column($key_results, 'id');
            $departments = $this->db->table('key_result_departments krd')
                ->select('krd.key_result_id, krd.role, d.short_name, d.name as department_name')
                ->join('departments d', 'krd.department_id = d.id')
                ->whereIn('krd.key_result_id', $krIds)
                ->orderBy('krd.id', 'ASC') // Keep order of insertion or define another order
                ->get()->getResultArray();

            // Group departments by Key Result ID
            $departmentsMap = [];
            foreach ($departments as $dept) {
                $departmentsMap[$dept['key_result_id']][] = $dept;
            }

            // Merge back to Key Results
            foreach ($key_results as &$kr) {
                $kr['departments'] = $departmentsMap[$kr['id']] ?? [];
            }
        }

        $this->data['key_results'] = $key_results;

        $this->contentTemplate = 'admin/keyresult/index';
        return $this->render();
    }

    public function form($id = null)
    {
        if (!isAdminFast()) {
            return redirect()->to(base_url('login'));
        }

        $this->data['title'] = $id ? 'แก้ไข Key Result' : 'สร้าง Key Result ใหม่';

        // Get Dropdown Data
        $this->data['years'] = ['2571','2570','2569','2568'];

        // Fetch All Departments
        $this->data['departments'] = $this->db->table('departments')
            ->select('id, name, short_name')
            ->orderBy('id')
            ->get()->getResultArray();

        // Fetch Objective Groups (Top level)
        $this->data['objective_groups'] = $this->db->table('objective_groups')
            ->select('id, name')
            ->orderBy('id')
            ->get()->getResultArray();

        $item = null;
        if ($id) {
            // Join to get linked template -> objective -> group info
            $item = $this->db->table('key_results kr')
                ->select('kr.*, kt.objective_id, obj.objective_group_id')
                ->join('key_result_templates kt', 'kr.key_result_template_id = kt.id', 'left')
                ->join('objectives obj', 'kt.objective_id = obj.id', 'left')
                ->where('kr.id', $id)
                ->get()->getRowArray();

            if (!$item) {
                return redirect()->to(base_url('admin/keyresult'))->with('error', 'ไม่พบข้อมูล');
            }

            // Get existing departments
            $this->data['existing_departments'] = $this->db->table('key_result_departments')
                ->where('key_result_id', $id)
                ->get()->getResultArray();
        }

        $this->data['item'] = $item;
        if (!isset($this->data['existing_departments'])) {
             $this->data['existing_departments'] = [];
        }

        $this->data['jsSrc'] = ['assets/js/admin/keyresult/form.js'];
        $this->contentTemplate = 'admin/keyresult/form';
        return $this->render();
    }

    public function getRelatedData()
    {
        if (!isAdminFast()) {
            return $this->failForbidden();
        }

        $type = $this->request->getPost('type');
        $parentId = $this->request->getPost('parent_id');
        $year = $this->request->getPost('year');

        if (!$type) {
            return $this->fail('Invalid Request');
        }

        $data = [];

        switch ($type) {
            case 'objectives':
                // Get Objectives by Group ID
                if ($parentId) {
                    $data = $this->db->table('objectives')
                        ->select('id, sequence_no, name')
                        ->where('objective_group_id', $parentId)
                        ->orderBy('sequence_no')
                        ->get()->getResultArray();
                }
                break;

            case 'templates':
                // Get Templates by Objective ID
                if ($parentId) {
                    $data = $this->db->table('key_result_templates')
                        ->select('id, sequence_no, name')
                        ->where('objective_id', $parentId)
                        ->orderBy('sequence_no')
                        ->get()->getResultArray();
                }
                break;

            case 'key_results':
                 // Get existing Key Results by filters
                $builder = $this->db->table('key_results kr')
                    ->select('kr.id, kr.sequence_no, kr.name, kr.target_value, kr.target_unit, kt.name as template_name')
                    ->join('key_result_templates kt', 'kr.key_result_template_id = kt.id')
                    ->join('objectives obj', 'kt.objective_id = obj.id')
                    ->orderBy('kr.sequence_no');

                if ($year) {
                    $builder->where('kr.key_result_year', $year);
                }

                // If objective_id provided, filter by it
                if ($this->request->getPost('objective_id')) {
                    $builder->where('obj.id', $this->request->getPost('objective_id'));
                } // If only group_id provided
                elseif ($this->request->getPost('objective_group_id')) {
                    $builder->where('obj.objective_group_id', $this->request->getPost('objective_group_id'));
                }

                $data = $builder->get()->getResultArray();
                break;
        }

        return $this->respond(['success' => true, 'data' => $data]);
    }

    public function save()
    {
        if (!isAdminFast()) {
            return $this->failForbidden();
        }

        $id = $this->request->getPost('id');

        $data = [
            'key_result_year' => $this->request->getPost('key_result_year'),
            'sequence_no' => $this->request->getPost('sequence_no'),
            'name' => $this->request->getPost('name'),
            'target_value' => $this->request->getPost('target_value'),
            'target_unit' => $this->request->getPost('target_unit'),
            'key_result_template_id' => $this->request->getPost('key_result_template_id'),
        ];

        $c_department_ids = $this->request->getPost('department_ids');
        $c_roles = $this->request->getPost('department_roles');

        // Debug Logging
        log_message('error', 'KeyResult Save Data: ' . print_r($data, true));

        // Basic Validation
        if (empty($data['key_result_year'])) {
            return $this->fail('กรุณาระบุปีงบประมาณ', 400);
        }
        if (empty($data['name'])) {
            return $this->fail('กรุณาระบุชื่อ Key Result', 400);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('key_results');

        $db->transStart();

        try {
            if ($id) {
                // Update
                $data['updated_by'] = session('user_id');
                $builder->where('id', $id)->update($data);
                $message = 'แก้ไขข้อมูลสำเร็จ';
                $action = 'update_key_result';
            } else {
                // Insert
                $data['created_by'] = session('user_id');
                $builder->insert($data);
                $id = $db->insertID();
                $message = 'เพิ่มข้อมูลสำเร็จ';
                $action = 'create_key_result';
            }

            // Handle Departments
            // First delete existing
            $db->table('key_result_departments')->where('key_result_id', $id)->delete();

            // Insert new ones
            if (!empty($c_department_ids) && is_array($c_department_ids)) {
                $batchData = [];
                foreach ($c_department_ids as $index => $deptId) {
                    if (!empty($deptId)) {
                        $role = $c_roles[$index] ?? 'CoWorking';
                        if (!in_array($role, ['Leader', 'CoWorking'])) {
                            $role = 'CoWorking';
                        }

                        $batchData[] = [
                            'key_result_id' => $id,
                            'department_id' => $deptId,
                            'role' => $role
                        ];
                    }
                }

                if (!empty($batchData)) {
                    $db->table('key_result_departments')->insertBatch($batchData);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                 return $this->fail('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }

            // Log the action
            $operator = session('full_name') . ' (' . session('uid') . ')';
            $verb = ($action == 'create_key_result') ? 'Created' : 'Updated';
            $description = "$verb Key Result '{$data['name']}' (ID: $id). Year: {$data['key_result_year']} by $operator";

            (new ActivityLogger())->log($action, [
                'key_result_id' => $id,
                'data' => $data,
                'departments' => $batchData ?? []
            ], null, $description, 'key_result_master');

            return $this->respond(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->fail('เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (!isAdminFast()) {
            return $this->failForbidden();
        }

        try {
            // Get name before delete for logging
            $kr = $this->db->table('key_results')->select('name, key_result_year')->where('id', $id)->get()->getRowArray();
            $name = $kr['name'] ?? 'Unknown';
            $year = $kr['key_result_year'] ?? '';

            $this->db->table('key_results')->where('id', $id)->delete();

            // Log Delete
            $operator = session('full_name') . ' (' . session('uid') . ')';
            $description = "Deleted Key Result '$name' (ID: $id, Year: $year) by $operator";
            (new ActivityLogger())->log('delete_key_result', [
                'key_result_id' => $id,
                'name' => $name,
                'year' => $year
            ], null, $description, 'key_result_master');

            return $this->respondDeleted(['success' => true, 'message' => 'ลบข้อมูลสำเร็จ']);
        } catch (\Exception $e) {
            return $this->fail('ไม่สามารถลบข้อมูลได้ อาจมีการใช้งานอยู่');
        }
    }
}
