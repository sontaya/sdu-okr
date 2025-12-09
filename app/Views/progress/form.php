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
            <?= isset($is_edit) && $is_edit ? 'Edit Progress' : 'Add Progress' ?>
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
                            <?= isset($is_edit) && $is_edit ? 'แก้ไขข้อมูลรายงานความคืบหน้า' : 'เพิ่มข้อมูลรายงานความคืบหน้า' ?>
                        </a>
                    </li>
                    <!--end::Nav item-->
                </ul>
                <!--end::Nav-->
            </div>
        </div>
<!--end::KR Header Card-->



<!--begin::Form-->
<form id="kt_progress_form" class="form d-flex flex-column flex-lg-row"
      action="<?= isset($is_edit) && $is_edit ? base_url('progress/update/' . $progress['id']) : base_url('progress/save') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="key_result_id" value="<?= esc($key_result_id) ?>">
    <?php if (isset($is_edit) && $is_edit): ?>
        <input type="hidden" name="progress_id" value="<?= esc($progress['id']) ?>">
    <?php else: ?>
        <input type="hidden" name="reporting_period_id" value="<?= esc($reporting_period_id) ?>">
    <?php endif; ?>



    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">

        <!--begin::Status and Reporting Period Row-->
        <div class="row g-7">
            <!--begin::สถานะรายงาน-->
            <div class="col-lg-6">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>สถานะรายงาน</h2>
                        </div>
                        <div class="card-toolbar">
                            <div class="rounded-circle bg-warning w-15px h-15px" id="progress_status_indicator"></div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php
                        // ✅ ตรวจสอบสิทธิ์และสถานะปัจจุบัน
                        $currentStatus = $progress['status'] ?? 'draft';
                        $canEditStatus = hasRole('Approver') || hasRole('Admin');
                        $isNewReport = !isset($is_edit) || !$is_edit;
                        ?>

                        <?php if ($isNewReport || ($currentStatus === 'draft' && !$canEditStatus)): ?>
                            <!-- ✅ Reporter เห็นเฉพาะ draft สำหรับรายงานใหม่หรือแก้ไข draft -->
                            <select class="form-select mb-2" name="status" data-control="select2" data-hide-search="true"
                                    data-placeholder="เลือกสถานะ" id="progress_status_select">
                                <option value="draft" selected>ฉบับร่าง</option>
                            </select>
                            <div class="text-muted fs-7">รายงานจะถูกบันทึกเป็นฉบับร่าง</div>

                        <?php elseif ($canEditStatus): ?>
                            <!-- ✅ Approver/Admin เห็นสถานะทั้งหมด -->
                            <select class="form-select mb-2" name="status" data-control="select2" data-hide-search="true"
                                    data-placeholder="เลือกสถานะ" id="progress_status_select">
                                <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>ฉบับร่าง</option>
                                <option value="submitted" <?= $currentStatus === 'submitted' ? 'selected' : '' ?>>ส่งรายงานแล้ว</option>
                                <option value="approved" <?= $currentStatus === 'approved' ? 'selected' : '' ?>>อนุมัติแล้ว</option>
                                <option value="rejected" <?= $currentStatus === 'rejected' ? 'selected' : '' ?>>ปฏิเสธ</option>
                            </select>
                            <div class="text-muted fs-7">กำหนดสถานะการรายงาน</div>

                        <?php else: ?>
                            <!-- ✅ แสดงสถานะปัจจุบันเป็น readonly -->
                            <div class="alert alert-info d-flex align-items-center">
                                <div class="rounded-circle bg-<?= $currentStatus === 'submitted' ? 'info' : ($currentStatus === 'approved' ? 'success' : 'danger') ?> w-15px h-15px me-3"></div>
                                <div>
                                    <strong>สถานะปัจจุบัน:</strong>
                                    <?php
                                    $statusText = [
                                        'draft' => 'ฉบับร่าง',
                                        'submitted' => 'ส่งรายงานแล้ว',
                                        'approved' => 'อนุมัติแล้ว',
                                        'rejected' => 'ปฏิเสธ'
                                    ];
                                    echo $statusText[$currentStatus] ?? $currentStatus;
                                    ?>
                                </div>
                            </div>
                            <input type="hidden" name="status" value="<?= $currentStatus ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!--end::สถานะรายงาน-->

            <!--begin::รอบการรายงาน-->
            <?php if (!isset($is_edit) || !$is_edit): ?>
            <div class="col-lg-6">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>รอบการรายงาน</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <select class="form-select mb-2" name="reporting_period_id" data-control="select2"
                                data-placeholder="เลือกรอบการรายงาน" required>
                            <option></option>
                            <?php foreach ($reportingPeriods as $period): ?>
                                <option value="<?= $period['id'] ?>"
                                        <?= ($reporting_period_id == $period['id']) ? 'selected' : '' ?>>
                                    <?= esc($period['quarter_name']) ?>
                                    (<?= date('d/m/Y', strtotime($period['start_date'])) ?> - <?= date('d/m/Y', strtotime($period['end_date'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="text-muted fs-7">เลือกรอบการรายงาน</div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="col-lg-6">
                <div class="alert alert-info">
                    <h4 class="alert-heading">รอบการรายงาน</h4>
                    <p class="mb-0">กำลังแก้ไขรายงานที่มีอยู่แล้ว ไม่สามารถเปลี่ยนรอบการรายงานได้</p>
                </div>
            </div>
            <?php endif; ?>
            <!--end::รอบการรายงาน-->
        </div>
        <!--end::Status and Reporting Period Row-->

        <!--begin::ข้อมูลความคืบหน้า-->
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2>ข้อมูลความคืบหน้า</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <!--begin::ค่าความคืบหน้า-->
                <div class="mb-10 fv-row">
                    <label class="required form-label">ค่าความคืบหน้า</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="progress_value" class="form-control"
                               placeholder="0.00" value="<?= old('progress_value', $progress['progress_value'] ?? '') ?>"
                               id="progress_value_input" required />
                        <span class="input-group-text"><?= esc($keyresult['target_unit']) ?></span>
                    </div>
                    <div class="form-text">
                        เป้าหมาย: <?= esc($keyresult['target_value']) ?> <?= esc($keyresult['target_unit']) ?>
                        <span id="progress_percentage_display" class="fw-bold text-primary ms-3"></span>
                    </div>
                </div>
                <!--end::ค่าความคืบหน้า-->

                <!--begin::คำอธิบายความคืบหน้า-->
                <div class="mb-10 fv-row">
                    <label class="form-label">รายละเอียดการดำเนินงาน และ ความคืบหน้า</label>
                    <div id="progress_description_editor" class="min-h-200px mb-2"></div>
                    <textarea name="progress_description" id="progress_description" hidden><?= old('progress_description', $progress['progress_description'] ?? '') ?></textarea>
                    <div class="text-muted fs-7">อธิบายรายละเอียดของความคืบหน้าในรอบนี้</div>
                </div>
                <!--end::คำอธิบายความคืบหน้า-->
            </div>
        </div>
        <!--end::ข้อมูลความคืบหน้า-->

        <!--begin::อุปสรรคและแนวทางแก้ไข-->
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2>อุปสรรคและแนวทางแก้ไข</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <!--begin::อุปสรรคและปัญหา-->
                <div class="mb-10 fv-row">
                    <label class="form-label">อุปสรรคและปัญหา</label>
                    <div id="challenges_editor" class="min-h-350px mb-2"></div>
                    <textarea name="challenges" id="challenges" hidden><?= old('challenges', $progress['challenges'] ?? '') ?></textarea>
                    <div class="text-muted fs-7">ระบุอุปสรรคและปัญหาที่พบในการดำเนินงาน</div>
                </div>
                <!--end::อุปสรรคและปัญหา-->

                <!--begin::แนวทางแก้ไข-->
                <div class="mb-10 fv-row">
                    <label class="form-label">แนวทางแก้ไข</label>
                    <div id="solutions_editor" class="min-h-350px mb-2"></div>
                    <textarea name="solutions" id="solutions" hidden><?= old('solutions', $progress['solutions'] ?? '') ?></textarea>
                    <div class="text-muted fs-7">ระบุแนวทางในการแก้ไขปัญหา</div>
                </div>
                <!--end::แนวทางแก้ไข-->

                <!--begin::แผนการดำเนินงานต่อไป-->
                <div class="mb-5 fv-row">
                    <label class="form-label">แผนการดำเนินงานต่อไป</label>
                    <div id="next_actions_editor" class="min-h-350px mb-2"></div>
                    <textarea name="next_actions" id="next_actions" hidden><?= old('next_actions', $progress['next_actions'] ?? '') ?></textarea>
                    <div class="text-muted fs-7">ระบุแผนการดำเนินงานในช่วงต่อไป</div>
                </div>
                <!--end::แผนการดำเนินงานต่อไป-->
            </div>
        </div>
        <!--end::อุปสรรคและแนวทางแก้ไข-->

        <!--begin::รายการข้อมูลที่เกี่ยวข้อง-->
        <div class="card card-flush py-4">
            <div class="card-header">
                <div class="card-title">
                    <h2>รายการข้อมูลที่เกี่ยวข้อง</h2>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="fv-row">
                    <label class="form-label">เลือกรายการข้อมูลที่เกี่ยวข้องกับความคืบหน้านี้</label>

                    <div class="row">
                        <!-- รายการข้อมูลทั้งหมด -->
                        <div class="col-md-5">
                            <div class="form-label fw-bold">รายการข้อมูลทั้งหมด</div>
                            <select multiple class="form-select" id="available_entries" size="8">
                                <?php foreach ($all_entries as $entry): ?>
                                    <?php if (!in_array($entry['id'], $selected_entries)): ?>
                                        <option value="<?= $entry['id'] ?>"
                                                data-description="<?= esc($entry['entry_description']) ?>"
                                                data-status="<?= esc($entry['entry_status']) ?>">
                                            <?= esc($entry['entry_name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- ปุ่มควบคุม -->
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-primary mb-2" id="btn_add_entries">
                                <i class="ki-outline ki-right fs-2"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary mb-2" id="btn_add_all_entries">
                                <i class="ki-outline ki-double-right fs-2"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary mb-2" id="btn_remove_entries">
                                <i class="ki-outline ki-left fs-2"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" id="btn_remove_all_entries">
                                <i class="ki-outline ki-double-left fs-2"></i>
                            </button>
                        </div>

                        <!-- รายการที่เลือก -->
                        <div class="col-md-5">
                            <div class="form-label fw-bold">รายการที่เลือก</div>
                            <select multiple class="form-select" id="selected_entries" name="selected_entries[]" size="8">
                                <?php foreach ($all_entries as $entry): ?>
                                    <?php if (in_array($entry['id'], $selected_entries)): ?>
                                        <option value="<?= $entry['id'] ?>"
                                                data-description="<?= esc($entry['entry_description']) ?>"
                                                data-status="<?= esc($entry['entry_status']) ?>">
                                            <?= esc($entry['entry_name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- รายละเอียดรายการที่เลือก -->
                    <div id="entry_details" class="mt-4" style="display: none;">
                        <div class="alert alert-info">
                            <h6 class="alert-heading" id="entry_title"></h6>
                            <p class="mb-0" id="entry_description"></p>
                            <small class="text-muted" id="entry_status"></small>
                        </div>
                    </div>

                    <div class="text-muted fs-7 mt-2">
                        เลือกรายการข้อมูลที่เกี่ยวข้องกับความคืบหน้าในรอบนี้
                        คลิกที่รายการเพื่อดูรายละเอียด
                    </div>
                </div>
            </div>
        </div>
        <!--end::รายการข้อมูลที่เกี่ยวข้อง-->

        <!--begin::Action buttons-->
        <div class="d-flex justify-content-end">
            <a href="<?= base_url('progress/view/' . $key_result_id) ?>" class="btn btn-light me-5">ยกเลิก</a>

            <?php if (isset($is_edit) && $is_edit && $progress['status'] === 'draft'): ?>
                <button type="button" id="kt_progress_submit_btn" class="btn btn-warning me-3">
                    <span class="indicator-label">ส่งรายงาน</span>
                    <span class="indicator-progress">กำลังส่ง...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                </button>
            <?php endif; ?>

            <button type="submit" id="kt_progress_save_btn" class="btn btn-primary">
                <span class="indicator-label"><?= isset($is_edit) && $is_edit ? 'บันทึกการแก้ไข' : 'บันทึกความคืบหน้า' ?></span>
                <span class="indicator-progress">กำลังบันทึก...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
        <!--end::Action buttons-->

    </div>
    <!--end::Main column-->
</form>
<!--end::Form-->

<script>
    // ข้อมูลสำหรับ JavaScript
    const isEditMode = <?= isset($is_edit) && $is_edit ? 'true' : 'false' ?>;
    const targetValue = <?= $keyresult['target_value'] ?? 0 ?>;
    const progressId = <?= isset($progress['id']) ? $progress['id'] : 'null' ?>;
    const keyResultId = <?= $key_result_id ?>;
</script>