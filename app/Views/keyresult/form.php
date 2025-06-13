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