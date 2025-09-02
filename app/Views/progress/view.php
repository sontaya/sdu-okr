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
        <div class="py-2">
            <!--begin::Timeline-->
            <div class="timeline-label">
                <?php if (!empty($progressHistory)): ?>
                    <?php foreach ($progressHistory as $progress): ?>
                        <!--begin::Item-->
                        <div class="timeline-item">
                            <!--begin::Label-->
                            <div class="timeline-label fw-bold text-gray-800 fs-6">
                                <?= date('d/m/Y', strtotime($progress['created_date'])) ?>
                            </div>
                            <!--end::Label-->
                            <!--begin::Badge-->
                            <div class="timeline-badge">
                                <?php
                                $statusColors = [
                                    'draft' => 'warning',
                                    'submitted' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $color = $statusColors[$progress['status']] ?? 'secondary';
                                ?>
                                <i class="ki-outline ki-abstract-8 fs-2 text-<?= $color ?>"></i>
                            </div>
                            <!--end::Badge-->
                            <!--begin::Text-->
                            <div class="fw-normal timeline-content text-muted ps-3">
                                <!--begin::Title-->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="fw-bold text-gray-800"><?= esc($progress['quarter_name']) ?></span>
                                        <span class="text-muted mx-2">|</span>
                                        <span class="badge badge-light-<?= $color ?> fs-8">
                                            <?php
                                            $statusText = [
                                                'draft' => 'ฉบับร่าง',
                                                'submitted' => 'ส่งรายงานแล้ว',
                                                'approved' => 'อนุมัติแล้ว',
                                                'rejected' => 'ปฏิเสธ'
                                            ];
                                            echo $statusText[$progress['status']] ?? 'ไม่ระบุ';
                                            ?>
                                        </span>
                                        <?php if ($progress['version'] > 1): ?>
                                            <span class="badge badge-light-info fs-8 ms-2">เวอร์ชัน <?= $progress['version'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($progress['progress_percentage']): ?>
                                            <div class="fw-bold text-success fs-4"><?= number_format($progress['progress_percentage'], 1) ?>%</div>
                                            <div class="text-muted fs-7"><?= esc($progress['progress_value']) ?> / <?= esc($keyresult['target_value']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!--end::Title-->

                            <!--begin::Progress Description-->
                                <?php if (!empty($progress['progress_description'])): ?>
                                    <div class="mb-3">
                                        <div class="fw-semibold text-gray-700 mb-1">รายละเอียดความคืบหน้า:</div>
                                        <div class="text-gray-600"><?= $progress['progress_description'] ?></div>
                                    </div>
                                <?php endif; ?>
                            <!--end::Progress Description-->

                            <!--begin::Related Entries-->
                                <?php if (!empty($progress['related_entries'])): ?>
                                    <div class="mb-3">
                                        <div class="fw-semibold text-gray-700 mb-2">รายการข้อมูลที่เกี่ยวข้อง (<?= count($progress['related_entries']) ?> รายการ):</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($progress['related_entries'] as $entry): ?>
                                                <span class="badge badge-light-info fs-8 py-1 px-2"
                                                    title="<?= esc($entry['entry_description']) ?>">
                                                    <i class="ki-outline ki-document fs-8 me-1"></i>
                                                    <?= esc($entry['entry_name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <!--end::Related Entries-->

                            <!--begin::Additional Details-->
                                <?php if (!empty($progress['challenges']) || !empty($progress['solutions']) || !empty($progress['next_actions'])): ?>
                                    <div class="mb-3">
                                        <button class="btn btn-sm btn-light-primary" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#details-<?= $progress['id'] ?>" aria-expanded="false">
                                            <i class="ki-outline ki-eye fs-6"></i>
                                            ดูรายละเอียดเพิ่มเติม
                                        </button>
                                        <div class="collapse mt-2" id="details-<?= $progress['id'] ?>">
                                            <?php if (!empty($progress['challenges'])): ?>
                                                <div class="mb-2">
                                                    <div class="fw-semibold text-warning mb-1">อุปสรรค:</div>
                                                    <div class="text-gray-600 fs-7"><?= $progress['challenges'] ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($progress['solutions'])): ?>
                                                <div class="mb-2">
                                                    <div class="fw-semibold text-info mb-1">แนวทางแก้ไข:</div>
                                                    <div class="text-gray-600 fs-7"><?= $progress['solutions'] ?></div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($progress['next_actions'])): ?>
                                                <div class="mb-2">
                                                    <div class="fw-semibold text-primary mb-1">แผนต่อไป:</div>
                                                    <div class="text-gray-600 fs-7"><?= $progress['next_actions'] ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <!--end::Additional Details-->

                                <!--begin::Actions-->
                                <div class="d-flex gap-2">
                                    <a href="<?= base_url('progress/view/' . $keyresult['key_result_id'] . '/' . $progress['id']) ?>"
                                            class="btn btn-sm btn-light-primary">
                                            <i class="ki-outline ki-eye fs-6"></i>
                                            ดูรายละเอียด
                                    </a>
                                    <?php if (isset($progress['can_edit']) && $progress['can_edit']): ?>
                                        <a href="<?= base_url('progress/form/' . $keyresult['key_result_id'] . '/' . $progress['reporting_period_id'] . '/' . $progress['id']) ?>"
                                           class="btn btn-sm btn-light-warning">
                                            <i class="ki-outline ki-pencil fs-6"></i>
                                            แก้ไข
                                        </a>
                                    <?php endif; ?>
                                    <?php if (isset($progress['can_submit']) && $progress['can_submit']): ?>
                                        <button type="button" class="btn btn-sm btn-light-info submit-progress-btn"
                                                data-progress-id="<?= $progress['id'] ?>">
                                            <i class="ki-outline ki-send fs-6"></i>
                                            ส่งรายงาน
                                        </button>
                                    <?php endif; ?>
                                    <?php if (isset($progress['can_approve']) && $progress['can_approve']): ?>
                                        <button type="button" class="btn btn-sm btn-light-success approve-progress-btn"
                                                data-progress-id="<?= $progress['id'] ?>">
                                            <i class="ki-outline ki-check fs-6"></i>
                                            อนุมัติ
                                        </button>
                                    <?php endif; ?>
                                    <?php if (isset($progress['can_delete']) && $progress['can_delete']): ?>
                                        <button type="button" class="btn btn-sm btn-light-danger delete-progress-btn"
                                                data-progress-id="<?= $progress['id'] ?>">
                                            <i class="ki-outline ki-trash fs-6"></i>
                                            ลบ
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Text-->
                        </div>
                        <!--end::Item-->
                    <?php endforeach; ?>
                <?php else: ?>
                    <!--begin::Empty State-->
                    <div class="text-center py-10">
                        <div class="mb-7">
                            <i class="ki-outline ki-information-5 fs-3x text-muted"></i>
                        </div>
                        <div class="fw-semibold text-gray-500 fs-4 mb-5">ยังไม่มีการรายงานความคืบหน้า</div>
                        <div class="text-gray-400 fs-6 mb-7">เริ่มต้นสร้างรายงานความคืบหน้าแรกของคุณ</div>
                        <?php if ($can_report_progress): ?>
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="ki-outline ki-plus fs-2"></i>
                                    สร้างรายงานความคืบหน้า
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
                        <?php endif; ?>
                    </div>
                    <!--end::Empty State-->
                <?php endif; ?>
            </div>
            <!--end::Timeline-->
        </div>
        <!--end::Records-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Progress History-->

<script>
    const keyResultId = <?= $keyresult['key_result_id'] ?>;
</script>