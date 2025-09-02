<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" data-kt-progress-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="ค้นหา Key Result" />
            </div>
            <!--end::Search-->
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
            <!--begin::Filter-->
            <div class="w-150px">
                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="สถานะ" data-kt-progress-filter="status">
                    <option></option>
                    <option value="all">ทั้งหมด</option>
                    <option value="draft">ฉบับร่าง</option>
                    <option value="submitted">ส่งรายงานแล้ว</option>
                    <option value="approved">อนุมัติแล้ว</option>
                    <option value="rejected">ปฏิเสธ</option>
                </select>
            </div>
            <!--end::Filter-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_progress_table">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_progress_table .form-check-input" value="1" />
                        </div>
                    </th>
                    <th class="min-w-250px">Key Result</th>
                    <th class="min-w-150px">ความคืบหน้าล่าสุด</th>
                    <th class="min-w-100px">สถานะรายงาน</th>
                    <th class="min-w-100px">รอบการรายงาน</th>
                    <th class="text-end min-w-70px">การดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                <?php foreach ($keyresults as $item): ?>
                    <tr>
                        <td>
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="<?= $item['key_result_id'] ?>" />
                            </div>
                        </td>
                        <td>
                            <div class="d-flex">
                                <!--begin::Thumbnail-->
                                <a href="#" class="symbol symbol-50px">
                                    <?php
                                    $og_id = isset($item['og_id']) ? (int)$item['og_id'] : 1;
                                    if ($og_id < 1 || $og_id > 5) {
                                        $og_id = 1;
                                    }
                                    $badge_image = base_url('assets/images/badge-goal' . $og_id . '.png');
                                    ?>
                                    <span class="symbol-label" style="background-image:url(<?= $badge_image ?>); background-size: contain; background-repeat: no-repeat; background-position: center;"></span>
                                </a>
                                <!--end::Thumbnail-->
                                <div class="ms-5">
                                    <!--begin::Goal Badge-->
                                    <?php
                                    $goal_colors = [
                                        1 => '#F4B400', // Goal 1 - Yellow
                                        2 => '#2196F3', // Goal 2 - Blue
                                        3 => '#D32F2F', // Goal 3 - Red
                                        4 => '#388E3C', // Goal 4 - Green
                                        5 => '#7B1FA2'  // Goal 5 - Purple
                                    ];
                                    $goal_color = isset($goal_colors[$og_id]) ? $goal_colors[$og_id] : $goal_colors[1];
                                    ?>
                                    <div class="mb-2">
                                        <span class="badge fw-bold px-3 py-2 fs-7" style="background-color: <?= $goal_color ?>; color: white; border-radius: 20px;">
                                            <?= esc($item['og_name']) ?>
                                        </span>
                                    </div>
                                    <!--end::Goal Badge-->
                                    <!--begin::Title-->
                                    <div class="mb-1">
                                        <a href="<?= base_url('progress/view/' . $item['key_result_id']) ?>" class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            <?= esc($item['key_result_name']) ?>
                                        </a>
                                    </div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7 fw-semibold">
                                        <span class="text-gray-600"><?= esc($item['objective_name']) ?></span>
                                        <span class="text-gray-400 mx-1">|</span>
                                        <span class="text-gray-500">เป้าหมาย: <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?></span>
                                    </div>
                                    <!--end::Description-->
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($item['latest_progress'])): ?>
                                <div class="d-flex flex-column">
                                    <div class="badge badge-light-success fs-base mb-1">
                                        <?= number_format($item['latest_progress']['progress_percentage'], 1) ?>%
                                    </div>
                                    <div class="text-muted fs-7">
                                        <?= esc($item['latest_progress']['progress_value']) ?> / <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="badge badge-light-secondary fs-base">
                                    ยังไม่มีรายงาน
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $status = $item['latest_progress']['status'] ?? 'no_report';
                            $statusConfig = [
                                'draft' => ['badge' => 'badge-light-warning', 'text' => 'ฉบับร่าง'],
                                'submitted' => ['badge' => 'badge-light-info', 'text' => 'ส่งรายงานแล้ว'],
                                'approved' => ['badge' => 'badge-light-success', 'text' => 'อนุมัติแล้ว'],
                                'rejected' => ['badge' => 'badge-light-danger', 'text' => 'ปฏิเสธ'],
                                'no_report' => ['badge' => 'badge-light-secondary', 'text' => 'ยังไม่มีรายงาน']
                            ];
                            $config = $statusConfig[$status] ?? $statusConfig['no_report'];
                            ?>
                            <div class="badge <?= $config['badge'] ?> fs-base">
                                <?= $config['text'] ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($item['latest_progress'])): ?>
                                <div class="text-gray-800 fw-bold">
                                    <?= esc($item['latest_progress']['quarter_name']) ?>
                                </div>
                                <div class="text-muted fs-7">
                                    ปี <?= esc($item['latest_progress']['year']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="#" class="btn btn-sm btn-light btn-active-light-primary btn-flex btn-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                การดำเนินการ
                                <i class="ki-outline ki-down fs-5 ms-1"></i>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="<?= base_url('progress/view/' . $item['key_result_id']) ?>" class="menu-link px-3">
                                        <i class="ki-outline ki-eye fs-6 me-2"></i>
                                        ดูรายละเอียด
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="<?= base_url('progress/form/' . $item['key_result_id']) ?>" class="menu-link px-3">
                                        <i class="ki-outline ki-plus fs-6 me-2"></i>
                                        รายงานความคืบหน้า
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <?php if (!empty($item['latest_progress']) && $item['latest_progress']['status'] === 'draft'): ?>
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="<?= base_url('progress/form/' . $item['key_result_id'] . '?edit=' . $item['latest_progress']['id']) ?>" class="menu-link px-3">
                                        <i class="ki-outline ki-pencil fs-6 me-2"></i>
                                        แก้ไขรายงาน
                                    </a>
                                </div>
                                <!--end::Menu item-->
                                <?php endif; ?>
                            </div>
                            <!--end::Menu-->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>