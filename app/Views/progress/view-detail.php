<!--begin::Breadcrumb-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <!--begin::Path-->
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('keyresult/list') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-home fs-6 me-1"></i>
            My Key Results
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <a href="<?= base_url('progress/view/' . $currentProgress['key_result_id']) ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            Progress History
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <span class="text-gray-800 fw-bold fs-7">
            รายละเอียดรายงาน
        </span>
    </div>
    <!--end::Path-->
</div>
<!--end::Breadcrumb-->

<!--begin::KR Header Card -->
<div class="card mb-6 mb-xl-9">
    <div class="card-body pt-9 pb-0">
        <!--begin::Details-->
        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
            <!--begin::Image-->
            <div class="d-flex flex-center flex-shrink-0 bg-light rounded w-100px h-100px w-lg-150px h-lg-150px me-7 mb-4">
                <?php
                // ตรวจสอบ og_id และกำหนด ภาพ badge ที่เหมาะสม
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
                    <!--begin::Progress Info-->
                    <div class="text-end">
                        <div class="fs-2 fw-bold text-primary mb-1"><?= number_format($currentProgress['progress_percentage'], 1) ?>%</div>
                        <div class="fs-6 text-muted mb-2"><?= esc($currentProgress['progress_value']) ?> / <?= esc($keyresult['target_value']) ?> <?= esc($keyresult['target_unit']) ?></div>
                        <div class="badge badge-light-<?php
                            $statusColors = [
                                'draft' => 'warning',
                                'submitted' => 'info',
                                'approved' => 'success',
                                'rejected' => 'danger'
                            ];
                            echo $statusColors[$currentProgress['status']] ?? 'secondary';
                        ?> fs-7">
                            <?php
                            $statusText = [
                                'draft' => 'ฉบับร่าง',
                                'submitted' => 'ส่งรายงานแล้ว',
                                'approved' => 'อนุมัติแล้ว',
                                'rejected' => 'ปฏิเสธ'
                            ];
                            echo $statusText[$currentProgress['status']] ?? 'ไม่ระบุ';
                            ?>
                        </div>
                    </div>
                    <!--end::Progress Info-->
                </div>
                <!--end::KR Heading-->
                <!--begin::Info-->
                <div class="d-flex flex-wrap justify-content-start">


                </div>
                <!--end::Info-->
                <div class="d-flex flex-column">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <?php if (isset($departments)): ?>
                            <?php foreach ($departments as $dep): ?>
                                <?php
                                    $role = strtolower($dep['role']);
                                    $badgeClass = $role === 'leader' ? 'badge badge-primary' : 'badge badge-light-primary';
                                ?>
                                <span class="<?= $badgeClass ?>" title="<?= esc($dep['full_name']) ?>">
                                    <?= esc($dep['short_name']) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                    รายละเอียดรายงานความคืบหน้า
                </a>
            </li>
            <!--end::Nav item-->
        </ul>
        <!--end::Nav-->
    </div>
</div>
<!--end::KR Header Card-->

<!--begin::Layout-->
<div class="d-flex flex-column flex-lg-row">

    <!--begin::Content-->
    <div class="flex-lg-row-fluid ms-lg-15">
        <!--begin::Card-->
        <div class="card card-flush mb-6 mb-xl-9">
            <!--begin::Card header-->
            <div class="card-header mt-6">
                <div class="card-title flex-column">
                    <h2 class="mb-1"><?= esc($currentProgress['quarter_name']) ?> - รายละเอียดรายงานความคืบหน้า</h2>
                    <div class="fs-6 fw-semibold text-muted">เวอร์ชัน <?= $currentProgress['version'] ?> | สร้างเมื่อ <?= date('d/m/Y H:i', strtotime($currentProgress['created_date'])) ?></div>
                </div>
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('progress/view/' . $keyresult['key_result_id']) ?>"
                        class="btn btn-sm btn-light">
                            <i class="ki-outline ki-arrow-left fs-6"></i>
                            กลับไปประวัติทั้งหมด
                        </a>
                        <?php if ($currentProgress['status'] === 'draft' && $currentProgress['created_by'] == session('user_id')): ?>
                            <a href="<?= base_url('progress/form/' . $keyresult['key_result_id'] . '/' . $currentProgress['reporting_period_id'] . '/' . $currentProgress['id']) ?>"
                            class="btn btn-sm btn-warning">
                                <i class="ki-outline ki-pencil fs-6"></i>
                                แก้ไขรายงาน
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-4">
                <!--begin::Progress Description-->
                <?php if (!empty($currentProgress['progress_description'])): ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-gray-800 mb-4">รายละเอียดการดำเนินงานและความคืบหน้า</h4>
                        <div class="bg-light-primary p-6 rounded">
                            <?= $currentProgress['progress_description'] ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!--end::Progress Description-->

                <!--begin::Challenges-->
                <?php if (!empty($currentProgress['challenges'])): ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-warning mb-4">อุปสรรคและปัญหา</h4>
                        <div class="bg-light-warning p-6 rounded">
                            <?= $currentProgress['challenges'] ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!--end::Challenges-->

                <!--begin::Solutions-->
                <?php if (!empty($currentProgress['solutions'])): ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-info mb-4">แนวทางแก้ไข</h4>
                        <div class="bg-light-info p-6 rounded">
                            <?= $currentProgress['solutions'] ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!--end::Solutions-->

                <!--begin::Next Actions-->
                <?php if (!empty($currentProgress['next_actions'])): ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-primary mb-4">แผนการดำเนินงานต่อไป</h4>
                        <div class="bg-light-primary p-6 rounded">
                            <?= $currentProgress['next_actions'] ?>
                        </div>
                    </div>
                <?php endif; ?>
                <!--end::Next Actions-->

                <!--begin::Related Entries-->
                <?php if (!empty($currentProgress['entries'])): ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-gray-800 mb-4">
                            <i class="ki-outline ki-document text-info fs-3 me-2"></i>
                            รายการข้อมูลที่เกี่ยวข้อง (<?= count($currentProgress['entries']) ?> รายการ)
                        </h4>
                        <div class="row g-6">
                            <?php foreach ($currentProgress['entries'] as $entry): ?>
                                <div class="col-md-6 col-xl-4">
                                    <div class="card card-flush h-100 shadow-sm hover-elevate-up">
                                        <div class="card-header min-h-50px">
                                            <div class="card-title flex-column">
                                                <h5 class="fw-bold text-gray-800 mb-1"><?= esc($entry['entry_name']) ?></h5>
                                                <div class="fs-7 fw-semibold text-muted">
                                                    สร้างเมื่อ <?= date('d/m/Y', strtotime($entry['entry_created_date'])) ?>
                                                </div>
                                            </div>
                                            <div class="card-toolbar">
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($entry['entry_status']) {
                                                    case 'published':
                                                        $statusClass = 'badge-light-success';
                                                        $statusText = 'Published';
                                                        break;
                                                    case 'draft':
                                                        $statusClass = 'badge-light-warning';
                                                        $statusText = 'Draft';
                                                        break;
                                                    case 'inactive':
                                                        $statusClass = 'badge-light-danger';
                                                        $statusText = 'Inactive';
                                                        break;
                                                    default:
                                                        $statusClass = 'badge-light-secondary';
                                                        $statusText = 'Unknown';
                                                }
                                                ?>
                                                <span class="badge <?= $statusClass ?> fw-bold px-3 py-2"><?= $statusText ?></span>
                                            </div>
                                        </div>
                                        <div class="card-body pt-2">
                                            <?php if (!empty($entry['entry_description'])): ?>
                                                <div class="text-gray-700 fs-6 mb-3">
                                                    <?php
                                                    $description = strip_tags($entry['entry_description']);
                                                    $truncated = mb_substr($description, 0, 120);
                                                    echo esc($truncated);
                                                    if (mb_strlen($description) > 120) {
                                                        echo '<span class="text-muted">...</span>';
                                                    }
                                                    ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted fs-6 mb-3">ไม่มีรายละเอียด</div>
                                            <?php endif; ?>

                                            <!-- ✅ แสดงไฟล์แนบ -->
                                            <?php if (!empty($entry['files'])): ?>
                                                <div class="separator mb-3"></div>
                                                <div class="d-flex flex-column gap-2">
                                                    <?php foreach ($entry['files'] as $file): ?>
                                                        <div class="d-flex align-items-center bg-light rounded p-2">
                                                            <?php
                                                            $extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                                            $iconClass = match(strtolower($extension)) {
                                                                'pdf' => 'ki-file-pdf text-danger',
                                                                'doc', 'docx' => 'ki-file-word text-primary',
                                                                'xls', 'xlsx' => 'ki-file-excel text-success',
                                                                'jpg', 'jpeg', 'png' => 'ki-picture text-warning',
                                                                default => 'ki-file'
                                                            };
                                                            ?>
                                                            <i class="ki-outline <?= $iconClass ?> fs-2 me-2"></i>
                                                            <div class="d-flex flex-column flex-grow-1 overflow-hidden">
                                                                <a href="<?= base_url($file['file_path']) ?>" target="_blank"
                                                                   class="text-gray-800 text-hover-primary fs-7 fw-bold text-truncate">
                                                                    <?= esc($file['original_name'] ?? $file['file_name']) ?>
                                                                </a>
                                                            </div>
                                                            <a href="<?= base_url($file['file_path']) ?>"
                                                               download="<?= esc($file['original_name'] ?? $file['file_name']) ?>"
                                                               class="btn btn-sm btn-icon btn-light-primary w-25px h-25px ms-2"
                                                               title="ดาวน์โหลด">
                                                                <i class="ki-outline ki-arrow-down fs-4"></i>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer pt-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-outline ki-time fs-6 text-muted me-1"></i>
                                                    <span class="text-muted fs-7"><?= date('H:i น.', strtotime($entry['entry_created_date'])) ?></span>
                                                </div>
                                                <a href="<?= base_url('keyresult/view-entry/' . $entry['entry_id']) ?>"
                                                class="btn btn-sm btn-primary">
                                                    <i class="ki-outline ki-eye fs-6 me-1"></i>
                                                    ดูรายละเอียด
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-gray-800 mb-4">
                            <i class="ki-outline ki-document text-info fs-3 me-2"></i>
                            รายการข้อมูลที่เกี่ยวข้อง
                        </h4>
                        <div class="d-flex flex-column flex-center py-10">
                            <div class="mb-5">
                                <i class="ki-outline ki-document fs-3x text-muted"></i>
                            </div>
                            <div class="fs-5 fw-semibold text-muted mb-3">ไม่มีรายการข้อมูลที่เกี่ยวข้อง</div>
                            <div class="fs-7 text-gray-400">รายงานนี้ยังไม่ได้เชื่อมโยงกับรายการข้อมูลใดๆ</div>
                        </div>
                    </div>
                <?php endif; ?>
                <!--end::Related Entries-->

                <!--begin::Comments-->
                <?php if (!empty($currentProgress['comments'])): ?>
                    <div class="mb-8">
                        <h4 class="fw-bold text-gray-800 mb-4">ความคิดเห็น</h4>
                        <?php foreach ($currentProgress['comments'] as $comment): ?>
                            <div class="bg-light p-4 rounded mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?= esc($comment['commenter_role']) ?></strong>
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($comment['created_date'])) ?></small>
                                </div>
                                <div class="mt-2"><?= esc($comment['comment_text']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!--end::Comments-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content-->
</div>
<!--end::Layout-->