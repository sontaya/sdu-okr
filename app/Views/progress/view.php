<!--begin::Breadcrumb-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <!--begin::Path-->
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('keyresult/list') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-home fs-6 me-1"></i>
            My Key Results
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <span class="text-gray-800 fw-bold fs-7">
            View Progress Report
        </span>
    </div>
    <!--end::Path-->

</div>
<!--end::Breadcrumb-->

<!--begin::KR Header Card-->
        <div class="card mb-6 mb-xl-9">
            <div class="card-body pt-9 pb-0">
                <!--begin::Details-->
                <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                    <!--begin::Image-->
                    <div class="d-flex flex-center flex-shrink-0 bg-light rounded w-100px h-100px w-lg-150px h-lg-150px me-7 mb-4">
                        <?php
                        // ตรวจสอบ og_id และกำหนดภาพ badge ที่เหมาะสม
                        $og_id = isset($keyresult['og_id']) ? (int)$keyresult['og_id'] : 1;
                        // ตรวจสอบว่า og_id อยู่ในช่วง 1-5 หรือไม่
                        if ($og_id < 1 || $og_id > 5) {
                            $og_id = 1; // ใช้ค่าเริ่มต้นเป็น 1 หาก og_id ไม่อยู่ในช่วงที่กำหนด
                        }
                        $badge_image = base_url('assets/images/badge-goal' . $og_id . '.png');
                        ?>
                        <img class="mw-150px mw-lg-100px" src="<?= $badge_image ?>" alt="Goal Badge" style="object-fit: contain;" />
                    </div>
                    <!--end::Image-->
                    <!--begin::Wrapper-->
                    <div class="flex-grow-1">
                        <!--begin::KR Heading-->
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                            <!--begin::Details-->
                            <div class="d-flex flex-column">

                                <!--begin::Goal Badge-->
                                <?php $goal_class = 'goal-badge-' . $og_id; ?>
                                <div class="mb-2">
                                    <span class="badge goal-badge <?= $goal_class ?> fs-6">
                                        <?= esc($keyresult['og_name']) ?>
                                    </span>
                                </div>
                                <!--end::Goal Badge-->

                                <!--begin::Objective -->
                                <div class="d-flex flex-wrap fw-bold mb-2 fs-5 ">
                                    <span class="text-gray-800 obj-color-<?= $og_id ?>">Obj: <?= esc($keyresult['objective_name']) ?></span>
                                </div>
                                <!--end::Description-->

                                <!--begin::Title-->
                                <h1 class="text-gray-800 text-hover-primary fs-5 fw-bold mb-2">
                                   KR: <?= esc($keyresult['key_result_name']) ?>
                                </h1>
                                <!--end::Title-->
                            </div>
                            <!--end::Details-->
                        </div>
                        <!--end::KR Heading-->
                        <!--begin::Info-->
                        <div class="d-flex flex-wrap justify-content-start">


                        </div>
                        <!--end::Info-->
                        <div class="d-flex flex-column">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <?php foreach ($departments as $dep): ?>
                                    <?php
                                        $role = strtolower($dep['role']);
                                        $badgeClass = $role === 'leader' ? 'badge badge-primary' : 'badge badge-light-primary';
                                    ?>
                                    <span class="<?= $badgeClass ?>" title="<?= esc($dep['full_name']) ?>">
                                        <?= esc($dep['short_name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Details-->
                <div class="separator"></div>
                <!--begin::Nav-->
                 <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                    <!--begin::Nav item-->
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-5 me-6 active" href="#">
                            การรายงานความคืบหน้า
                        </a>
                    </li>
                    <!--end::Nav item-->
                </ul>
                <!--end::Nav-->
            </div>
        </div>
<!--end::KR Header Card-->


<!--begin::Progress History (Full Width)-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header mt-6">
        <!--begin::Card title-->
        <div class="card-title flex-column">
            <h2 class="mb-1">ประวัติการรายงานความคืบหน้า</h2>
            <div class="fs-6 fw-semibold text-muted">รายการรายงานทั้งหมดของ Key Result นี้</div>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <?php if ($can_report_progress): ?>
        <div class="card-toolbar">
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-light-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="ki-outline ki-plus fs-2"></i>
                    รายงานความคืบหน้า
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($reportingPeriods as $period): ?>
                        <li>
                            <a class="dropdown-item" href="<?= base_url('progress/form/' . $keyresult['key_result_id'] . '/' . $period['id']) ?>">
                                <div>
                                    <div class="fw-bold"><?= esc($period['quarter_name']) ?></div>
                                    <div class="text-muted fs-7">
                                        <?= date('d/m/Y', strtotime($period['start_date'])) ?> -
                                        <?= date('d/m/Y', strtotime($period['end_date'])) ?>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    <!--begin::Card body-->
    <div class="card-body p-9 pt-4">
        <!--begin::Records-->
            <!--begin::Table Container-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_progress_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-100px">รอบการรายงาน</th>
                            <th class="min-w-150px">ความก้าวหน้า</th>
                            <th class="min-w-100px">สถานะ</th>
                            <th class="min-w-100px">วันที่รายงาน</th>
                            <th class="text-end min-w-100px">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        <?php if (!empty($progressHistory)): ?>
                            <?php foreach ($progressHistory as $progress): ?>
                                <tr>
                                    <!-- Quarter Name -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <a href="<?= base_url('progress/view/' . $keyresult['key_result_id'] . '/' . $progress['id']) ?>"
                                               class="text-gray-800 text-hover-primary mb-1">
                                                <?= esc($progress['quarter_name']) ?>
                                            </a>
                                            <?php if ($progress['version'] > 1): ?>
                                                <span class="badge badge-light-info fs-9 w-fit-content">เวอร์ชัน <?= $progress['version'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Progress -->
                                    <td>
                                        <div class="d-flex flex-column w-100 me-2">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="text-gray-900 fw-bold fs-6">
                                                    <?= number_format((float)$progress['progress_percentage'], 1) ?>%
                                                </span>
                                                <span class="text-gray-500 fw-semibold fs-7">
                                                    <?= esc($progress['progress_value']) ?> / <?= esc($keyresult['target_value']) ?>
                                                </span>
                                            </div>
                                            <div class="h-6px mx-3 w-100 bg-light-success rounded">
                                                <div class="bg-success rounded h-6px" role="progressbar"
                                                     style="width: <?= $progress['progress_percentage'] ?>%"
                                                     aria-valuenow="<?= $progress['progress_percentage'] ?>"
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'draft' => 'warning',
                                            'submitted' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger'
                                        ];
                                        $color = $statusColors[$progress['status']] ?? 'secondary';

                                        $statusText = [
                                            'draft' => 'ฉบับร่าง',
                                            'submitted' => 'รออนุมัติ',
                                            'approved' => 'อนุมัติแล้ว',
                                            'rejected' => 'แก้ไขใหม่'
                                        ];
                                        ?>
                                        <div class="badge badge-light-<?= $color ?> fw-bold px-3 py-2">
                                            <?= $statusText[$progress['status']] ?? 'ไม่ระบุ' ?>
                                        </div>
                                    </td>

                                    <!-- Created Date -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold">
                                                <?= date('d/m/Y', strtotime($progress['created_date'])) ?>
                                            </span>
                                            <span class="text-muted fs-7">
                                                <?= date('H:i', strtotime($progress['created_date'])) ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary btn-flex btn-center"
                                           data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            Actions
                                            <i class="ki-outline ki-down fs-5 ms-1"></i>
                                        </a>
                                        <!--begin::Menu-->
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4"
                                             data-kt-menu="true">

                                            <!-- View -->
                                            <div class="menu-item px-3">
                                                <a href="<?= base_url('progress/view/' . $keyresult['key_result_id'] . '/' . $progress['id']) ?>"
                                                   class="menu-link px-3">
                                                    <i class="ki-outline ki-eye fs-5 me-2"></i>ดูรายละเอียด
                                                </a>
                                            </div>

                                            <!-- Edit -->
                                            <?php if (isset($progress['can_edit']) && $progress['can_edit']): ?>
                                                <div class="menu-item px-3">
                                                    <a href="<?= base_url('progress/form/' . $keyresult['key_result_id'] . '/' . $progress['reporting_period_id'] . '/' . $progress['id']) ?>"
                                                       class="menu-link px-3">
                                                        <i class="ki-outline ki-pencil fs-5 me-2"></i>แก้ไข
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Submit -->
                                            <?php if (isset($progress['can_submit']) && $progress['can_submit']): ?>
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3 submit-progress-btn" data-progress-id="<?= $progress['id'] ?>">
                                                        <i class="ki-outline ki-send fs-5 me-2"></i>ส่งรายงาน
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Approve -->
                                            <?php if (isset($progress['can_approve']) && $progress['can_approve']): ?>
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3 approve-progress-btn" data-progress-id="<?= $progress['id'] ?>">
                                                        <i class="ki-outline ki-check fs-5 me-2"></i>อนุมัติ
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Delete -->
                                            <?php if (isset($progress['can_delete']) && $progress['can_delete']): ?>
                                                <div class="menu-item px-3">
                                                    <a href="#" class="menu-link px-3 text-danger delete-progress-btn" data-progress-id="<?= $progress['id'] ?>">
                                                        <i class="ki-outline ki-trash fs-5 me-2"></i>ลบ
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <!--end::Menu-->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="mb-5">
                                        <i class="ki-outline ki-information-5 fs-3x text-muted"></i>
                                    </div>
                                    <div class="fw-semibold text-gray-500 fs-4 mb-5">ยังไม่มีการรายงานความคืบหน้า</div>
                                    <div class="text-gray-400 fs-6 mb-7">เริ่มต้นสร้างรายงานความคืบหน้าแรกของคุณ</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!--end::Table Container-->
        <!--end::Records-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Progress History-->

<script>
    const keyResultId = <?= $keyresult['key_result_id'] ?>;
</script>