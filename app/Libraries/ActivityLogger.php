<?php

namespace App\Libraries;

use App\Models\ActivityLogModel;

class ActivityLogger
{
    protected $activityLogModel;

    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
    }

    /**
     * Log a user activity
     *
     * @param string $action The action name (e.g., 'login', 'create_user')
     * @param array $context Additional context data (will be JSON encoded)
     * @param int|null $userId Optional user ID (defaults to current logged-in user)
     * @return bool|int|string Insert ID or false
     */
    public function log(string $action, array $context = [], ?int $userId = null, ?string $description = null, ?string $module = null)
    {
        // specific check for login usage where session might be newly created
        if ($userId === null) {
            $session = session();
            $userId = $session->get('user_id'); // Adjust key based on Auth implementation
        }

        // access request service
        $request = service('request');

        $data = [
            'user_id'    => $userId ?? 0, // 0 for guest/system or unknown
            'action'     => $action,
            'description'=> $description,
            'module'     => $module,
            'context'    => json_encode($context, JSON_UNESCAPED_UNICODE),
            'ip_address' => $request->getIPAddress(),
            'user_agent' => (string) $request->getUserAgent(),
            'session_id' => session_id(),
            'created_date' => date('Y-m-d H:i:s')
        ];

        return $this->activityLogModel->insert($data);
    }
}
