<?php
namespace App\Controllers;

use App\Models\KeyresultModel;
use App\Models\ProgressModel;
use App\Models\ReportingPeriodModel;
use App\Models\ProgressCommentModel;
use App\Models\ProgressEntryModel;
use App\Models\KeyResultEntryModel;

class ProgressController extends TemplateController
{
    protected $allowed = [];

    public function index()
    {
        return redirect()->to(base_url('progress/list'));
    }

    public function list()
    {
        return redirect()->to(base_url('keyresult'));
    }

    public function view($keyResultId, $progressId = null)
    {
        // ปรับการตรวจสอบสิทธิ์ - ใช้ function ใหม่
        $viewPermissions = canViewProgressHistory($keyResultId);

        if (!$viewPermissions['can_view']) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ดู Key Result นี้');
        }

        $progressModel = new ProgressModel();
        $reportingPeriodModel = new ReportingPeriodModel();
        $commentModel = new ProgressCommentModel();
        $keyResultModel = new KeyresultModel();

        $keyresult = $progressModel->getKeyResultById($keyResultId);
        $departments = $keyResultModel->getDepartmentsByKeyResult($keyResultId);

        if (!$keyresult) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูล Key Result');
        }

        // ✅ เพิ่มการตรวจสอบว่าต้องการดูรายละเอียดเฉพาะรายการหรือไม่
        if ($progressId) {
            // ดูรายละเอียดเฉพาะรายการ
            $currentProgress = $progressModel->getProgressById($progressId);
            if (!$currentProgress) {
                return redirect()->back()->with('error', 'ไม่พบรายงานที่ต้องการ');
            }

            // ตรวจสอบว่า CoWorking สามารถดูรายงานนี้ได้หรือไม่
            if ($viewPermissions['can_see_approved_only'] && $currentProgress['status'] !== 'approved') {
                return redirect()->back()->with('error', 'คุณสามารถดูได้เฉพาะรายงานที่อนุมัติแล้ว');
            }

            // ดึงความคิดเห็นและรายการที่เกี่ยวข้อง
            $comments = $commentModel->getCommentsByProgressId($progressId);
            $currentProgress['comments'] = $comments;

            // ดึงรายการข้อมูลที่เกี่ยวข้อง
            if (class_exists('\App\Models\ProgressEntryModel')) {
                $progressEntryModel = new \App\Models\ProgressEntryModel();
                $keyResultFileModel = new \App\Models\KeyResultFileModel(); // ✅ เรียกใช้ Model ไฟล์

                $relatedEntries = $progressEntryModel->getEntriesByProgressId($progressId);

                // ✅ Loop ดึงไฟล์สำหรับแต่ละ entry
                foreach ($relatedEntries as &$entry) {
                    $entry['files'] = $keyResultFileModel->where('entry_id', $entry['entry_id'])->findAll();
                }

                $currentProgress['entries'] = $relatedEntries;
            }

            // ใช้ template สำหรับดูรายละเอียด
            $this->data['keyresult'] = $keyresult;
            $this->data['currentProgress'] = $currentProgress;
            $this->data['title'] = 'รายละเอียดรายงานความคืบหน้า';
            $this->contentTemplate = 'progress/view-detail';
            return $this->render();
        }

        // ดูประวัติทั้งหมด
        $reportingPeriods = $reportingPeriodModel->getActiveReportingPeriods();
        $progressHistory = $progressModel->getProgressHistory($keyResultId);

        // กรองประวัติตาม Role
        if ($viewPermissions['can_see_approved_only']) {
            $progressHistory = array_filter($progressHistory, function($progress) {
                return $progress['status'] === 'approved';
            });
        }

        // เพิ่มการดึงข้อมูลที่เกี่ยวข้องสำหรับแต่ละรายงาน
        if (class_exists('\App\Models\ProgressEntryModel')) {
            $progressEntryModel = new \App\Models\ProgressEntryModel();
            foreach ($progressHistory as &$progress) {
                $relatedEntries = $progressEntryModel->getEntriesByProgressId($progress['id']);
                $progress['related_entries'] = $relatedEntries;

                // ปรับการตรวจสอบสิทธิ์สำหรับแต่ละรายงาน
                $progress['can_edit'] = (
                    $progress['status'] === 'draft' &&
                    $progress['created_by'] == session('user_id') &&
                    $viewPermissions['can_see_all_status']
                );
                $progress['can_delete'] = (
                    $progress['status'] === 'draft' &&
                    ($progress['created_by'] == session('user_id') || hasRole('Admin')) &&
                    $viewPermissions['can_see_all_status']
                );
                $progress['can_submit'] = (
                    $progress['status'] === 'draft' &&
                    $progress['created_by'] == session('user_id') &&
                    $viewPermissions['can_see_all_status']
                );
                $progress['can_approve'] = (
                    $progress['status'] === 'submitted' &&
                    $viewPermissions['can_see_all_status'] &&
                    (hasRole('Approver') || hasRole('Admin')) &&
                    $progress['created_by'] != session('user_id')
                );
            }
        }

        $this->data['keyresult'] = $keyresult;
        $this->data['departments'] = $departments;
        $this->data['reportingPeriods'] = $reportingPeriods;
        $this->data['progressHistory'] = $progressHistory;
        $this->data['can_report_progress'] = canReportProgress($keyResultId);
        $this->data['view_permissions'] = $viewPermissions;
        $this->data['user_permissions'] = getDepartmentUserRoles();

        $this->data['title'] = 'ประวัติการรายงานความคืบหน้า';
        $this->data['cssSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css',
            'assets/css/progress/view.css'
        ];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/progress/view.js'
        ];

        $this->contentTemplate = 'progress/view';
        return $this->render();
    }

    public function form($keyResultId, $reportingPeriodId = null, $progressId = null)
    {

        // ตรวจสอบสิทธิ์การรายงานความคืบหน้า
        checkPermissionOrFail(
            canReportProgress($keyResultId),
            'คุณไม่มีสิทธิ์รายงานความคืบหน้าสำหรับ Key Result นี้'
        );

        $progressModel = new ProgressModel();
        $reportingPeriodModel = new ReportingPeriodModel();
        $progressEntryModel = new ProgressEntryModel();
        $keyResultEntryModel = new KeyResultEntryModel();
        $keyResultModel = new KeyresultModel();

        // ดึงข้อมูล Key Result
        $keyresult = $progressModel->getKeyResultById($keyResultId);

        if (!$keyresult) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูล Key Result');
        }

        // ดึงข้อมูลรอบการรายงาน
        $reportingPeriods = $reportingPeriodModel->getActiveReportingPeriods();

        // ดึงรายการ entries ทั้งหมดของ Key Result นี้
        $allEntries = $keyResultEntryModel->where('key_result_id', $keyResultId)
                                        ->where('entry_status', 'published')
                                        ->orderBy('entry_name')
                                        ->findAll();

        // ข้อมูลสำหรับการแก้ไข
        $progress = null;
        $selectedEntries = [];
        $isEdit = false;

        if ($progressId) {
            $progress = $progressModel->find($progressId);

            if (!$progress) {
                return redirect()->back()->with('error', 'ไม่พบข้อมูลที่ต้องการแก้ไข');
            }


            // ตรวจสอบสิทธิ์การแก้ไข
            if ($progress['status'] !== 'draft') {
                return redirect()->back()->with('error', 'ไม่สามารถแก้ไขรายงานที่ส่งแล้วหรืออนุมัติแล้ว');
            }

            if ($progress['created_by'] != session('user_id') && !isAdmin()) {
                return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขรายงานนี้');
            }

            if ($progress['key_result_id'] == $keyResultId) {
                $isEdit = true;
                // ดึงรายการ entries ที่เลือกไว้
                $selectedEntryObjects = $progressEntryModel->getEntriesByProgressId($progressId);
                $selectedEntries = array_column($selectedEntryObjects, 'entry_id');
            }
        }

        $departments = $keyResultModel->getDepartmentsByKeyResult($keyResultId);

        $this->data['keyresult'] = $keyresult;
        $this->data['reportingPeriods'] = $reportingPeriods;
        $this->data['progress'] = $progress;
        $this->data['is_edit'] = $isEdit;
        $this->data['key_result_id'] = $keyResultId;
        $this->data['reporting_period_id'] = $reportingPeriodId;
        $this->data['all_entries'] = $allEntries;
        $this->data['selected_entries'] = $selectedEntries;
        $this->data['departments'] = $departments;
        $this->data['user_permissions'] = getDepartmentUserRoles();

        $this->data['title'] = $isEdit ? 'แก้ไขรายงานความคืบหน้า' : 'บันทึกความคืบหน้า';
        $this->data['cssSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'
        ];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/progress/form.js'
        ];

        $this->contentTemplate = 'progress/form';
        return $this->render();
    }

    public function save()
    {

        // Debug ข้อมูลที่ได้รับ
        log_message('debug', '=== SAVE METHOD DEBUG ===');
        log_message('debug', 'REQUEST METHOD: ' . $this->request->getMethod());
        log_message('debug', 'POST DATA: ' . json_encode($this->request->getPost()));

        $progressDescription = $this->request->getPost('progress_description');
        $challenges = $this->request->getPost('challenges');
        $solutions = $this->request->getPost('solutions');
        $nextActions = $this->request->getPost('next_actions');

        log_message('debug', 'Quill Content Lengths:');
        log_message('debug', '- Progress Description: ' . strlen($progressDescription ?? ''));
        log_message('debug', '- Challenges: ' . strlen($challenges ?? ''));
        log_message('debug', '- Solutions: ' . strlen($solutions ?? ''));
        log_message('debug', '- Next Actions: ' . strlen($nextActions ?? ''));

        $progressModel = new ProgressModel();
        $progressEntryModel = new ProgressEntryModel();

        $keyResultId = $this->request->getPost('key_result_id');
        $reportingPeriodId = $this->request->getPost('reporting_period_id');

        log_message('debug', 'Progress save - Raw POST data: ' . print_r($this->request->getPost(), true));


        // ดึงข้อมูล target_value สำหรับคำนวณค่าจริง
        $keyresult = $progressModel->getKeyResultById($keyResultId);
        $targetValue = $keyresult['target_value'] ?? 0;

        // รับค่าเป็นเปอร์เซ็นต์
        $progressPercentage = (float)$this->request->getPost('progress_percentage');

        // คำนวณค่าจริง (progress_value) จากเปอร์เซ็นต์
        $progressValue = $targetValue > 0 ? ($progressPercentage * $targetValue) / 100 : 0;

        // หาเวอร์ชันถัดไป
        $nextVersion = $progressModel->getNextVersion($keyResultId, $reportingPeriodId);

        // debug สำหรับ Quill content
        $progressDescription = $this->request->getPost('progress_description');
        $challenges = $this->request->getPost('challenges');
        $solutions = $this->request->getPost('solutions');
        $nextActions = $this->request->getPost('next_actions');
        log_message('debug', 'Progress Description: ' . $progressDescription);
        log_message('debug', 'Challenges: ' . $challenges);
        log_message('debug', 'Solutions: ' . $solutions);
        log_message('debug', 'Next Actions: ' . $nextActions);

        $data = [
            'key_result_id' => $keyResultId,
            'reporting_period_id' => $reportingPeriodId,
            'progress_value' => $progressValue,
            'progress_percentage' => round($progressPercentage, 2),
            'progress_description' => $this->request->getPost('progress_description'),
            'challenges' => $this->request->getPost('challenges'),
            'solutions' => $this->request->getPost('solutions'),
            'next_actions' => $this->request->getPost('next_actions'),
            'status' => $this->request->getPost('status') ?? 'draft',
            'version' => $nextVersion,
            'created_by' => session('user_id'),
            'created_date' => date('Y-m-d H:i:s')
        ];

        log_message('debug', 'Data to insert: ' . print_r($data, true));

        $progressId = $progressModel->insert($data);

        if ($progressId) {
            // บันทึกรายการ entries ที่เลือก
            $selectedEntries = $this->request->getPost('selected_entries');
            if ($selectedEntries && is_array($selectedEntries)) {
                $progressEntryModel->saveProgressEntries($progressId, $selectedEntries);
                log_message('debug', 'Selected entries saved: ' . print_r($selectedEntries, true));
            }

            // บันทึกประวัติ
            $progressModel->insertHistory($progressId, 'created', 'สร้างรายงานความคืบหน้าใหม่', session('user_id'));

            return redirect()->to('/progress/view/' . $keyResultId)->with('success', 'บันทึกความคืบหน้าสำเร็จ');
        } else {
            log_message('error', 'Failed to save progress');
        }

        return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }


    public function update($progressId)
    {
        $progressModel = new ProgressModel();
        $progressEntryModel = new ProgressEntryModel();

        $progress = $progressModel->find($progressId);
        if (!$progress) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลที่ต้องการแก้ไข');
        }

        log_message('debug', 'Progress UPDATE - Raw POST data: ' . print_r($this->request->getPost(), true));


        // ดึงข้อมูล target_value สำหรับคำนวณค่าจริง
        $keyresult = $progressModel->getKeyResultById($progress['key_result_id']);
        $targetValue = $keyresult['target_value'] ?? 0;

        // รับค่าเป็นเปอร์เซ็นต์
        $progressPercentage = (float)$this->request->getPost('progress_percentage');

        // คำนวณค่าจริง (progress_value) จากเปอร์เซ็นต์
        $progressValue = $targetValue > 0 ? ($progressPercentage * $targetValue) / 100 : 0;

        // debug สำหรับ Quill content
        $progressDescription = $this->request->getPost('progress_description');
        $challenges = $this->request->getPost('challenges');
        $solutions = $this->request->getPost('solutions');
        $nextActions = $this->request->getPost('next_actions');
        log_message('debug', 'UPDATE - Progress Description: ' . $progressDescription);
        log_message('debug', 'UPDATE - Challenges: ' . $challenges);
        log_message('debug', 'UPDATE - Solutions: ' . $solutions);
        log_message('debug', 'UPDATE - Next Actions: ' . $nextActions);


        $oldData = $progress;
        $newData = [
            'progress_value' => $progressValue,
            'progress_percentage' => round($progressPercentage, 2),
            'progress_description' => $this->request->getPost('progress_description'),
            'challenges' => $this->request->getPost('challenges'),
            'solutions' => $this->request->getPost('solutions'),
            'next_actions' => $this->request->getPost('next_actions'),
            'status' => $this->request->getPost('status') ?? 'draft',
            'updated_by' => session('user_id'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        log_message('debug', 'UPDATE - Data to update: ' . print_r($newData, true));

        if ($progressModel->update($progressId, $newData)) {

            log_message('debug', 'Progress updated successfully with ID: ' . $progressId);

            // อัพเดทรายการ entries ที่เลือก
            $selectedEntries = $this->request->getPost('selected_entries');
            if ($selectedEntries && is_array($selectedEntries)) {
                $progressEntryModel->saveProgressEntries($progressId, $selectedEntries);
                log_message('debug', 'UPDATE - Selected entries saved: ' . print_r($selectedEntries, true));
            } else {
                // ถ้าไม่มีการเลือก entries ให้ลบทั้งหมด
                $progressEntryModel->where('progress_id', $progressId)->delete();
                log_message('debug', 'UPDATE - All entries removed');
            }

            // บันทึกประวัติการแก้ไข
            $progressModel->insertHistory(
                $progressId,
                'updated',
                'แก้ไขรายงานความคืบหน้า',
                session('user_id'),
                json_encode($oldData),
                json_encode($newData)
            );

            return redirect()->to('/progress/view/' . $progress['key_result_id'])->with('success', 'แก้ไขความคืบหน้าสำเร็จ');
        } else {
            log_message('error', 'Failed to update progress with ID: ' . $progressId);
        }

        return redirect()->back()->with('error', 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล');
    }

    public function submit($progressId)
    {
        $progressModel = new ProgressModel();
        $progress = $progressModel->find($progressId);

        if (!$progress) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }

        // ตรวจสอบสิทธิ์การส่งรายงาน
        if ($progress['created_by'] != session('user_id') && !isAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ส่งรายงานนี้'
            ]);
        }

        // ตรวจสอบสถานะ
        if ($progress['status'] !== 'draft') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'รายงานนี้ถูกส่งไปแล้วหรืออนุมัติแล้ว'
            ]);
        }

        // ตรวจสอบความสมบูรณ์ของข้อมูล
        if (empty($progress['progress_value']) || $progress['progress_value'] <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'กรุณากรอกค่าความคืบหน้าก่อนส่งรายงาน'
            ]);
        }


        $data = [
            'status' => 'submitted',
            'submitted_by' => session('user_id'),
            'submitted_date' => date('Y-m-d H:i:s'),
            'updated_by' => session('user_id'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        if ($progressModel->update($progressId, $data)) {
            // บันทึกประวัติ
            $progressModel->insertHistory($progressId, 'submitted', 'ส่งรายงานเพื่อขออนุมัติ', session('user_id'));

            return $this->response->setJSON(['success' => true, 'message' => 'ส่งรายงานเพื่อขออนุมัติสำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }

    public function approve($progressId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        log_message('debug', '=== APPROVE START ===');
        log_message('debug', 'Progress ID: ' . $progressId);

        $progressModel = new ProgressModel();
        $commentModel = new ProgressCommentModel(); // เพิ่มบรรทัดนี้
        $progress = $progressModel->find($progressId);

        if (!$progress) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }

        $canApprove = canApproveProgress($progressId);
        if (!$canApprove) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์อนุมัติรายงานนี้'
            ]);
        }

        if ($progress['status'] !== 'submitted') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'รายงานนี้ยังไม่ได้ส่งหรืออนุมัติไปแล้ว'
            ]);
        }

        $comment = $this->request->getPost('approve_comment') ?? '';

        $data = [
            'status' => 'approved',
            'approved_by' => session('user_id'),
            'approved_date' => date('Y-m-d H:i:s'),
            'updated_by' => session('user_id'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        if ($progressModel->update($progressId, $data)) {
            // ✅ บันทึก approve comment ลงใน progress_comments table
            if (!empty($comment)) {
                $commentModel->insert([
                    'progress_id' => $progressId,
                    'comment_type' => 'approve',
                    'comment_text' => $comment,
                    'commenter_role' => 'approver',
                    'created_by' => session('user_id'),
                    'created_date' => date('Y-m-d H:i:s')
                ]);
            }

            // บันทึกประวัติ
            $historyNote = 'อนุมัติรายงานความคืบหน้า';
            if (!empty($comment)) {
                $historyNote .= ': ' . $comment;
            }

            $progressModel->insertHistory($progressId, 'approved', $historyNote, session('user_id'));

            return $this->response->setJSON(['success' => true, 'message' => 'อนุมัติรายงานสำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
    }

    public function delete($progressId)
    {
        $progressModel = new ProgressModel();

        $progress = $progressModel->find($progressId);
        if (!$progress) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }


        // ตรวจสอบสถานะ
        if ($progress['status'] !== 'draft') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'สามารถลบได้เฉพาะรายงานที่มีสถานะ "ฉบับร่าง" เท่านั้น'
            ]);
        }

        // ตรวจสอบสิทธิ์
        if ($progress['created_by'] != session('user_id') && !hasRole('Admin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ลบรายงานนี้'
            ]);
        }

        try {

            $progressModel->delete($progressId);
            return $this->response->setJSON(['success' => true, 'message' => 'ลบรายงานสำเร็จ']);

        } catch (\Exception $e) {
            log_message('error', 'Delete progress error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
        }
    }


    public function addComment()
    {
        $commentModel = new ProgressCommentModel();

        $data = [
            'progress_id' => $this->request->getPost('progress_id'),
            'comment_type' => $this->request->getPost('comment_type') ?? 'feedback',
            'comment_text' => $this->request->getPost('comment_text'),
            'commenter_role' => $this->request->getPost('commenter_role') ?? 'manager',
            'created_by' => session('user_id'),
            'created_date' => date('Y-m-d H:i:s')
        ];

        if ($commentModel->insert($data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'เพิ่มความคิดเห็นสำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด']);
    }

    public function getProgressDetails($progressId)
    {
        $progressModel = new ProgressModel();
        $progressEntryModel = new ProgressEntryModel();
        $commentModel = new ProgressCommentModel();

        $progress = $progressModel->getProgressById($progressId);

        if (!$progress) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบข้อมูลรายงานความคืบหน้า'
            ]);
        }

        // ดึงรายการข้อมูลที่เกี่ยวข้อง
        $relatedEntries = $progressEntryModel->getEntriesByProgressId($progressId);
        $progress['entries'] = $relatedEntries;

        // ดึงความคิดเห็น
        $comments = $commentModel->getCommentsByProgressId($progressId);
        $progress['comments'] = $comments;

        return $this->response->setJSON([
            'success' => true,
            'progress' => $progress
        ]);
    }

    public function reject($progressId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        if (!canApproveProgress($progressId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ปฏิเสธรายงานนี้'
            ]);
        }

        $progressModel = new ProgressModel();
        $commentModel = new ProgressCommentModel(); // เพิ่มบรรทัดนี้
        $progress = $progressModel->find($progressId);

        if (!$progress || $progress['status'] !== 'submitted') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถปฏิเสธรายงานนี้ได้'
            ]);
        }

        $rejectReason = $this->request->getPost('reject_reason');
        if (empty(trim($rejectReason))) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'กรุณาระบุเหตุผลในการปฏิเสธ'
            ]);
        }

        $data = [
            'status' => 'rejected',
            'updated_by' => session('user_id'),
            'updated_date' => date('Y-m-d H:i:s')
        ];

        if ($progressModel->update($progressId, $data)) {
            // ✅ บันทึก reject reason ลงใน progress_comments table
            $commentModel->insert([
                'progress_id' => $progressId,
                'comment_type' => 'reject',
                'comment_text' => $rejectReason,
                'commenter_role' => 'approver',
                'created_by' => session('user_id'),
                'created_date' => date('Y-m-d H:i:s')
            ]);

            // บันทึกประวัติ
            $progressModel->insertHistory(
                $progressId,
                'rejected',
                'ปฏิเสธรายงาน: ' . $rejectReason,
                session('user_id')
            );

            return $this->response->setJSON(['success' => true, 'message' => 'ปฏิเสธรายงานสำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
    }

    // สำหรับดู pending approvals
    public function pendingApprovals()
    {
        if (!isApprover()) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        // 🔍 DEBUG: เช็ค session values
        log_message('debug', '=== PENDING APPROVALS DEBUG ===');
        log_message('debug', 'User ID: ' . session('user_id'));
        log_message('debug', 'Department: ' . session('department'));
        log_message('debug', 'Role: ' . session('role'));

        $progressModel = new ProgressModel();

        $pendingApprovals = $this->getPendingApprovalsList();

        $this->data['pending_approvals'] = $pendingApprovals;
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->data['title'] = 'รายงานที่รอการอนุมัติ';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/progress/pending-approvals.js'
        ];
        $this->contentTemplate = 'progress/pending-approvals';
        return $this->render();
    }

    // Progress list เฉพาะสำหรับ Approver
    public function approverList()
    {
        // ตรวจสอบสิทธิ์ Approver
        $authCheck = $this->requireApprover('คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        if ($authCheck) return $authCheck;

        $progressModel = new ProgressModel();

        // ดึงเฉพาะ Key Results ที่มีรายงานรออนุมัติ
        $conditions = [
            'department_id' => session('department'),
            'year' => '2568',
            'status' => 'submitted' // เฉพาะที่รออนุมัติ
        ];

        $keyresults = $progressModel->getKeyResults([
            'conditions' => $conditions
        ]);

        // เพิ่มข้อมูลสำหรับการอนุมัติ
        foreach ($keyresults as &$keyresult) {
            $latestProgress = $progressModel->getLatestProgress($keyresult['key_result_id']);
            $keyresult['latest_progress'] = $latestProgress;

            // สิทธิ์การอนุมัติ
            $keyresult['can_approve'] = $latestProgress ? canApproveProgress($latestProgress['id']) : false;
        }

        $this->data['keyresults'] = $keyresults;
        $this->data['user_permissions'] = getDepartmentUserRoles();
        $this->data['title'] = 'รายงานรออนุมัติ';
        $this->data['cssSrc'] = ['assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.css'];
        $this->data['jsSrc'] = [
            'assets/themes/metronic38/assets/plugins/custom/datatables/datatables.bundle.js',
            'assets/js/progress/approver-list.js'
        ];

        $this->contentTemplate = 'progress/approver-list';
        return $this->render();
    }

    private function getPendingApprovalsList()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('key_result_progress krp')
            ->select('
                krp.*,
                kr.name as key_result_name,
                u.full_name as creator_name,
                rp.quarter_name,
                rp.year
            ')
            ->join('key_results kr', 'krp.key_result_id = kr.id')
            ->join('users u', 'krp.created_by = u.id')
            ->join('reporting_periods rp', 'krp.reporting_period_id = rp.id')
            ->join('key_result_departments krd', 'kr.id = krd.key_result_id')
            ->where('krp.status', 'submitted')
            ->where('krd.department_id', session('department'))
            ->where('krd.role', 'Leader');

        // ✅ Admin เห็นทุกรายงาน, Approver ทั่วไปไม่เห็นของตัวเอง
        if (!hasRole('Admin')) {
            $builder->where('krp.created_by !=', session('user_id'));
        }

        return $builder->orderBy('krp.submitted_date', 'ASC')
                    ->get()
                    ->getResultArray();
    }

/**
 * แสดงรายงานรายละเอียดแบบเต็ม (สำหรับ Admin/Strategic Viewer)
 */
public function detailedReport($keyResultId)
{
    // ตรวจสอบสิทธิ์
    if (!hasRole('Admin') && !canViewStrategicDashboard()) {
        return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์เข้าถึงรายงานรายละเอียด');
    }

    $progressModel = new ProgressModel(); // เปลี่ยนเป็น new แทน
    $keyResultModel = new KeyresultModel();

    // ดึงข้อมูล Key Result
    $keyresult = $progressModel->getKeyResultById($keyResultId);
    if (!$keyresult) {
        return redirect()->back()->with('error', 'ไม่พบข้อมูล Key Result');
    }

    // ดึงประวัติการรายงานทั้งหมด
    $progressHistory = $progressModel->getProgressHistory($keyResultId);

    // ดึงข้อมูลหน่วยงาน
    $departments = $keyResultModel->getDepartmentsByKeyResult($keyResultId);

    // ดึงสถิติรายละเอียด
    $detailedStats = $this->generateDetailedStats($keyResultId, $progressHistory);

    $this->data['keyresult'] = $keyresult;
    $this->data['departments'] = $departments;
    $this->data['progressHistory'] = $progressHistory;
    $this->data['detailedStats'] = $detailedStats;
    $this->data['title'] = 'รายงานรายละเอียด - ' . $keyresult['key_result_name'];

    $this->contentTemplate = 'progress/detailed-report';
    return $this->render();
}

/**
 * สร้างสถิติรายละเอียดสำหรับรายงาน
 */
private function generateDetailedStats($keyResultId, $progressHistory)
{
    $totalReports = count($progressHistory);
    $approvedReports = array_filter($progressHistory, function($p) {
        return $p['status'] === 'approved';
    });

    $submittedReports = array_filter($progressHistory, function($p) {
        return in_array($p['status'], ['submitted', 'approved', 'rejected']);
    });

    $avgProgress = 0;
    if (!empty($approvedReports)) {
        $totalProgress = array_sum(array_column($approvedReports, 'progress_percentage'));
        $avgProgress = round($totalProgress / count($approvedReports), 1);
    }

    $latestUpdate = !empty($progressHistory) ? $progressHistory[0]['updated_date'] : null;
    $approvalRate = count($submittedReports) > 0 ? round((count($approvedReports) / count($submittedReports)) * 100, 1) : 0;

    return [
        'total_reports' => $totalReports,
        'approved_reports' => count($approvedReports),
        'submitted_reports' => count($submittedReports),
        'avg_progress' => $avgProgress,
        'latest_update' => $latestUpdate,
        'approval_rate' => $approvalRate
    ];
}
}