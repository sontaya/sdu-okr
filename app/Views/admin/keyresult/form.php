<div class="row g-5">
    <div class="col-xl-8">
        <div class="card card-flush h-lg-100">
            <div class="card-header pt-7">
                <div class="card-title">
                    <h2><?= isset($item) ? 'แก้ไข' : 'เพิ่ม' ?> Key Result</h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <form id="kt_keyresult_form" class="form" action="#">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $item['id'] ?? '' ?>">

                    <div class="mb-5 fv-row">
                        <label class="required form-label">ปีงบประมาณ</label>
                        <select name="key_result_year" id="select_year" class="form-select form-select-solid" required>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= (isset($item['key_result_year']) && $item['key_result_year'] == $year) ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Cascading Selects -->
                    <div class="mb-5 fv-row">
                        <label class="required form-label">Objective Group</label>
                        <select id="select_group" class="form-select form-select-solid" data-control="select2" data-placeholder="เลือก Objective Group" required>
                            <option></option>
                            <?php foreach ($objective_groups as $group): ?>
                                <option value="<?= $group['id'] ?>" <?= (isset($item['objective_group_id']) && $item['objective_group_id'] == $group['id']) ? 'selected' : '' ?>>
                                    Group <?= $group['id'] ?>: <?= $group['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-5 fv-row">
                        <label class="required form-label">Objective</label>
                        <select id="select_objective" class="form-select form-select-solid" data-control="select2" data-placeholder="เลือก Objective" required <?= !isset($item['objective_group_id']) ? 'disabled' : '' ?>>
                            <option></option>
                            <!-- Populated via AJAX -->
                        </select>
                    </div>

                    <div class="mb-5 fv-row">
                        <label class="required form-label">Key Result Template</label>
                        <select name="key_result_template_id" id="select_template" class="form-select form-select-solid" data-control="select2" data-placeholder="เลือก Template" required <?= !isset($item['objective_id']) ? 'disabled' : '' ?>>
                            <option></option>
                            <!-- Populated via AJAX -->
                        </select>
                    </div>

                    <div class="separator mb-5"></div>

                    <div class="mb-5 fv-row">
                        <label class="required form-label">ลำดับ (Sequence No)</label>
                        <input type="number" name="sequence_no" class="form-control form-control-solid" placeholder="เช่น 1" value="<?= $item['sequence_no'] ?? '' ?>" required />
                    </div>

                    <div class="mb-5 fv-row">
                        <label class="required form-label">ชื่อ Key Result</label>
                        <textarea name="name" class="form-control form-control-solid" rows="3" required><?= $item['name'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-5 fv-row">
                        <label class="form-label fw-bold">เป้าหมาย (Target)</label>
                        <div class="input-group">
                            <input type="text" name="target_value" class="form-control form-control-solid" placeholder="ค่าเป้าหมาย" value="<?= $item['target_value'] ?? '' ?>" />
                            <span class="input-group-text">หน่วย</span>
                            <input type="text" name="target_unit" class="form-control form-control-solid" placeholder="หน่วยนับ" value="<?= $item['target_unit'] ?? '' ?>" />
                        </div>
                    </div>

                    <div class="separator mb-5"></div>

                    <div class="mb-5">
                        <label class="form-label fw-bold">หน่วยงานที่รับผิดชอบ</label>
                        <div id="department_list">
                            <!-- Dynamic Department Rows -->
                            <?php
                            $departments_list = $existing_departments ?? [];
                            if (empty($departments_list)):
                            ?>
                                <!-- Template Row for New Entry -->
                                <div class="row mb-2 department-item">
                                    <div class="col-7">
                                        <select name="department_ids[]" class="form-select form-select-solid" data-control="select2" data-placeholder="เลือกหน่วยงาน">
                                            <option></option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['id'] ?>"><?= $dept['short_name'] ?> - <?= $dept['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <select name="department_roles[]" class="form-select form-select-solid">
                                            <option value="Leader">Leader</option>
                                            <option value="CoWorking" selected>CoWorking</option>
                                        </select>
                                    </div>
                                    <div class="col-1">
                                        <button type="button" class="btn btn-icon btn-light-danger remove-dept">
                                            <i class="ki-outline ki-trash fs-3"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($departments_list as $row): ?>
                                    <div class="row mb-2 department-item">
                                        <div class="col-7">
                                            <select name="department_ids[]" class="form-select form-select-solid" data-control="select2" data-placeholder="เลือกหน่วยงาน">
                                                <option></option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= $dept['id'] == $row['department_id'] ? 'selected' : '' ?>>
                                                        <?= $dept['short_name'] ?> - <?= $dept['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <select name="department_roles[]" class="form-select form-select-solid">
                                                <option value="Leader" <?= $row['role'] == 'Leader' ? 'selected' : '' ?>>Leader</option>
                                                <option value="CoWorking" <?= $row['role'] == 'CoWorking' ? 'selected' : '' ?>>CoWorking</option>
                                            </select>
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-icon btn-light-danger remove-dept">
                                                <i class="ki-outline ki-trash fs-3"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-light-primary btn-sm mt-2" id="add_department">
                            <i class="ki-outline ki-plus fs-3"></i> เพิ่มหน่วยงาน
                        </button>
                    </div>

                    <div class="d-flex justify-content-end pt-5">
                        <a href="<?= base_url('admin/keyresult') ?>" class="btn btn-light btn-active-light-primary me-2">ย้อนกลับ</a>
                        <button type="submit" class="btn btn-primary" id="kt_keyresult_submit">
                            <span class="indicator-label">บันทึก</span>
                            <span class="indicator-progress">กรุณารอสักครู่...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reference Table Sidebar -->
    <div class="col-xl-4">
        <div class="card card-flush h-lg-100">
            <div class="card-header pt-7">
                <div class="card-title">
                    <h4>รายการในกลุ่มนี้</h4>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-7 gy-3" id="table_reference">
                        <thead>
                            <tr class="fw-bold fs-7 text-gray-800 border-bottom-2 border-gray-200">
                                <th class="min-w-50px">Seq</th>
                                <th class="min-w-150px">Name</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <tr><td colspan="2" class="text-center text-muted">กรุณาเลือก Group/Objective</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var KeyResultFormConfig = {
        initialGroupId: '<?= $item['objective_group_id'] ?? '' ?>',
        initialObjectiveId: '<?= $item['objective_id'] ?? '' ?>',
        initialTemplateId: '<?= $item['key_result_template_id'] ?? '' ?>',
        relatedDataUrl: '<?= base_url('admin/keyresult/get-related-data') ?>',
        saveUrl: '<?= base_url('admin/keyresult/save') ?>',
        redirectUrl: '<?= base_url('admin/keyresult') ?>',
        departmentOptions: `
            <option></option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>"><?= esc($dept['short_name']) . ' - ' . esc($dept['name']) ?></option>
            <?php endforeach; ?>
        `
    };
</script>
