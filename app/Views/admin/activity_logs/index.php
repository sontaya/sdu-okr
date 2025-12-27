<?php
/**
 * Security Logs View
 */
?>

<div class="row g-6 g-xl-9 mb-6">
    <!-- Stats Cards -->
    <div class="col-md-4 col-xl-4">
        <div class="card bg-light-primary h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                <i class="ki-outline ki-shield-search fs-2x text-primary mb-2"></i>
                <div class="fs-2 fw-bold text-primary"><?= number_format($stats['total_logs']) ?></div>
                <div class="fs-8 text-muted">Total Logs Included</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-4">
        <div class="card bg-light-success h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                <i class="ki-outline ki-key fs-2x text-success mb-2"></i>
                <div class="fs-2 fw-bold text-success"><?= number_format($stats['today_logins']) ?></div>
                <div class="fs-8 text-muted">Today's Logins (Success)</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-xl-4">
        <div class="card bg-light-danger h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center p-4">
                <i class="ki-outline ki-shield-cross fs-2x text-danger mb-2"></i>
                <div class="fs-2 fw-bold text-danger"><?= number_format($stats['today_failures']) ?></div>
                <div class="fs-8 text-muted">Today's Failed Attempts</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <!-- Search -->
            <div class="d-flex align-items-center position-relative my-1 me-3">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <form action="" method="get" class="d-flex align-items-center">
                    <input type="text" name="search" value="<?= esc($filters['search']) ?>"
                           class="form-control form-control-solid w-250px ps-13 me-3"
                           placeholder="Search logs..." />

                    <!-- Event Filter -->
                    <select name="event_type" class="form-select form-select-solid w-200px me-3" onchange="this.form.submit()">
                        <option value="">All Events</option>
                        <option value="login" <?= $filters['event_type'] == 'login' ? 'selected' : '' ?>>Login (Success)</option>
                        <option value="failed_login" <?= $filters['event_type'] == 'failed_login' ? 'selected' : '' ?>>Login (Failed)</option>
                        <option value="logout" <?= $filters['event_type'] == 'logout' ? 'selected' : '' ?>>Logout</option>
                        <option value="permission_change" <?= $filters['event_type'] == 'permission_change' ? 'selected' : '' ?>>Permission Change</option>
                        <option value="unauthorized_access" <?= $filters['event_type'] == 'unauthorized_access' ? 'selected' : '' ?>>Unauthorized Access</option>
                        <option value="create_user" <?= $filters['event_type'] == 'create_user' ? 'selected' : '' ?>>Create User</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <?php if(!empty($filters['search']) || !empty($filters['event_type'])): ?>
                        <a href="<?= base_url('admin/activity-logs') ?>" class="btn btn-light btn-sm ms-2">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-125px">Timestamp</th>
                        <th class="min-w-125px">User</th>
                        <th class="min-w-100px">Action</th>
                        <th class="min-w-100px">IP / Agent</th>
                        <th class="min-w-200px">Details</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    <?php if(empty($logs)): ?>
                        <tr><td colspan="5" class="text-center">No logs found</td></tr>
                    <?php else: ?>
                        <?php foreach($logs as $log):
                            $badgeClass = 'badge-secondary';
                            switch($log['action']) {
                                case 'login': $badgeClass = 'badge-success'; break;
                                case 'failed_login': $badgeClass = 'badge-danger'; break;
                                case 'logout': $badgeClass = 'badge-light'; break;
                                case 'permission_change': $badgeClass = 'badge-warning'; break;
                                case 'unauthorized_access': $badgeClass = 'badge-light-danger'; break;
                                case 'create_user': $badgeClass = 'badge-primary'; break;
                            }

                            $details = json_decode($log['context'], true);
                            $detailsStr = $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '';
                        ?>
                        <tr>
                            <td><?= $log['created_date'] ?></td>
                            <td>
                                <?php if($log['user_id'] && $log['user_id'] > 0): ?>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold"><?= esc($log['full_name'] ?? 'Unknown') ?></span>
                                        <span class="text-muted fs-8"><?= esc($log['uid'] ?? '') ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Guest / System</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>"><?= esc($log['action']) ?></span>
                            </td>
                            <td>
                                <div class="fs-7"><?= esc($log['ip_address']) ?></div>
                                <div class="text-muted fs-9 text-truncate" style="max-width: 150px;" title="<?= esc($log['user_agent']) ?>">
                                    <?= esc($log['user_agent']) ?>
                                </div>
                            </td>
                            <td>
                                <?php if($details): ?>
                                    <!-- Simple expander or code block -->
                                    <div class="fs-7 text-gray-600">
                                        <?php
                                            // Handle specific displays
                                            if ($log['action'] == 'permission_change' && isset($details['role'])) {
                                                echo "Role: <strong>" . esc($details['role']) . "</strong> " . esc($details['action']);
                                                if (isset($details['target_user_id'])) echo " for User ID " . $details['target_user_id'];
                                            } elseif ($log['action'] == 'failed_login') {
                                                echo "Reason: " . esc($details['reason'] ?? 'Unknown');
                                                if (isset($details['username'])) echo " (User: " . esc($details['username']) . ")";
                                            } else {
                                                // General JSON display, maybe truncated
                                                $display = substr(json_encode($details, JSON_UNESCAPED_UNICODE), 0, 100);
                                                echo esc($display);
                                                if (strlen($display) >= 100) echo '...';
                                            }
                                        ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-end">
            <?= $pager->links('default', 'default_full') // Assuming pagination view exists ?>
        </div>
    </div>
</div>
