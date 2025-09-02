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
    <!--begin::Aside column-->
    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">

        <!--begin::ข้อมูลรายการ-->
            <div class="card card-flush py-4">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>ข้อมูลรายการ</h2>
                    </div>
                    <!--end::Card title-->
                    <!--begin::Card toolbar-->
                    <div class="card-toolbar">
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
                        <div class="rounded-circle bg-<?= $statusColor ?> w-15px h-15px"></div>
                    </div>
                    <!--end::Card toolbar-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0">
                    <!--begin::Status-->
                    <div class="mb-4">
                        <label class="form-label text-muted">สถานะ:</label>
                        <div>
                            <div class="badge badge-light-<?= $statusColor ?> fs-7 fw-bold"><?= $statusText ?></div>
                        </div>
                    </div>
                    <!--end::Status-->

                    <!--begin::Created Date-->
                    <div class="mb-4">
                        <label class="form-label text-muted">สร้างเมื่อ:</label>
                        <div class="fw-bold text-gray-800">
                            <?= date('d/m/Y H:i:s', strtotime($entry['created_date'])) ?>
                        </div>
                    </div>
                    <!--end::Created Date-->

                    <!--begin::Creator-->
                    <div class="mb-3">
                        <label class="form-label text-muted">สร้างโดย:</label>
                        <div class="d-flex align-items-center">
                            <div class="fw-bold text-gray-800">
                                <?= esc($entry['creator_name'] ?? 'ไม่ระบุผู้สร้าง') ?>
                            </div>
                        </div>
                    </div>
                    <!--end::Creator-->
                </div>
                <!--end::Card body-->
            </div>
        <!--end::ข้อมูลรายการ-->

        <!--begin::Actions-->
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2>การจัดการ</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex flex-column gap-2">
                    <a href="<?= base_url('keyresult/edit-entry/' . $entry['id']) ?>" class="btn btn-primary btn-sm">
                        <i class="ki-outline ki-pencil fs-3"></i>
                        แก้ไขรายการ
                    </a>
                    <a href="<?= base_url('keyresult/view/' . $entry['key_result_id']) ?>" class="btn btn-secondary btn-sm">
                        <i class="ki-outline ki-arrow-left fs-3"></i>
                        กลับหน้า Key Result
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" id="delete-entry-btn" data-entry-id="<?= $entry['id'] ?>">
                        <i class="ki-outline ki-trash fs-3"></i>
                        ลบรายการ
                    </button>
                </div>
            </div>
        </div>
        <!--end::Actions-->

    </div>
    <!--end::Aside column-->

    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">

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
            <div class="card-body pt-0">
                <!--begin::ชื่อรายการ-->
                <div class="mb-10">
                    <label class="form-label fw-bold">ชื่อรายการข้อมูล</label>
                    <div class="fs-4 fw-bold text-gray-800"><?= esc($entry['entry_name']) ?></div>
                </div>
                <!--end::ชื่อรายการ-->

                <!--begin::รายละเอียด-->
                <div class="mb-5">
                    <label class="form-label fw-bold">รายละเอียด</label>
                    <div class="bg-light-primary p-4 rounded border-dashed border-primary">
                        <?php if (!empty($entry['entry_description'])): ?>
                            <div class="text-gray-800"><?= nl2br(esc($entry['entry_description'])) ?></div>
                        <?php else: ?>
                            <div class="text-muted fst-italic">ไม่มีรายละเอียด</div>
                        <?php endif; ?>
                    </div>
                </div>
                <!--end::รายละเอียด-->

                <!--begin::คำสำคัญ-->
                <div class="mb-5">
                    <label class="form-label fw-bold">คำสำคัญ</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (!empty($tags)): ?>
                            <?php foreach ($tags as $tag): ?>
                                <span class="badge badge-light-info fs-7"><?= esc($tag) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted fst-italic">ไม่มีคำสำคัญ</span>
                        <?php endif; ?>
                    </div>
                </div>
                <!--end::คำสำคัญ-->

            </div>
            <!--end::Card body-->
        </div>
        <!--end::รายการข้อมูล-->

        <!--begin::ไฟล์แนบ-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>ไฟล์แนบ</h2>
                </div>
                <div class="card-toolbar">
                    <span class="badge badge-light-primary"><?= count($files) ?> ไฟล์</span>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <?php if (!empty($files)): ?>
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px">ชื่อไฟล์</th>
                                    <th class="min-w-100px">วันที่อัปโหลด</th>
                                    <th class="min-w-100px text-end">การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($files as $file): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-5">
                                                    <div class="symbol-label bg-light-primary">
                                                        <?php
                                                        $extension = pathinfo($file['file_name'], PATHINFO_EXTENSION);
                                                        $iconClass = match(strtolower($extension)) {
                                                            'pdf' => 'ki-outline ki-file text-danger',
                                                            'doc', 'docx' => 'ki-outline ki-document text-primary',
                                                            'xls', 'xlsx' => 'ki-outline ki-some-files text-success',
                                                            'jpg', 'jpeg', 'png', 'gif' => 'ki-outline ki-picture text-warning',
                                                            default => 'ki-outline ki-file text-muted'
                                                        };
                                                        ?>
                                                        <i class="<?= $iconClass ?> fs-2x"></i>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold text-hover-primary mb-1">
                                                        <?= esc($file['original_name'] ?? $file['file_name']) ?>
                                                    </span>
                                                    <?php if (!empty($file['original_name']) && $file['original_name'] !== $file['file_name']): ?>
                                                        <span class="text-muted fs-7">ชื่อไฟล์ในระบบ: <?= esc($file['file_name']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fw-semibold d-block fs-7">
                                                <?= date('d/m/Y', strtotime($file['uploaded_date'])) ?>
                                            </span>
                                            <span class="text-muted fw-semibold d-block fs-8">
                                                <?= date('H:i:s', strtotime($file['uploaded_date'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= base_url($file['file_path']) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary"
                                               data-bs-toggle="tooltip"
                                               title="ดาวน์โหลดไฟล์">
                                                <i class="ki-outline ki-down-2 fs-3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-10">
                        <div class="mb-5">
                            <i class="ki-outline ki-file-deleted text-muted fs-5x"></i>
                        </div>
                        <div class="text-muted fs-5 fw-semibold">ไม่มีไฟล์แนบ</div>
                        <div class="text-muted fs-7">รายการนี้ยังไม่มีไฟล์แนบใด ๆ</div>
                    </div>
                <?php endif; ?>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::ไฟล์แนบ-->

    </div>
    <!--end::Main column-->
</div>
<!--end::View Entry-->