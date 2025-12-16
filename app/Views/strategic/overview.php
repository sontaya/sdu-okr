<!-- Strategic Overview Template: app/Views/strategic/overview.php -->

<!--begin::Breadcrumb-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('strategic') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-chart-simple fs-6 me-1"></i>
            Strategic Overview
        </a>
        <span class="text-muted mx-2">•</span>
        <span class="text-primary fw-bold fs-7">มหาวิทยาลัยสวนดุสิต</span>
    </div>

    <!-- Export & Actions -->
    <div class="d-flex gap-2">
        <?php if ($strategic_permissions['can_export_data']): ?>
        <button type="button" class="btn btn-sm btn-light-primary" id="btn-export-excel">
            <i class="ki-outline ki-file-down fs-6 me-1"></i>
            Export Excel
        </button>
        <?php endif; ?>

        <button type="button" class="btn btn-sm btn-light-success" id="btn-refresh-data">
            <i class="ki-outline ki-arrows-circle fs-6 me-1"></i>
            Refresh
        </button>
    </div>
</div>
<!--end::Breadcrumb-->

<!-- Enhanced Summary Cards -->
<div class="row g-6 g-xl-9 mb-6">
    <!-- Total Key Results -->
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
                        <div class="fs-1 fw-bold text-primary"><?= $stats['total_key_results'] ?></div>
                        <div class="fs-7 text-muted">Total Key Results</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Progress -->
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-success">
                            <i class="ki-outline ki-chart-line fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-success"><?= $stats['progress_summary']['avg_progress'] ?>%</div>
                        <div class="fs-7 text-muted">ความคืบหน้าเฉลี่ย</div>
                        <div class="fs-8 text-muted">
                            <?= $stats['progress_summary']['total_with_progress'] ?> จาก <?= $stats['total_key_results'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- On Track vs At Risk -->
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-info">
                            <i class="ki-outline ki-rocket fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-info"><?= $stats['progress_summary']['on_track'] ?></div>
                        <div class="fs-7 text-muted">On Track (≥75%)</div>
                        <div class="fs-8 text-warning">
                            At Risk: <?= $stats['progress_summary']['at_risk'] ?> |
                            Behind: <?= $stats['progress_summary']['behind'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-warning">
                            <i class="ki-outline ki-time fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-warning"><?= $stats['reporting_activity']['last_7_days'] ?></div>
                        <div class="fs-7 text-muted">รายงานใน 7 วันที่ผ่านมา</div>
                        <div class="fs-8 text-muted">
                            เดือนที่แล้ว: <?= $stats['reporting_activity']['last_30_days'] ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Strategic Filters -->
<div class="card card-flush mb-6">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <h3 class="fw-bold m-0">
                <i class="ki-outline ki-filter fs-3 me-2"></i>
                Strategic Filters
            </h3>
        </div>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary" id="btn-clear-filters">
                <i class="ki-outline ki-cross fs-6 me-1"></i>
                Clear All
            </button>
        </div>
    </div>

    <div class="card-body pt-0">
        <form id="strategic-filters-form" method="GET">
            <div class="row g-3">
                <!-- Year -->
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fs-7 fw-bold">ปีงบประมาณ</label>
                    <select name="year" class="form-select form-select-sm" data-control="select2" data-hide-search="true">
                        <?php foreach ($filter_options['years'] as $year): ?>
                        <option value="<?= $year ?>" <?= $filters['year'] == $year ? 'selected' : '' ?>><?= $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Objective Group -->
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fs-7 fw-bold">Strategic Goal</label>
                    <select name="group" class="form-select form-select-sm" data-control="select2" data-placeholder="ทั้งหมด">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($filter_options['objective_groups'] as $group): ?>
                        <option value="<?= $group['id'] ?>" <?= $filters['objective_group_id'] == $group['id'] ? 'selected' : '' ?>>
                            <?= esc($group['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Department -->
                <div class="col-md-6 col-lg-4">
                    <label class="form-label fs-7 fw-bold">หน่วยงาน</label>
                    <select name="dept" class="form-select form-select-sm" data-control="select2" data-placeholder="ทั้งหมด">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($filter_options['departments'] as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $filters['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                            <?= esc($dept['short_name']) ?> | <?= esc($dept['name']) ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Progress Status -->
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fs-7 fw-bold">สถานะรายงาน</label>
                    <select name="status" class="form-select form-select-sm" data-control="select2" data-placeholder="ทั้งหมด">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($filter_options['status_options'] as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $filters['progress_status'] == $key ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Role -->
                <div class="col-md-6 col-lg-2">
                    <label class="form-label fs-7 fw-bold">บทบาท</label>
                    <select name="role" class="form-select form-select-sm" data-control="select2" data-placeholder="ทั้งหมด">
                        <option value="">ทั้งหมด</option>
                        <?php foreach ($filter_options['role_options'] as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $filters['role_type'] == $key ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="col-md-6 col-lg-6">
                    <label class="form-label fs-7 fw-bold">ค้นหา</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="คำค้น..." value="<?= esc($filters['keyword']) ?>" />
                        <button class="btn btn-primary" type="submit">
                            <i class="ki-outline ki-magnifier fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Quick Stats by Category -->
<div class="row g-6 mb-6">
    <!-- By Objective Group -->
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="card-title fw-bold">By Strategic Goals</h3>
            </div>
            <div class="card-body pt-0">
                <?php foreach ($stats['by_objective_group'] as $groupName => $count): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-7"><?= esc($groupName) ?></div>
                        <div class="progress h-6px mt-1">
                            <?php
                            $percentage = ($count / $stats['total_key_results']) * 100;
                            $progressClass = $percentage >= 30 ? 'bg-success' : ($percentage >= 15 ? 'bg-warning' : 'bg-info');
                            ?>
                            <div class="progress-bar <?= $progressClass ?>" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <div class="text-end ms-3">
                        <span class="badge badge-light-primary"><?= $count ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- By Status -->
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h3 class="card-title fw-bold">By Status</h3>
            </div>
            <div class="card-body pt-0">
                <?php
                $statusColors = [
                    'no_report' => 'secondary',
                    'draft' => 'warning',
                    'submitted' => 'info',
                    'approved' => 'success',
                    'rejected' => 'danger'
                ];
                ?>
                <?php foreach ($stats['by_status'] as $status => $count): ?>
                <?php if ($count > 0): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-grow-1">
                        <div class="fw-semibold fs-7"><?= $filter_options['status_options'][$status] ?? ucfirst($status) ?></div>
                        <div class="progress h-6px mt-1">
                            <?php
                            $percentage = ($count / $stats['total_key_results']) * 100;
                            $color = $statusColors[$status] ?? 'primary';
                            ?>
                            <div class="progress-bar bg-<?= $color ?>" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <div class="text-end ms-3">
                        <span class="badge badge-light-<?= $color ?>"><?= $count ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Data Table -->
<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="kt-strategic-search" class="form-control form-control-solid w-300px ps-12" placeholder="ค้นหาข้อมูลในตาราง..." />
            </div>
        </div>

        <div class="card-toolbar">
            <div class="d-flex gap-2">
                <!-- View Toggle -->
                <div class="btn-group" data-kt-buttons="true">
                    <label class="btn btn-sm btn-light-primary active">
                        <input class="btn-check" type="radio" name="view_mode" value="table" checked="checked" />
                        <i class="ki-outline ki-row-horizontal fs-6"></i>
                        Table
                    </label>
                    <label class="btn btn-sm btn-light-primary">
                        <input class="btn-check" type="radio" name="view_mode" value="cards" />
                        <i class="ki-outline ki-category fs-6"></i>
                        Cards
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body pt-0">
        <div id="strategic-table-view">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_strategic_table">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_strategic_table .form-check-input" value="1" />
                            </div>
                        </th>
                        <th class="min-w-350px">Key Result</th>
                        <th class="min-w-120px">ความคืบหน้า</th>
                        <th class="min-w-100px">สถานะ</th>
                        <th class="min-w-120px">รอบรายงาน</th>
                        <th class="min-w-100px">อัพเดทล่าสุด</th>
                        <th class="text-end min-w-100px">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    <?php foreach ($keyresults as $item): ?>
                    <tr data-dept="<?= !empty($item['departments']) ? $item['departments'][0]['department_id'] : '' ?>" data-group="<?= esc($item['og_id']) ?>" data-status="<?= esc($item['progress_status']) ?>">
                        <td>
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="<?= $item['key_result_id'] ?>" />
                            </div>
                        </td>

                        <!-- Key Result Info -->
                        <td>
                            <div class="d-flex">
                                <!-- Thumbnail & Role (เหมือนเดิม) -->
                                <div class="d-flex flex-column align-items-center me-4">
                                    <div class="symbol symbol-45px mb-2">
                                        <?php
                                        $og_id = (int)$item['og_id'];
                                        if ($og_id < 1 || $og_id > 5) $og_id = 1;
                                        $badge_image = base_url('assets/images/badge-goal' . $og_id . '.png');
                                        ?>
                                        <span class="symbol-label" style="background-image:url(<?= $badge_image ?>); background-size: contain; background-repeat: no-repeat; background-position: center;"></span>
                                    </div>

                                    <?php
                                    $roleClass = $item['key_result_dep_role'] === 'Leader' ? 'badge-primary' : 'badge-info';
                                    $roleIcon = $item['key_result_dep_role'] === 'Leader' ? 'ki-crown' : 'ki-people';
                                    ?>
                                    <div class="badge <?= $roleClass ?> fs-8 px-2 py-1">
                                        <i class="ki-outline <?= $roleIcon ?> fs-7 me-1"></i>
                                        <?= esc($item['key_result_dep_role']) ?>
                                    </div>
                                </div>

                                <div class="flex-grow-1">
                                    <!-- Strategic Goal Badge (เหมือนเดิม) -->
                                    <div class="mb-2">
                                        <span class="badge goal-badge goal-badge-<?= $og_id ?> fs-7">
                                            <?= esc($item['og_name']) ?>
                                        </span>
                                    </div>

                                    <!-- Objective (เหมือนเดิม) -->
                                    <div class="fs-7 fw-bold obj-color-<?= $og_id ?> mb-1">
                                        Obj: <?= esc($item['objective_name']) ?>
                                    </div>

                                    <!-- Key Results -->
                                    <div class="mb-1">
                                        <span class="text-gray-600 fs-7 fw-semibold">
                                            KR: <?= esc($item['key_result_template_name']) ?>
                                        </span>
                                    </div>

                                    <!-- Key Result Name (เหมือนเดิม) -->
                                    <div class="mb-1">
                                        <span class="text-gray-600 fs-7 fw-semibold">
                                            KR<?= esc($item['key_result_year']) ?>: <?= esc($item['key_result_name']) ?>
                                        </span>
                                    </div>

                                    <!-- Target (เหมือนเดิม) -->
                                    <div class="text-muted fs-8 mb-2">
                                        <span>เป้าหมาย: <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?></span>
                                        <span class="text-gray-400 mx-1">|</span>
                                        <span class="text-success">
                                            <i class="ki-outline ki-document fs-7 me-1"></i>
                                            <?= $item['published_entries_count'] ?? 0 ?> รายการ
                                        </span>
                                    </div>

                                    <!--  Departments Badges -->
                                    <div class="d-flex flex-column">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <?php


                                            if (!empty($item['departments'])):
                                                foreach ($item['departments'] as $dept):
                                                    $role = strtolower($dept['role']);
                                                    $badgeClass = '';

                                                    if ($role === 'leader') {
                                                        $badgeClass = 'badge badge-primary';
                                                    } else {
                                                        // CoWorking
                                                        if (isset($dept['entry_count']) && $dept['entry_count'] > 0) {
                                                            $badgeClass = 'badge badge-light-primary';
                                                        } else {
                                                            $badgeClass = 'badge badge-light-secondary';
                                                        }
                                                    }

                                            ?>
                                                <span class="<?= $badgeClass ?>" title="<?= esc($dept['full_name']) ?> (<?= $dept['role'] ?>)">
                                                    <?= esc($dept['short_name']) ?>
                                                </span>


                                            <?php
                                                endforeach;
                                            else:
                                            ?>
                                                <span class="badge badge-light-secondary">ไม่ระบุหน่วยงาน</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Progress  -->
                        <td>
                            <?php if ($item['progress_percentage'] !== null): ?>
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="progress h-8px flex-row-fluid me-2">
                                        <?php
                                        $percentage = $item['progress_percentage'];
                                        $progressClass = $percentage >= 75 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <div class="progress-bar <?= $progressClass ?>" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <span class="badge badge-light-primary fs-7"><?= number_format($percentage, 1) ?>%</span>
                                </div>
                                <div class="text-muted fs-8">
                                    <?= esc($item['progress_value']) ?> / <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?>
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

                        <!-- Column 3: Status -->
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

                        <!-- Column 4: Reporting Period -->
                        <td>
                            <div class="text-gray-800 fw-semibold fs-7">
                                <?= $item['reporting_period_text'] ?: '-' ?>
                            </div>
                            <?php if ($item['progress_creator_name']): ?>
                            <div class="text-muted fs-8">
                                โดย: <?= esc($item['progress_creator_name']) ?>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- Column 5: Last Update -->
                        <td>
                            <?php if ($item['progress_updated_date']): ?>
                            <div class="text-gray-800 fs-7"><?= date('d M Y', strtotime($item['progress_updated_date'])) ?></div>
                            <div class="text-muted fs-8"><?= date('H:i น.', strtotime($item['progress_updated_date'])) ?></div>
                            <?php if ($item['days_since_update'] !== null): ?>
                            <div class="fs-8 <?= $item['days_since_update'] > 30 ? 'text-danger' : 'text-muted' ?>">
                                <?= $item['days_since_update'] ?> วันที่แล้ว
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted fs-8">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- Column 6: Actions -->
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="<?= base_url('keyresult/view/' . $item['key_result_id']) ?>"  class="btn btn-sm btn-light-primary" target="_blank">
                                    <i class="ki-outline ki-eye fs-6"></i>
                                </a>
                                <a href="<?= base_url('progress/view/' . $item['key_result_id']) ?>" class="btn btn-sm btn-light-info" target="_blank">
                                    <i class="ki-outline ki-chart-line fs-6"></i>
                                </a>
                                <a href="<?= base_url('progress/detailed/' . $item['key_result_id']) ?>" class="btn btn-sm btn-light-warning" target="_blank">
                                    <i class="ki-outline ki-document fs-6"></i>
                                </a>
                            </div>
                        </td>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Card View (Hidden by default) -->
        <div id="strategic-cards-view" class="d-none">
            <div class="row g-6">
                <!-- Cards will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>
