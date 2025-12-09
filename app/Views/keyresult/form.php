<!--begin::Breadcrumb-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <!--begin::Path-->
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('keyresult/list') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-home fs-6 me-1"></i>
            My Key Results
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <a href="<?= base_url('keyresult/view/' . ($entry['key_result_id'] ?? $key_result_id ?? '')) ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            View KR
        </a>
        <i class="ki-outline ki-right fs-8 text-gray-400 mx-2"></i>
        <span class="text-gray-800 fw-bold fs-7">
            <?= isset($is_edit) && $is_edit ? 'Edit Entry' : 'Add Entry' ?>
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
                            KR: <?= esc($keyresult['key_result_template_name']) ?>
                            </h1>
                            <!--end::Title-->

                            <!--begin::Title-->
                            <h1 class="text-gray-600 text-hover-goal-<?= $og_id ?> fs-7 fw-bold mb-2">
                            KR<?= esc($keyresult['key_result_year']) ?>: <?= esc($keyresult['key_result_name'] ?? 'Key Result Name') ?>
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
            <div class="separator"></div>
            <!--begin::Nav-->
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                <!--begin::Nav item-->
                <li class="nav-item">
                    <a class="nav-link text-active-primary py-5 me-6 active" href="#">
                        <?= isset($is_edit) && $is_edit ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่' ?>
                    </a>
                </li>
                <!--end::Nav item-->
            </ul>
            <!--end::Nav-->
        </div>
    </div>
<!--end::KR Header Card-->


<!--begin::Form-->
        <form id="kt_keyresult_entries_form" class="form d-flex flex-column flex-lg-row" data-kt-redirect=""
              action="<?= isset($is_edit) && $is_edit ? base_url('keyresult/update-entry/' . $entry['id']) : base_url('keyresult/save-entry') ?>"
              method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="key_result_id" value="<?= esc($entry['key_result_id'] ?? $key_result_id ?? '') ?>">

            <!--begin::Aside column-->
            <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">

                <!--begin::สถานะรายการข้อมูล-->
                <div class="card card-flush py-4">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2>สถานะรายการข้อมูล</h2>
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <div class="rounded-circle bg-success w-15px h-15px" id="entry_status"></div>
                        </div>
                        <!--begin::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Select2-->
                        <select class="form-select mb-2" name="entry_status"  data-control="select2" data-hide-search="true" data-placeholder="Select an option" id="entry_status_select">
                            <option></option>
                            <option value="published" <?= ($entry['entry_status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= ($entry['entry_status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="inactive" <?= ($entry['entry_status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <!--end::Select2-->
                        <!--begin::Description-->
                        <div class="text-muted fs-7">กำหนดสถานะรายการข้อมูล</div>
                        <!--end::Description-->

                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::สถานะรายการข้อมูล-->

            </div>
            <!--end::Aside column-->
            <!--begin::Main column-->
            <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                <!--begin::Form -->
                        <div class="d-flex flex-column gap-7 gap-lg-10">
                            <!--begin::General options-->
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
                                    <!--begin::Input group-->
                                    <div class="mb-10 fv-row">

                                        <label class="required form-label">ชื่อรายการข้อมูล</label>
                                        <input type="text" name="entry_name" class="form-control mb-2" placeholder="" value="<?= old('entry_name', $entry['entry_name'] ?? '') ?>" />

                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-5 fv-row">
                                        <label class="form-label">รายละเอียด</label>
                                        <div id="entry_description_editor"  class="min-h-200px mb-2"></div>
                                        <textarea name="entry_description" id="entry_description" hidden><?= old('entry_description', $entry['entry_description'] ?? '') ?></textarea>
                                        <div class="text-muted fs-7">คำอธิบายรายละเอียดของข้อมูล</div>
                                    </div>
                                    <!--end::Input group-->

                                    <div>
                                        <label class="form-label d-block">คำสำคัญ</label>
                                        <input id="entry_tag" name="entry_tag" class="form-control mb-2" value="<?= isset($tags) ? htmlspecialchars(json_encode(array_map(fn($tag) => ['value' => $tag], $tags))) : '' ?>" />
                                        <div class="text-muted fs-7">คำสำคัญที่เกี่ยวข้อง</div>
                                    </div>

                                </div>
                                <!--end::Card header-->
                            </div>
                            <!--end::General options-->
                            <!--begin::Media-->
                            <div class="card card-flush py-4">
                                <!--begin::Card header-->
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>Media</h2>
                                    </div>
                                </div>
                                <!--end::Card header-->
                                <!--begin::Card body-->
                                <div class="card-body pt-0">
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-2">
                                        <!--begin::Dropzone-->
                                        <div class="dropzone" id="kt_dropzone_attachments">
                                            <!--begin::Message-->
                                            <div class="dz-message needsclick">
                                                <!--begin::Icon-->
                                                <i class="ki-outline ki-file-up text-primary fs-3x"></i>
                                                <!--end::Icon-->
                                                <!--begin::Info-->
                                                <div class="ms-4">
                                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">ลากไฟล์มาวาง หรือคลิกเพื่อเลือก</h3>
                                                    <span class="fs-7 fw-semibold text-gray-500">รองรับหลายไฟล์</span>
                                                </div>
                                                <!--end::Info-->
                                            </div>
                                        </div>
                                        <!--end::Dropzone-->
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">Set the product media gallery.</div>
                                    <!--end::Description-->

                                    <?php if (!empty($files)): ?>
                                        <div class="mt-4">
                                            <h6>ไฟล์ที่แนบไว้แล้ว:</h6>
                                            <ul class="list-group" id="existing-files-list">
                                                <?php foreach ($files as $file): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center" data-file-id="<?= $file['id'] ?>">
                                                        <div class="d-flex flex-column">
                                                            <a href="<?= base_url($file['file_path']) ?>" target="_blank" class="fw-bold">
                                                                <?= esc($file['original_name'] ?? $file['file_name']) ?>
                                                            </a>
                                                            <?php if (!empty($file['original_name']) && $file['original_name'] !== $file['file_name']): ?>
                                                                <small class="text-muted">ชื่อไฟล์ในระบบ: <?= esc($file['file_name']) ?></small>
                                                            <?php endif; ?>
                                                            <small class="text-muted">อัปโหลดเมื่อ: <?= date('d/m/Y H:i', strtotime($file['uploaded_date'])) ?></small>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-danger delete-file-btn" data-file-id="<?= $file['id'] ?>">ลบ</button>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                </div>
                                <!--end::Card header-->
                            </div>
                            <!--end::Media-->

                        </div>

                <!--end::Form -->

                <div class="d-flex justify-content-end">
                    <!--begin::Button-->
                    <a href="<?= base_url('keyresult/view/' . ($entry['key_result_id'] ?? $key_result_id ?? '')) ?>" class="btn btn-light me-5">Cancel</a>
                    <!--end::Button-->
                    <!--begin::Button-->
                    <button type="submit" id="kt_keyresult_entries_submit" class="btn btn-primary">
                        <span class="indicator-label"><?= isset($is_edit) && $is_edit ? 'Update Changes' : 'Save Changes' ?></span>
                        <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                    <!--end::Button-->
                </div>
            </div>
            <!--end::Main column-->
        </form>
        <!--end::Form-->

<script>
    // ✅ ส่งข้อมูล tags ในรูปแบบที่ถูกต้องสำหรับ Tagify
    const initialTags = <?= isset($tags) && !empty($tags) ? json_encode(array_map(fn($tag) => ['value' => $tag], $tags)) : '[]' ?>;
    const isEditMode = <?= isset($is_edit) && $is_edit ? 'true' : 'false' ?>;
</script>