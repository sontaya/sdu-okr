<!-- สร้างไฟล์ app/Views/keyresult/unified-list.php -->

<!--begin::Breadcrumb-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <!--begin::Path-->
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('keyresult/list') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-home fs-6 me-1"></i>
            My Key Results
        </a>
    </div>
    <!--end::Path-->
</div>
<!--end::Breadcrumb-->

<!-- Summary Cards -->
<div class="row g-6 g-xl-9 mb-6">
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-primary">
                            <i class="ki-outline ki-abstract-26 fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-primary"><?= $stats['total_keyresults'] ?></div>
                        <div class="fs-7 text-muted">Key Results ทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-success">
                            <i class="ki-outline ki-chart-simple fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-success"><?= $stats['can_report_count'] ?></div>
                        <div class="fs-7 text-muted">สามารถรายงานได้</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-warning">
                            <i class="ki-outline ki-notification-status fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-warning"><?= $stats['pending_reports'] + $stats['submitted_reports'] ?></div>
                        <div class="fs-7 text-muted">รอดำเนินการ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-info">
                            <i class="ki-outline ki-check-square fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-info"><?= $stats['approved_reports'] ?></div>
                        <div class="fs-7 text-muted">อนุมัติแล้ว</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" data-kt-keyresults-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="ค้นหา Key Result" />
            </div>
        </div>
        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
            <!-- Role Filter -->
            <div class="w-150px">
                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="บทบาท" data-kt-keyresults-filter="role">
                    <option></option>
                    <option value="all">ทั้งหมด</option>
                    <option value="Leader">Leader</option>
                    <option value="CoWorking">CoWorking</option>
                </select>
            </div>
            <!-- Progress Status Filter -->
            <div class="w-150px">
                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="สถานะรายงาน" data-kt-keyresults-filter="progress_status">
                    <option></option>
                    <option value="all">ทั้งหมด</option>
                    <option value="no_report">ยังไม่รายงาน</option>
                    <option value="draft">ฉบับร่าง</option>
                    <option value="submitted">ส่งแล้ว</option>
                    <option value="approved">อนุมัติแล้ว</option>
                    <option value="rejected">ปฏิเสธ</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_keyresults_table">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_keyresults_table .form-check-input" value="1" />
                        </div>
                    </th>
                    <th class="min-w-300px">Key Result</th>
                    <th class="min-w-140px">ความคืบหน้า</th>
                    <th class="min-w-120px">สถานะรายงาน</th>
                    <th class="min-w-120px">รอบรายงาน</th>
                    <th class="text-end min-w-100px">การดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                <?php foreach ($keyresults as $item): ?>
                <?php
                    //กำหนด Role ที่ถูกต้อง
                    $keyResultRole = $item['key_result_role'] ?? $item['key_result_dep_role'];
                ?>
                <tr data-role="<?= esc($keyResultRole) ?>" data-progress-status="<?= esc($item['progress_status']) ?>">
                    <td>
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="<?= $item['key_result_id'] ?>" />
                        </div>
                    </td>

                    <td>
                        <div class="d-flex">
                            <!-- Thumbnail & Role Section -->
                            <div class="d-flex flex-column align-items-center me-4">
                                <!-- Thumbnail -->
                                <a href="<?= base_url('keyresult/view/' . $item['key_result_id']) ?>" class="symbol symbol-50px mb-2">
                                    <?php
                                    $og_id = isset($item['og_id']) ? (int)$item['og_id'] : 1;
                                    if ($og_id < 1 || $og_id > 5) $og_id = 1;
                                    $badge_image = base_url('assets/images/badge-goal' . $og_id . '.png');
                                    ?>
                                    <span class="symbol-label" style="background-image:url(<?= $badge_image ?>); background-size: contain; background-repeat: no-repeat; background-position: center;"></span>
                                </a>

                                <!-- Role Badge ใต้ Thumbnail -->
                                <?php
                                    $roleClass = $keyResultRole === 'Leader' ? 'badge-primary' : 'badge-info';
                                    $roleIcon = $keyResultRole === 'Leader' ? 'ki-crown' : 'ki-people';
                                ?>
                                <div class="badge <?= $roleClass ?> fs-8 px-2 py-1">
                                    <i class="ki-outline <?= $roleIcon ?> fs-7 me-1"></i>
                                    <?= esc($keyResultRole) ?>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <!-- Goal Badge -->
                                <?php $goal_class = 'goal-badge-' . $og_id; ?>
                                <div class="mb-2">
                                    <span class="badge goal-badge <?= $goal_class ?> fs-7">
                                        <?= esc($item['og_name']) ?>
                                    </span>
                                </div>

                                <!-- Objective -->
                                <div class="fs-7 fw-bold obj-color-<?= $og_id ?>">
                                    Obj:<?= esc($item['objective_name']) ?>
                                </div>

                                <!-- Title -->
                                <div class="mb-1">
                                    <a href="<?= base_url('keyresult/view/' . $item['key_result_id']) ?>" class="text-gray-600 text-hover-goal-<?= $og_id ?> fs-7 fw-semibold">
                                    KR: <?= esc($item['key_result_name']) ?>
                                    </a>
                                </div>

                                <!-- KR Description -->
                                <div class="text-muted fs-8">
                                    <span class="text-gray-500">เป้าหมาย: <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?></span>
                                    <span class="text-gray-400 mx-1">|</span>
                                    <span class="text-success">
                                        <i class="ki-outline ki-document fs-7 me-1 text-success"></i> รายการที่เผยแพร่
                                        <?= $item['published_entries_count'] ?? 0 ?> รายการ
                                    </span>
                                </div>

                                <!-- Last Update -->
                                <?php if ($item['last_update']): ?>
                                <div class="text-muted fs-8 mt-1">
                                    <i class="ki-outline ki-time fs-7 me-1"></i>
                                    อัพเดทล่าสุด: <?= date('d M Y H:i', strtotime($item['last_update'])) ?>
                                </div>
                                <?php endif; ?>

                                <!-- Hidden text สำหรับ DataTables search -->
                                <span class="d-none"><?= esc($keyResultRole) ?></span>

                            </div>
                        </div>
                    </td>

                    <!-- Progress -->
                    <td>
                        <?php if ($item['latest_progress']): ?>
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-1">
                                <div class="progress h-8px flex-row-fluid me-2">
                                    <?php
                                    $percentage = $item['progress_percentage'];
                                    $progressClass = $percentage >= 80 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <div class="progress-bar <?= $progressClass ?>" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <span class="badge badge-light-primary fs-7"><?= number_format($percentage, 1) ?>%</span>
                            </div>
                            <div class="text-muted fs-8">
                                <?= esc($item['latest_progress']['progress_value']) ?> / <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="d-flex align-items-center">
                            <div class="progress h-8px flex-row-fluid me-2">
                                <div class="progress-bar bg-light-secondary" style="width: 100%"></div>
                            </div>
                            <span class="badge badge-light-secondary fs-7">0%</span>
                        </div>
                        <div class="text-muted fs-8">ยังไม่มีรายงาน</div>
                        <?php endif; ?>
                    </td>

                    <!-- Status -->
                    <td>
                        <?php
                        $statusConfig = [
                            'no_report' => ['badge' => 'badge-light-secondary', 'text' => 'ยังไม่รายงาน', 'icon' => 'ki-outline ki-information-5'],
                            'draft' => ['badge' => 'badge-light-warning', 'text' => 'ฉบับร่าง', 'icon' => 'ki-outline ki-pencil'],
                            'submitted' => ['badge' => 'badge-light-info', 'text' => 'รออนุมัติ', 'icon' => 'ki-outline ki-time'],
                            'approved' => ['badge' => 'badge-light-success', 'text' => 'อนุมัติแล้ว', 'icon' => 'ki-outline ki-check'],
                            'rejected' => ['badge' => 'badge-light-danger', 'text' => 'ปฏิเสธ', 'icon' => 'ki-outline ki-cross']
                        ];
                        $config = $statusConfig[$item['progress_status']] ?? $statusConfig['no_report'];
                        ?>
                        <div class="badge <?= $config['badge'] ?> fs-7 fw-bold px-3 py-2">
                            <i class="<?= $config['icon'] ?> fs-7 me-1"></i>
                            <?= $config['text'] ?>
                        </div>
                    </td>

                    <!-- Reporting Period -->
                    <td>
                        <div class="text-gray-800 fw-semibold fs-7">
                            <?= $item['reporting_info']['period_text'] ?: '-' ?>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td class="text-end">
                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary btn-flex btn-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            การดำเนินการ
                            <i class="ki-outline ki-down fs-5 ms-1"></i>
                        </a>

                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-250px py-4" data-kt-menu="true">

                            <!-- View Details (ทุกคนที่เกี่ยวข้องดูได้) -->
                            <?php if ($item['can_view']): ?>
                            <div class="menu-item px-3">
                                <a href="<?= base_url('keyresult/view/' . $item['key_result_id']) ?>" class="menu-link px-3">
                                    <i class="ki-outline ki-eye fs-6 me-2"></i>
                                    ดูรายละเอียด Key Result
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- Progress View -->
                            <div class="menu-item px-3">
                                <a href="<?= base_url('progress/view/' . $item['key_result_id']) ?>" class="menu-link px-3">
                                    <i class="ki-outline ki-chart-line fs-6 me-2"></i>
                                    ดูประวัติความคืบหน้า
                                    <?php if ($item['key_result_role'] === 'CoWorking'): ?>
                                    <span class="badge badge-light-info ms-1">อนุมัติแล้วเท่านั้น</span>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="separator my-2"></div>

                            <!-- Entry Management (Leader และ CoWorking) -->
                            <?php if ($item['can_manage_entries']): ?>
                            <div class="menu-item px-3">
                                <a href="<?= base_url('keyresult/form/' . $item['key_result_id']) ?>" class="menu-link px-3">
                                    <i class="ki-outline ki-plus fs-6 me-2"></i>
                                    เพิ่มรายการข้อมูล
                                </a>
                            </div>
                            <div class="separator my-2"></div>
                            <?php endif; ?>

                            <!-- Progress Reporting (เฉพาะ Leader เท่านั้น) -->
                            <?php if ($item['can_report']): ?>
                                <div class="menu-item px-3">
                                    <div class="text-muted fs-8 px-3 py-2">
                                        <i class="ki-outline ki-crown fs-7 me-1 text-primary"></i>
                                        สิทธิ์ Leader เท่านั้น
                                    </div>
                                </div>

                                <!-- New Report -->
                                <?php if ($item['progress_status'] === 'no_report' || $item['progress_status'] === 'approved' || $item['progress_status'] === 'rejected'): ?>
                                <div class="menu-item px-3">
                                    <a href="<?= base_url('progress/form/' . $item['key_result_id']) ?>" class="menu-link px-3">
                                        <i class="ki-outline ki-document-add fs-6 me-2"></i>
                                        รายงานความคืบหน้า
                                    </a>
                                </div>
                                <?php endif; ?>

                                <!-- Edit Draft -->
                                <?php if ($item['can_edit_report']): ?>
                                <div class="menu-item px-3">
                                    <a href="<?= base_url('progress/form/' . $item['key_result_id'] . '/' . $item['latest_progress']['reporting_period_id'] . '/' . $item['latest_progress']['id']) ?>" class="menu-link px-3">
                                        <i class="ki-outline ki-pencil fs-6 me-2"></i>
                                        แก้ไขรายงาน
                                    </a>
                                </div>
                                <?php endif; ?>

                                <!-- Submit Report -->
                                <?php if ($item['can_submit_report']): ?>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 submit-report-btn" data-progress-id="<?= $item['latest_progress']['id'] ?>">
                                        <i class="ki-outline ki-send fs-6 me-2"></i>
                                        ส่งรายงาน
                                    </a>
                                </div>
                                <?php endif; ?>

                            <?php else: ?>
                                <!-- แสดงข้อความสำหรับ CoWorking -->
                                <?php if ($item['key_result_role'] === 'CoWorking'): ?>
                                <div class="menu-item px-3">
                                    <div class="text-muted fs-8 px-3 py-2">
                                        <i class="ki-outline ki-information-5 fs-7 me-1"></i>
                                        เฉพาะ Leader เท่านั้นที่รายงานได้
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Approval Actions (เฉพาะ Leader + Approver/Admin) -->
                            <?php if ($item['can_approve']): ?>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-3">
                                <div class="text-muted fs-8 px-3 py-2">
                                    <i class="ki-outline ki-shield-tick fs-7 me-1 text-success"></i>
                                    การอนุมัติ
                                </div>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 text-success approve-report-btn" data-progress-id="<?= $item['latest_progress']['id'] ?>">
                                    <i class="ki-outline ki-check fs-6 me-2"></i>
                                    อนุมัติรายงาน
                                </a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 text-danger reject-report-btn" data-progress-id="<?= $item['latest_progress']['id'] ?>">
                                    <i class="ki-outline ki-cross fs-6 me-2"></i>
                                    ปฏิเสธรายงาน
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>