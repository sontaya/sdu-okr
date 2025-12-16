<!--begin::Breadcrumb-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <!--begin::Path-->
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('keyresult/list') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-home fs-6 me-1"></i>
            My Key Results
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <a href="<?= base_url('keyresult/view/' . $entry['key_result_id']) ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <?= isset($keyresult['key_result_name']) ? mb_substr($keyresult['key_result_name'], 0, 25) . '...' : 'View KR' ?>
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <span class="text-gray-800 fw-bold fs-7">
            <?= mb_substr($entry['entry_name'], 0, 30) ?>...
        </span>
    </div>
    <!--end::Path-->

    <!--begin::Actions-->
    <div class="d-flex align-items-center gap-2">
        <a href="<?= base_url('keyresult/view/' . $entry['key_result_id']) ?>" class="btn btn-sm btn-light btn-active-light-primary">
            <i class="ki-outline ki-arrow-left fs-5"></i>
            Back to KR
        </a>
        <?php if (isset($can_manage_entries) && $can_manage_entries): ?>
            <a href="<?= base_url('keyresult/edit-entry/' . $entry['id']) ?>" class="btn btn-sm btn-primary">
                <i class="ki-outline ki-pencil fs-5"></i>
                แก้ไขรายการ
            </a>
            <button type="button" class="btn btn-sm btn-danger" id="delete-entry-btn" data-entry-id="<?= $entry['id'] ?>">
                <i class="ki-outline ki-trash fs-5"></i>
                ลบรายการ
            </button>
        <?php endif; ?>
    </div>
    <!--end::Actions-->
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
                                    <?= esc($keyresult['og_name'] ?? 'Goal Name') ?>
                                </span>
                            </div>
                            <!--end::Goal Badge-->

                            <!--begin::Objective -->
                            <div class="fs-7 fw-semibold obj-color-<?= $og_id ?> mb-2">
                                Obj: <?= esc($keyresult['objective_name'] ?? 'Objective Name') ?>
                            </div>
                            <!--end::Objective-->

                            <!--begin::Title-->
                            <h1 class="text-gray-600 text-hover-goal-<?= $og_id ?> fs-7 fw-bold mb-2">
                            KR: <?= esc($keyresult['key_result_name'] ?? 'Key Result Name') ?>
                            </h1>
                            <!--end::Title-->

                            <!--begin::Form Action Indicator-->
                            <div class="d-flex align-items-center">
                                <i class="ki-outline <?= isset($is_edit) && $is_edit ? 'ki-pencil' : 'ki-plus' ?> fs-6 text-primary me-2"></i>
                                <span class="text-primary fw-semibold fs-7">
                                    <?= isset($is_edit) && $is_edit ? 'แก้ไขรายการข้อมูล' : 'เพิ่มรายการข้อมูลใหม่' ?>
                                </span>
                            </div>
                            <!--end::Form Action Indicator-->
                        </div>
                        <!--end::Details-->
                    </div>
                    <!--end::KR Heading-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Details-->
        </div>
    </div>
<!--end::KR Header Card-->


<!--begin::View Entry-->
<div class="d-flex flex-column flex-lg-row">

<!--begin::View Entry-->
<div class="d-flex flex-column">
    <!--begin::Entry Meta Info Bar-->
    <div class="card card-flush mb-6">
        <div class="card-body py-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                <!--begin::Status & Date Info-->
                <div class="d-flex flex-wrap align-items-center gap-6">
                    <!--begin::Status-->
                    <?php
                    $status = $entry['entry_status'] ?? 'draft';
                    $statusColor = [
                        'published' => 'success',
                        'draft' => 'warning',
                        'inactive' => 'danger'
                    ][$status] ?? 'secondary';
                    $statusText = [
                        'published' => 'เผยแพร่แล้ว',
                        'draft' => 'ร่าง',
                        'inactive' => 'ไม่ใช้งาน'
                    ][$status] ?? 'ไม่ระบุ';
                    ?>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-<?= $statusColor ?> w-15px h-15px me-2"></div>
                        <span class="badge badge-light-<?= $statusColor ?> fs-7 fw-bold"><?= $statusText ?></span>
                    </div>
                    <!--end::Status-->

                    <!--begin::Created Date-->
                    <div class="d-flex align-items-center text-muted">
                        <i class="ki-outline ki-calendar fs-5 me-2"></i>
                        <div>
                            <span class="fw-semibold text-gray-800"><?= date('d/m/Y H:i:s', strtotime($entry['created_date'])) ?></span>
                        </div>
                    </div>
                    <!--end::Created Date-->

                    <!--begin::Creator-->
                    <div class="d-flex align-items-center text-muted">
                        <i class="ki-outline ki-user fs-5 me-2"></i>
                        <span class="fw-semibold text-gray-800"><?= esc($entry['creator_name'] ?? 'ไม่ระบุผู้สร้าง') ?></span>
                    </div>
                    <!--end::Creator-->
                </div>
                <!--end::Status & Date Info-->

                <!--begin::File Count (if any)-->
                <?php if (!empty($files)): ?>
                <div class="d-flex align-items-center text-muted">
                    <i class="ki-outline ki-file fs-5 text-primary me-2"></i>
                    <span class="fw-bold text-primary"><?= count($files) ?> ไฟล์</span>
                </div>
                <?php endif; ?>
                <!--end::File Count-->
            </div>
        </div>
    </div>
    <!--end::Entry Meta Info Bar-->

    <!--begin::Main column-->
    <div class="d-flex flex-column gap-6">

        <!--begin::รายการข้อมูล-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>รายการข้อมูล</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <!--begin::Card body-->
            <div class="card-body pt-0">

                <div class="d-flex flex-column gap-5">
                    <!--begin::ชื่อรายการ-->
                    <div>
                        <h3 class="fw-bold text-gray-900 mb-2"><?= esc($entry['entry_name']) ?></h3>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                             <?php if (!empty($tags)): ?>
                                <?php foreach ($tags as $tag): ?>
                                    <span class="badge badge-light-info fs-7 fw-bold">
                                        <i class="ki-outline ki-tag fs-6 me-1"></i>
                                        <?= esc($tag) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!--end::ชื่อรายการ-->

                    <div class="separator"></div>

                    <!--begin::รายละเอียด-->
                    <div>
                        <h4 class="fw-bold text-gray-800 mb-3">รายละเอียด</h4>
                        <!-- ✅ แสดง HTML โดยตรง (trust internal WYSIWYG) -->
                        <div class="fs-6 text-gray-700 lh-lg">
                            <?php if (!empty($entry['entry_description'])): ?>
                                <?= $entry['entry_description'] ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">ไม่มีรายละเอียด</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!--end::รายละเอียด-->
                </div>

            </div>
            <!--end::Card body-->
        </div>
        <!--end::รายการข้อมูล-->

        <!--begin::ไฟล์แนบ-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h4><i class="ki-outline ki-file-added fs-2 me-2 text-primary"></i> ไฟล์แนบ (<?= count($files) ?>)</h4>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <?php if (!empty($files)): ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-3">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-200px">ชื่อไฟล์</th>
                                    <th class="min-w-100px">วันที่อัปโหลด</th>
                                    <th class="min-w-50px text-end">ดาวน์โหลด</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <!-- Icon -->
                                                <div class="symbol symbol-35px me-3">
                                                    <span class="symbol-label bg-light-primary">
                                                        <?php
                                                        $extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                                        $iconClass = match(strtolower($extension)) {
                                                            'pdf' => 'ki-outline ki-file-pdf text-danger',
                                                            'doc', 'docx' => 'ki-outline ki-file-word text-primary',
                                                            'xls', 'xlsx' => 'ki-outline ki-file-excel text-success',
                                                            'jpg', 'jpeg', 'png', 'gif' => 'ki-outline ki-picture text-warning',
                                                            default => 'ki-outline ki-file text-muted'
                                                        };
                                                        ?>
                                                        <i class="<?= $iconClass ?> fs-2"></i>
                                                    </span>
                                                </div>
                                                <!-- Details -->
                                                <div class="d-flex flex-column">
                                                    <a href="<?= base_url($file['file_path']) ?>" target="_blank" class="text-gray-800 text-hover-primary mb-1">
                                                        <?= esc($file['original_name'] ?? $file['file_name']) ?>
                                                    </a>
                                                    <?php if (!empty($file['original_name']) && $file['original_name'] !== $file['file_name']): ?>
                                                        <span class="text-muted fs-8"><?= esc($file['file_name']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span><?= date('d/m/Y', strtotime($file['uploaded_date'])) ?></span>
                                                <span class="text-muted fs-8"><?= date('H:i', strtotime($file['uploaded_date'])) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= base_url($file['file_path']) ?>"
                                               download="<?= esc($file['original_name'] ?? $file['file_name']) ?>"
                                               class="btn btn-sm btn-icon btn-light-primary btn-active-primary w-30px h-30px">
                                                <i class="ki-outline ki-arrow-down fs-3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-dashed border-gray-300 d-flex flex-column flex-sm-row p-5 mb-0">
                        <i class="ki-outline ki-file fs-2hx text-muted me-4 mb-5 mb-sm-0"></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <h5 class="mb-1">ไม่มีไฟล์แนบ</h5>
                            <span class="text-muted">รายการนี้ไม่ได้แนบไฟล์ประกอบ</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::ไฟล์แนบ-->

    </div>
    <!--end::Content-->
</div>
<!--end::View Entry-->