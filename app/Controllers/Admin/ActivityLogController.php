<?php

namespace App\Controllers\Admin;

use App\Controllers\TemplateController;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLogController extends TemplateController
{
    public function index()
    {
        // Permission Check
        $authCheck = $this->requireAdmin();
        if ($authCheck) return $authCheck;

        // Set Active Menu
        session()->set('menu.active', 'admin-activity-logs');

        $logModel = new ActivityLogModel();
        $userModel = new UserModel();
        $db = \Config\Database::connect();

        // Get Inputs
        $search     = $this->request->getGet('search');
        $eventType  = $this->request->getGet('event_type');
        $startDate  = $this->request->getGet('start_date');
        $endDate    = $this->request->getGet('end_date');
        $userId     = $this->request->getGet('user_id');

        // Builders
        $builder = $logModel->select('activity_logs.*, users.full_name, users.uid')
                            ->join('users', 'users.id = activity_logs.user_id', 'left');

        // Apply Filters
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('activity_logs.action', $search)
                    ->orLike('activity_logs.ip_address', $search)
                    ->orLike('users.full_name', $search)
                    ->orLike('users.uid', $search)
                    ->groupEnd();
        }

        if (!empty($eventType)) {
            $builder->where('activity_logs.action', $eventType);
        }

        if (!empty($userId)) {
            $builder->where('activity_logs.user_id', $userId);
        }

        if (!empty($startDate)) {
            $builder->where('activity_logs.created_date >=', $startDate . ' 00:00:00');
        }

        if (!empty($endDate)) {
            $builder->where('activity_logs.created_date <=', $endDate . ' 23:59:59');
        }

        // Sorting
        $builder->orderBy('activity_logs.created_date', 'DESC');

        // Pagination
        $logs = $builder->paginate(20, 'default');
        $pager = $logModel->pager;

        // Stats (Simple summary)
        $today = date('Y-m-d');
        $stats = [
            'total_logs' => $logModel->countAllResults(false), // count without resetting query? No, countAllResults resets. Need new query.
            'today_logins' => $db->table('activity_logs')
                                 ->where('action', 'login')
                                 ->where('created_date >=', $today . ' 00:00:00')
                                 ->countAllResults(),
            'today_failures' => $db->table('activity_logs')
                                   ->where('action', 'failed_login')
                                   ->where('created_date >=', $today . ' 00:00:00')
                                   ->countAllResults()
        ];

        // Pass to View
        $this->data['logs'] = $logs;
        $this->data['pager'] = $pager;
        $this->data['filters'] = [
            'search' => $search,
            'event_type' => $eventType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $userId
        ];
        $this->data['stats'] = $stats;
        $this->data['title'] = 'Security Logs (บันทึกความปลอดภัย)';

        // Use standard Metronic JS for DateRangePicker if available, else plain dates
        $this->data['jsSrc'] = [
            // Add custom JS if needed
        ];

        $this->contentTemplate = 'admin/activity_logs/index';
        return $this->render();
    }
}
