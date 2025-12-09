<!--begin::Detailed Report-->
<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <div class="d-flex align-items-center flex-wrap">
        <a href="<?= base_url('strategic/overview') ?>" class="text-muted text-hover-primary fw-semibold fs-7">
            <i class="ki-outline ki-arrow-left fs-6 me-1"></i>
            Strategic Overview
        </a>
        <span class="text-muted mx-2">•</span>
        <span class="text-primary fw-bold fs-7">รายงานรายละเอียด</span>
    </div>
</div>

<!-- KR Header -->
<div class="card mb-6">
    <div class="card-body">
        <h1 class="text-gray-800 mb-2"><?= esc($keyresult['key_result_name']) ?></h1>
        <div class="text-muted fs-6"><?= esc($keyresult['objective_name']) ?></div>
    </div>
</div>

<!-- Detailed Stats -->
<div class="row g-6 mb-6">
    <div class="col-md-3">
        <div class="card bg-light-primary">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-primary"><?= $detailedStats['total_reports'] ?></div>
                <div class="text-muted">รายงานทั้งหมด</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light-success">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-success"><?= $detailedStats['approved_reports'] ?></div>
                <div class="text-muted">อนุมัติแล้ว</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light-info">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-info"><?= $detailedStats['avg_progress'] ?>%</div>
                <div class="text-muted">ความคืบหน้าเฉลี่ย</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light-warning">
            <div class="card-body text-center">
                <div class="fs-2 fw-bold text-warning"><?= $detailedStats['approval_rate'] ?>%</div>
                <div class="text-muted">อัตราการอนุมัติ</div>
            </div>
        </div>
    </div>
</div>

<!-- Progress History Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">ประวัติการรายงานทั้งหมด</h3>
    </div>
    <div class="card-body">
        <!-- แสดงรายละเอียดการรายงานแบบตาราง -->
        <table class="table table-row-bordered">
            <thead>
                <tr>
                    <th>วันที่</th>
                    <th>รอบรายงาน</th>
                    <th>ความคืบหน้า</th>
                    <th>สถานะ</th>
                    <th>ผู้รายงาน</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($progressHistory as $progress): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($progress['created_date'])) ?></td>
                    <td><?= esc($progress['quarter_name']) ?></td>
                    <td><?= number_format($progress['progress_percentage'], 1) ?>%</td>
                    <td>
                        <span class="badge badge-light-success"><?= esc($progress['status']) ?></span>
                    </td>
                    <td><?= esc($progress['creator_name'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>