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
            รายงานที่รอการอนุมัติ
        </span>
    </div>
    <!--end::Path-->
</div>
<!--end::Breadcrumb-->

<!--begin::Statistics Cards-->
<div class="row g-6 mb-8">
    <div class="col-md-4">
        <div class="card bg-light-warning">
            <div class="card-body d-flex align-items-center py-5">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-30px me-5">
                        <span class="symbol-label bg-warning">
                            <i class="ki-outline ki-time fs-3 text-white"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="fs-2 fw-bold text-gray-900" id="pending-count">
                            <?= count($pending_approvals) ?>
                        </div>
                        <div class="fs-7 fw-semibold text-muted">รายงานรอการอนุมัติ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light-success">
            <div class="card-body d-flex align-items-center py-5">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-30px me-5">
                        <span class="symbol-label bg-success">
                            <i class="ki-outline ki-check fs-3 text-white"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="fs-2 fw-bold text-gray-900" id="approved-today">0</div>
                        <div class="fs-7 fw-semibold text-muted">อนุมัติวันนี้</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-light-info">
            <div class="card-body d-flex align-items-center py-5">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-30px me-5">
                        <span class="symbol-label bg-info">
                            <i class="ki-outline ki-calendar fs-3 text-white"></i>
                        </span>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="fs-2 fw-bold text-gray-900">
                            <?php
                            $avgDays = 0;
                            if (count($pending_approvals) > 0) {
                                $totalDays = 0;
                                foreach ($pending_approvals as $report) {
                                    $submittedDate = new DateTime($report['submitted_date']);
                                    $today = new DateTime();
                                    $totalDays += $submittedDate->diff($today)->days;
                                }
                                $avgDays = round($totalDays / count($pending_approvals), 1);
                            }
                            echo $avgDays;
                            ?>
                        </div>
                        <div class="fs-7 fw-semibold text-muted">วันเฉลี่ยที่รอ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Statistics Cards-->

<!--begin::Pending Approvals Card-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h2 class="fw-bold m-0">
                <i class="ki-outline ki-notification-status fs-2 text-warning me-2"></i>
                รายงานที่รอการอนุมัติ
            </h2>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Filter-->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-3" style="top: 50%; transform: translateY(-50%);"></i>
                    <input type="text" id="search-reports" class="form-control form-control-solid w-250px ps-10" placeholder="ค้นหารายงาน...">
                </div>
                <button type="button" class="btn btn-light-primary" id="refresh-table">
                    <i class="ki-outline ki-arrows-circle fs-2"></i>
                    รีเฟรช
                </button>
            </div>
            <!--end::Filter-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">

        <!--begin::Hidden Data-->
            <input type="hidden" id="pending-approvals-data" value='<?= htmlspecialchars(json_encode($pending_approvals)) ?>'>
        <!--end::Hidden Data-->

        <?php if (empty($pending_approvals)): ?>
            <!--begin::Empty State-->
            <div class="d-flex flex-column flex-center py-10">
                <div class="mb-5">
                    <i class="ki-outline ki-check-circle fs-3x text-success"></i>
                </div>
                <div class="fs-1 fw-bold text-gray-700 mb-3">ไม่มีรายงานที่รอการอนุมัติ</div>
                <div class="fs-6 text-muted mb-5">รายงานทั้งหมดได้รับการอนุมัติแล้ว หรือยังไม่มีการส่งรายงานใหม่</div>
                <a href="<?= base_url('keyresult/list') ?>" class="btn btn-primary">
                    <i class="ki-outline ki-arrow-left fs-2"></i>
                    กลับไป Key Results
                </a>
            </div>
            <!--end::Empty State-->
        <?php else: ?>
            <!--begin::Notice-->
            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-6 p-6">
                <i class="ki-outline ki-information fs-2tx text-primary me-4"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">คำแนะนำการอนุมัติรายงาน</h4>
                        <div class="fs-6 text-gray-700">
                            • คลิกที่ชื่อรายงานเพื่อดูรายละเอียดก่อนตัดสินใจ<br>
                            • ตรวจสอบค่าความคืบหน้าและรายการข้อมูลที่เกี่ยวข้อง<br>
                            • สามารถเพิ่มความคิดเห็นก่อนอนุมัติหรือปฏิเสธได้
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Notice-->

            <!--begin::Table-->
            <div class="table-responsive">
                <table id="kt_pending_approvals_table" class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-200px">Key Result</th>
                            <th class="min-w-125px">รอบการรายงาน</th>
                            <th class="min-w-100px">ผู้รายงาน</th>
                            <th class="min-w-100px">ความคืบหน้า</th>
                            <th class="min-w-100px">วันที่ส่ง</th>
                            <th class="min-w-100px">รอมานาน</th>
                            <th class="text-end min-w-150px">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_approvals as $report): ?>
                        <tr>
                            <!--begin::Key Result-->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-5">
                                        <span class="symbol-label bg-light-warning text-warning fs-6 fw-bold">
                                            KR
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-start flex-column">
                                        <a href="<?= base_url('progress/view/' . $report['key_result_id'] . '/' . $report['id']) ?>"
                                           class="text-gray-900 fw-bold text-hover-primary fs-6">
                                            <?= esc($report['key_result_name']) ?>
                                        </a>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">
                                            ID: <?= $report['key_result_id'] ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <!--end::Key Result-->

                            <!--begin::Reporting Period-->
                            <td>
                                <div class="badge badge-light-info fs-7 fw-bold">
                                    <?= esc($report['quarter_name']) ?>
                                </div>
                                <div class="text-muted fs-8"><?= esc($report['year']) ?></div>
                            </td>
                            <!--end::Reporting Period-->

                            <!--begin::Reporter-->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-25px symbol-circle me-3">
                                        <span class="symbol-label bg-info text-inverse-info fs-8 fw-bold">
                                            <?= strtoupper(substr($report['creator_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-7">
                                            <?= esc($report['creator_name']) ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <!--end::Reporter-->

                            <!--begin::Progress-->
                            <td>
                                <?php
                                $percentage = $report['progress_percentage'] ?? 0;
                                $progressClass = 'success';
                                if ($percentage < 30) $progressClass = 'danger';
                                elseif ($percentage < 70) $progressClass = 'warning';
                                ?>
                                <div class="d-flex flex-column w-100px">
                                    <div class="d-flex justify-content-between fw-semibold text-gray-900 fs-7 mb-1">
                                        <span><?= number_format($percentage, 1) ?>%</span>
                                    </div>
                                    <div class="progress h-6px w-100 bg-light-<?= $progressClass ?>">
                                        <div class="progress-bar bg-<?= $progressClass ?>" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                </div>
                                <div class="text-muted fs-8 mt-1">
                                    <?= number_format($report['progress_value'], 2) ?> / <?= number_format($report['target_value'] ?? 0, 2) ?>
                                </div>
                            </td>
                            <!--end::Progress-->

                            <!--begin::Submitted Date-->
                            <td>
                                <div class="text-gray-900 fw-bold fs-7">
                                    <?= date('d/m/Y', strtotime($report['submitted_date'])) ?>
                                </div>
                                <div class="text-muted fs-8">
                                    <?= date('H:i น.', strtotime($report['submitted_date'])) ?>
                                </div>
                            </td>
                            <!--end::Submitted Date-->

                            <!--begin::Days Waiting-->
                            <td>
                                <?php
                                $submittedDate = new DateTime($report['submitted_date']);
                                $today = new DateTime();
                                $daysWaiting = $submittedDate->diff($today)->days;

                                $urgencyClass = 'success';
                                $urgencyText = 'ปกติ';
                                if ($daysWaiting > 7) {
                                    $urgencyClass = 'danger';
                                    $urgencyText = 'เร่งด่วน';
                                } elseif ($daysWaiting > 3) {
                                    $urgencyClass = 'warning';
                                    $urgencyText = 'ควรดำเนินการ';
                                }
                                ?>
                                <span class="badge badge-light-<?= $urgencyClass ?> fs-8 fw-bold">
                                    <?= $daysWaiting ?> วัน
                                </span>
                                <div class="text-muted fs-8"><?= $urgencyText ?></div>
                            </td>
                            <!--end::Days Waiting-->

                            <!--begin::Actions-->
                            <td class="text-end">
                                <div class="d-flex flex-column gap-2">
                                    <!--begin::View Details-->
                                    <a href="<?= base_url('progress/view/' . $report['key_result_id'] . '/' . $report['id']) ?>"
                                       class="btn btn-sm btn-light-primary">
                                        <i class="ki-outline ki-eye fs-6 me-1"></i>
                                        ดูรายละเอียด
                                    </a>
                                    <!--end::View Details-->

                                    <!--begin::Action Buttons-->
                                    <div class="d-flex gap-1">
                                        <!--begin::Approve Button-->
                                        <button type="button" class="btn btn-sm btn-success approve-btn"
                                                data-progress-id="<?= $report['id'] ?>"
                                                data-key-result="<?= esc($report['key_result_name']) ?>">
                                            <i class="ki-outline ki-check fs-6 me-1"></i>
                                            อนุมัติ
                                        </button>
                                        <!--end::Approve Button-->

                                        <!--begin::Reject Button-->
                                        <button type="button" class="btn btn-sm btn-danger reject-btn"
                                                data-progress-id="<?= $report['id'] ?>"
                                                data-key-result="<?= esc($report['key_result_name']) ?>">
                                            <i class="ki-outline ki-cross fs-6 me-1"></i>
                                            ปฏิเสธ
                                        </button>
                                        <!--end::Reject Button-->
                                    </div>
                                    <!--end::Action Buttons-->
                                </div>
                            </td>
                            <!--end::Actions-->
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!--end::Table-->
        <?php endif; ?>
    </div>
    <!--end::Card body-->
</div>
<!--end::Pending Approvals Card-->

<!--begin::Approve Modal-->
<div class="modal fade" id="kt_approve_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form id="kt_approve_form">
                <div class="modal-header">
                    <h2 class="fw-bold">อนุมัติรายงานความคืบหน้า</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 my-7">
                    <div class="fv-row mb-7">
                        <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-6">
                            <i class="ki-outline ki-information fs-2tx text-success me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <div class="fs-6 text-gray-700">
                                        คุณกำลังจะอนุมัติรายงาน: <strong id="approve-key-result-name"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fv-row">
                        <label class="fs-6 fw-semibold mb-2">ความคิดเห็นเพิ่มเติม (ไม่บังคับ)</label>
                        <textarea class="form-control form-control-solid" rows="3" name="approve_comment"
                                  placeholder="เพิ่มความคิดเห็นหรือคำแนะนำ..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">
                        <span class="indicator-label">อนุมัติรายงาน</span>
                        <span class="indicator-progress">กำลังดำเนินการ...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Approve Modal-->

<!--begin::Reject Modal-->
<div class="modal fade" id="kt_reject_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <form id="kt_reject_form">
                <div class="modal-header">
                    <h2 class="fw-bold">ปฏิเสธรายงานความคืบหน้า</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 my-7">
                    <div class="fv-row mb-7">
                        <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                            <i class="ki-outline ki-information fs-2tx text-danger me-4"></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <div class="fs-6 text-gray-700">
                                        คุณกำลังจะปฏิเสธรายงาน: <strong id="reject-key-result-name"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fv-row">
                        <label class="required fs-6 fw-semibold mb-2">เหตุผลในการปฏิเสธ</label>
                        <textarea class="form-control form-control-solid" rows="4" name="reject_reason" required
                                  placeholder="กรุณาระบุเหตุผลในการปฏิเสธ เพื่อให้ผู้รายงานสามารถปรับปรุงได้..."></textarea>
                        <div class="text-muted fs-7">เหตุผลนี้จะถูกส่งไปให้ผู้รายงานทราบ</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger">
                        <span class="indicator-label">ปฏิเสธรายงาน</span>
                        <span class="indicator-progress">กำลังดำเนินการ...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Reject Modal-->

