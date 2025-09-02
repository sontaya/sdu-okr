<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\KeyresultModel;
use App\Models\KeyResultEntryModel;
use App\Models\KeyResultFileModel;
use App\Models\KeyResultTagModel;
use App\Models\ProgressModel;
use CodeIgniter\Controller;


class KeyresultController extends TemplateController
{
    protected $allowed = [];


    public function index()
    {
         return redirect()->to(base_url('keyresult'));
    }


    public function list()
    {
        $model = new KeyresultModel();
        $progressModel = new ProgressModel();

        $conditions = [
            'department_id' => session('department'),
            'year' => '2568'
        ];

        // ดึงข้อมูล Key Results ทั้งหมดของหน่วยงาน
        $keyresults = $model->getKeyResults([
            'conditions' => $conditions
        ]);

        $db = \Config\Database::connect();

        // ข้อมูลความคืบหน้าและสิทธิ์สำหรับแต่ละ Key Result
        foreach ($keyresults as &$keyresult) {
            $keyResultId = $keyresult['key_result_id'];

            // นับจำนวน entries ที่ published
            $entriesCount = $db->table('key_result_entries')
                ->where('key_result_id', $keyResultId)
                ->where('entry_status', 'published')
                ->countAllResults();
            $keyresult['published_entries_count'] = $entriesCount;

            // ดึงความคืบหน้าล่าสุด
            $latestProgress = $progressModel->getLatestProgress($keyResultId);
            $keyresult['latest_progress'] = $latestProgress;

            // ปรับการตรวจสอบสิทธิ์ตาม Key Result Role
            $keyresult['can_view'] = canViewKeyResult($keyResultId);
            $keyresult['can_report'] = canReportProgress($keyResultId); // Leader + User Permission
            $keyresult['can_manage_entries'] = canManageEntries($keyResultId); // Leader/CoWorking + User Permission

            // ดึงบทบาทใน Key Result
            $keyresult['key_result_role'] = getKeyResultRole($keyResultId, session('department'));

            // สิทธิ์เฉพาะการรายงาน (เฉพาะ Leader)
            if ($keyresult['can_report'] && $latestProgress) {
                $keyresult['can_edit_report'] = (
                    $latestProgress['status'] === 'draft' &&
                    ($latestProgress['created_by'] == session('user_id') || hasRole('Admin'))
                );
                $keyresult['can_submit_report'] = (
                    $latestProgress['status'] === 'draft' &&
                    $latestProgress['created_by'] == session('user_id')
                );
            } else {
                $keyresult['can_edit_report'] = false;
                $keyresult['can_submit_report'] = false;
            }

            // สิทธิ์การอนุมัติ (สำหรับ Approver/Admin + Leader)
            $keyresult['can_approve'] = false;
            if ($latestProgress && $latestProgress['status'] === 'submitted') {
                // ✅ ต้องเป็น Leader + มีสิทธิ์ Approver/Admin + ไม่ใช่คนสร้างรายงาน
                $keyresult['can_approve'] = (
                    $keyresult['key_result_role'] === 'Leader' &&
                    (hasRole('Approver') || hasRole('Admin')) &&
                    $latestProgress['created_by'] != session('user_id')
                );
            }

            // ข้อมูลเพิ่มเติมสำหรับการแสดงผล (เหมือนเดิม)
            $keyresult['progress_percentage'] = $latestProgress['progress_percentage'] ?? 0;
            $keyresult['progress_status'] = $latestProgress['status'] ?? 'no_report';
            $keyresult['last_update'] = $latestProgress['updated_date'] ?? $latestProgress['created_date'] ?? null;

            // ข้อมูลรอบการรายงาน (เหมือนเดิม)
            if ($latestProgress) {
                $keyresult['reporting_info'] = [
                    'quarter' => $latestProgress['quarter_name'] ?? '',
                    'year' => $latestProgress['year'] ?? '',
                    'period_text' => ($latestProgress['quarter_name'] ?? '') . ' ' . ($latestProgress['year'] ?? '')
                ];
            } else {
                $keyresult['reporting_info'] = [
                    'quarter' => '',
                    'year' => '',
                    'period_text' => '-'
                ];
            }
        }

        // ข้อมูลสำหรับ View
        $this->data['keyresults'] = $keyresults;
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->data['pending_approvals_count'] = getPendingApprovalsCount();

        // นับจำนวนสถิติต่างๆ
        $stats = [
            'total_keyresults' => count($keyresults),
            'can_report_count' => count(array_filter($keyresults, function($kr) { return $kr['can_report']; })),
            'pending_reports' => count(array_filter($keyresults, function($kr) {
                return $kr['can_report'] && $kr['progress_status'] === 'draft';
            })),
            'submitted_reports' => count(array_filter($keyresults, function($kr) {
                return $kr['progress_status'] === 'submitted';
            })),
            'approved_reports' => count(array_filter($keyresults, function($kr) {
                return $kr['progress_status'] === 'approved';
            }))
        ];
        $this->data['stats'] = $stats;

        $this->data['title'] = 'My Key Results';
        $this->data['cssSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'
        ];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/keyresult/unified-list.js'
        ];

        $this->contentTemplate = 'keyresult/unified-list';
        return $this->render();
    }

    public function view($id)
    {
        $startTime = microtime(true);

        $model = new KeyresultModel();
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        $conditions = [
            'key_result_id' => $id
        ];

        $results = $model->getKeyResults([
            'conditions' => $conditions
        ]);

       $keyresult = $results[0] ?? null;

       // หน่วยงานที่เกี่ยวข้อง
       $departments = $model->getDepartmentsByKeyResult($id);

       // ✅ ดึงข้อมูล entries พร้อมไฟล์และ tags
       $entries = $this->getEntriesWithDetails($id);

        $this->data['title'] = 'รายละเอียด Key Result';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/keyresult/view.js'
        ];
        $this->data['keyresult'] = $keyresult;
        $this->data['departments'] = $departments;
        $this->data['entries'] = $entries; // ✅ ส่งข้อมูล entries
        $this->contentTemplate = 'keyresult/view';

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        log_message('info', "View page loaded in: {$executionTime}ms");

        return $this->render();
    }

    // ✅ เพิ่มฟังก์ชันช่วยในการดึงข้อมูล entries พร้อมรายละเอียด
    private function getEntriesWithDetails($keyResultId)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('key_result_entries kre')
            ->select('
                kre.id,
                kre.entry_name,
                kre.entry_description,
                kre.entry_status,
                kre.created_date,
                kre.created_by,
                COUNT(krf.id) as file_count
            ')
            ->join('key_result_files krf', 'kre.id = krf.entry_id', 'left')
            ->where('kre.key_result_id', $keyResultId)
            ->groupBy('kre.id')
            ->orderBy('kre.created_date', 'DESC');

        $entries = $builder->get()->getResultArray();

        // ✅ ดึง tags สำหรับแต่ละ entry
        foreach ($entries as &$entry) {
            $tagBuilder = $db->table('key_result_tags')
                ->select('tag_name')
                ->where('entry_id', $entry['id']);
            $tags = $tagBuilder->get()->getResultArray();
            $entry['tags'] = array_column($tags, 'tag_name');
        }

        return $entries;
    }

    public function form($id = null)
    {
        $this->data['title'] = 'บันทึกรายการข้อมูล';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/themes/metronic38/assets/plugins/custom/formrepeater/formrepeater.bundle.js',
            'assets/js/keyresult/form.js'
        ];

        // ดึงข้อมูล keyresult
        if ($id) {
            $model = new KeyresultModel();
            $results = $model->getKeyResults(['conditions' => ['key_result_id' => $id]]);
            $this->data['keyresult'] = $results[0] ?? null;
        }

        $this->data['key_result_id'] = $id;
        $this->contentTemplate = 'keyresult/form';
        return $this->render();
    }

    public function saveEntry()
    {
        helper(['form']);

        $request = service('request');
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        $data = [
            'key_result_id' => $request->getPost('key_result_id'),
            'entry_name' => $request->getPost('entry_name'),
            'entry_description' => $request->getPost('entry_description'),
            'entry_status' => $request->getPost('entry_status'),
            'created_by' => session('user_id'),
            'created_date' => date('Y-m-d H:i:s'),
        ];

        $entryId = $entryModel->insert($data);

        // ✅ Save Tags
        $tags = $request->getPost('entry_tag');
        if ($tags) {
            $tagsArray = json_decode($tags, true);
            foreach ($tagsArray as $tag) {
                $tagModel->insert([
                    'entry_id' => $entryId,
                    'tag_name' => $tag['value'],
                    'tag_date' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // ✅ สร้างโฟลเดอร์ปลายทาง
        $entryFolder = WRITEPATH . 'uploads/keyresult/entry_' . $entryId . '/';
        if (!is_dir($entryFolder)) {
            mkdir($entryFolder, 0775, true);
        }

        // ✅ ดึงชื่อไฟล์ที่แนบมาจาก form
        $attachments = $this->request->getPost('attachments');
        $originalNames = $this->request->getPost('original_names'); // ✅ ดึงชื่อเดิม
        log_message('debug', '📦 attachments = ' . print_r($attachments, true));
        log_message('debug', '📦 original_names = ' . print_r($originalNames, true));

        if ($attachments && is_array($attachments)) {
            foreach ($attachments as $index => $filename) {
                $tmpPath = WRITEPATH . 'uploads/tmp/' . $filename;
                $newPath = $entryFolder . $filename;

                // ✅ ตรวจว่าไฟล์อยู่ใน tmp จริง
                if (is_file($tmpPath)) {
                    rename($tmpPath, $newPath); // ย้ายไฟล์

                    // ✅ บันทึกลงตาราง key_result_files พร้อมชื่อเดิม
                    $fileModel->insert([
                        'entry_id' => $entryId,
                        'original_name' => $originalNames[$index] ?? $filename, // ✅ ใช้ชื่อเดิมจาก array
                        'file_name' => $filename,
                        'file_path' => 'uploads/keyresult/entry_' . $entryId . '/' . $filename,
                        'uploaded_date' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // ✅ Clear cache หลังจากเพิ่มข้อมูล
        $model = new KeyresultModel();
        $model->clearKeyResultsCache($data['key_result_id']);

        return redirect()->to('/keyresult/view/' . $data['key_result_id'])->with('success', 'เพิ่มรายการสำเร็จ');
    }

    // ✅ แก้ไขฟังก์ชัน editEntry - เพิ่มการดึงข้อมูลที่ครบถ้วน
    public function editEntry($id)
    {
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        $entry = $entryModel->find($id);
        if (!$entry) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลที่ต้องการแก้ไข');
        }

        $files = $fileModel->where('entry_id', $id)->findAll();
        $tags = $tagModel->where('entry_id', $id)->findAll();

        $this->data['entry'] = $entry;
        $this->data['files'] = $files;
        $this->data['tags'] = array_column($tags, 'tag_name'); // แปลงเป็น array ของ tag_name
        $this->data['key_result_id'] = $entry['key_result_id']; // ✅ เพิ่มตัวนี้
        $this->data['is_edit'] = true; // ✅ บอกว่าเป็นโหมดแก้ไข

        $this->data['title'] = 'แก้ไข Key Result Entry';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/themes/metronic38/assets/plugins/custom/formrepeater/formrepeater.bundle.js',
            'assets/js/keyresult/form.js'
        ];

        // ดึงข้อมูล keyresult
        $keyresultModel = new KeyresultModel();
        $keyresultResults = $keyresultModel->getKeyResults(['conditions' => ['key_result_id' => $entry['key_result_id']]]);
        $this->data['keyresult'] = $keyresultResults[0] ?? null;


        $this->contentTemplate = 'keyresult/form';
        return $this->render();
    }

    // ✅ แก้ไขฟังก์ชัน updateEntry - ปรับ logic และ redirect
    public function updateEntry($id)
    {
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        // ✅ ตรวจสอบว่า entry มีอยู่จริง
        $entry = $entryModel->find($id);
        if (!$entry) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลที่ต้องการอัปเดต');
        }

        $data = [
            'entry_name' => $this->request->getPost('entry_name'),
            'entry_description' => $this->request->getPost('entry_description'),
            'entry_status' => $this->request->getPost('entry_status')
        ];

        $entryModel->update($id, $data);

        // ✅ ลบ tag เก่า เพิ่ม tag ใหม่
        $tagModel->where('entry_id', $id)->delete();
        $tags = $this->request->getPost('entry_tag');
        if ($tags) {
            $tagsArray = json_decode($tags, true);
            if ($tagsArray && is_array($tagsArray)) {
                foreach ($tagsArray as $tag) {
                    $tagModel->insert([
                        'entry_id' => $id,
                        'tag_name' => $tag['value'],
                        'tag_date' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // ✅ แนบไฟล์ใหม่จาก Dropzone
        $attachments = $this->request->getPost('attachments');
        $originalNames = $this->request->getPost('original_names'); // ✅ ดึงชื่อเดิม

        if ($attachments && is_array($attachments)) {
            $targetPath = WRITEPATH . 'uploads/keyresult/entry_' . $id . '/';
            if (!is_dir($targetPath)) mkdir($targetPath, 0775, true);

            foreach ($attachments as $index => $filename) {
                $tmpPath = WRITEPATH . 'uploads/tmp/' . $filename;
                $newPath = $targetPath . $filename;

                if (is_file($tmpPath)) {
                    rename($tmpPath, $newPath);
                    $fileModel->insert([
                        'entry_id' => $id,
                        'original_name' => $originalNames[$index] ?? $filename, // ✅ ใช้ชื่อเดิมจาก array
                        'file_name' => $filename,
                        'file_path' => 'uploads/keyresult/entry_' . $id . '/' . $filename,
                        'uploaded_date' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // ✅ Clear cache หลังจากแก้ไข
        $model = new KeyresultModel();
        $model->clearKeyResultsCache($entry['key_result_id']);

        // ✅ แก้ไข redirect path - กลับไป view key result แทน
        return redirect()->to('/keyresult/view/' . $entry['key_result_id'])->with('success', 'แก้ไขเรียบร้อยแล้ว');
    }

    // ✅ เพิ่มฟังก์ชันลบไฟล์แนบ
    public function deleteFile($fileId)
    {
        $fileModel = new KeyResultFileModel();
        $file = $fileModel->find($fileId);

        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบไฟล์']);
        }

        // ลบไฟล์จาก storage
        $filePath = WRITEPATH . $file['file_path'];
        if (is_file($filePath)) {
            unlink($filePath);
        }

        // ลบจากฐานข้อมูล
        $fileModel->delete($fileId);

        return $this->response->setJSON(['success' => true, 'message' => 'ลบไฟล์สำเร็จ']);
    }

// ✅ เพิ่มฟังก์ชันลบ entry
    public function deleteEntry($id)
    {
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        // ตรวจสอบว่า entry มีอยู่จริง
        $entry = $entryModel->find($id);
        if (!$entry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบรายการที่ต้องการลบ'
            ]);
        }

        try {
            // ลบไฟล์แนบทั้งหมด
            $files = $fileModel->where('entry_id', $id)->findAll();
            foreach ($files as $file) {
                $filePath = WRITEPATH . $file['file_path'];
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }

            // ลบโฟลเดอร์ entry (ถ้าว่าง)
            $entryFolder = WRITEPATH . 'uploads/keyresult/entry_' . $id . '/';
            if (is_dir($entryFolder) && count(scandir($entryFolder)) == 2) { // เหลือแค่ . และ ..
                rmdir($entryFolder);
            }

            // ลบข้อมูลในฐานข้อมูล
            $fileModel->where('entry_id', $id)->delete();  // ลบไฟล์
            $tagModel->where('entry_id', $id)->delete();   // ลบ tags
            $entryModel->delete($id);                      // ลบ entry

            // Clear cache หลังจากลบ
            $model = new KeyresultModel();
            $model->clearKeyResultsCache($entry['key_result_id']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'ลบรายการสำเร็จ'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Delete entry error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบรายการ'
            ]);
        }

        // ✅ Clear cache หลังจากลบ
        $model = new KeyresultModel();
        $model->clearKeyResultsCache($entry['key_result_id']);
    }

    public function getEntryDetails($id)
    {
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        $entry = $entryModel->find($id);
        if (!$entry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบรายการข้อมูล'
            ]);
        }

        $files = $fileModel->where('entry_id', $id)->findAll();
        $tags = $tagModel->where('entry_id', $id)->findAll();

        $entry['files'] = $files;
        $entry['tags'] = array_column($tags, 'tag_name');

        return $this->response->setJSON([
            'success' => true,
            'entry' => $entry
        ]);
    }

    public function viewEntry($id)
    {
        $entryModel = new KeyResultEntryModel();
        $fileModel = new KeyResultFileModel();
        $tagModel = new KeyResultTagModel();

        // ดึงข้อมูล entry
        $entry = $entryModel->getEntryWithCreator($id);
        if (!$entry) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('ไม่พบรายการข้อมูลที่ต้องการ');
        }

        // ดึงข้อมูล Key Result
        $keyresultModel = new KeyresultModel();
        $keyresultResults = $keyresultModel->getKeyResults([
            'conditions' => ['key_result_id' => $entry['key_result_id']]
        ]);
        $keyresult = $keyresultResults[0] ?? null;

        // ดึงข้อมูลไฟล์แนบ
        $files = $fileModel->where('entry_id', $id)->findAll();

        // ดึงข้อมูล tags
        $tags = $tagModel->where('entry_id', $id)->findAll();

        // ส่งข้อมูลให้ View
        $this->data['entry'] = $entry;
        $this->data['keyresult'] = $keyresult;
        $this->data['files'] = $files;
        $this->data['tags'] = array_column($tags, 'tag_name');
        $this->data['is_view'] = true; // บอกว่าเป็นโหมดดู

        $this->data['title'] = 'รายละเอียด ' . $entry['entry_name'];
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/keyresult/view-entry.js'
        ];

        $this->contentTemplate = 'keyresult/view-entry';
        return $this->render();
    }

}