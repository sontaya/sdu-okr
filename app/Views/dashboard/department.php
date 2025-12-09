<div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
    <div class="d-flex align-items-center flex-wrap">
        <span class="text-primary fw-bold fs-7">Department Dashboard</span>
    </div>
</div>
<div class="row g-6 g-xl-9 mb-6">
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-primary"><i class="ki-outline ki-abstract-26 fs-1 text-white"></i></span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-primary"><?= $overview['total_key_results'] ?? 0 ?></div>
                        <div class="fs-7 text-muted">Total Key Results</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-success"><i class="ki-outline ki-chart-line fs-1 text-white"></i></span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-success"><?= round($overview['avg_progress'] ?? 0, 1) ?>%</div>
                        <div class="fs-7 text-muted">ความคืบหน้าเฉลี่ย</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-info"><i class="ki-outline ki-crown fs-1 text-white"></i></span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-info"><?= $overview['leader_count'] ?? 0 ?></div>
                        <div class="fs-7 text-muted">บทบาท Leader</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card bg-light-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-warning"><i class="ki-outline ki-people fs-1 text-white"></i></span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-warning"><?= $overview['coworking_count'] ?? 0 ?></div>
                        <div class="fs-7 text-muted">บทบาท Co-Working</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header align-items-center py-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" id="kt-department-search" class="form-control form-control-solid w-300px ps-12" placeholder="ค้นหา Key Result..." />
            </div>
        </div>
    </div>
    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_department_krs_table">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-400px">Key Result</th>
                    <th class="min-w-100px">บทบาท</th>
                    <th class="min-w-150px">ความคืบหน้าล่าสุด</th>
                    <th class="min-w-120px">อัพเดทล่าสุด</th>
                    <th class="text-end min-w-100px">Actions</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                <?php foreach ($key_results as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="<?= base_url('keyresult/view/' . $item['id']) ?>" class="text-gray-800 text-hover-primary mb-1 fs-6 fw-bold"><?= esc($item['name']) ?></a>
                                <span class="text-muted fs-7"><?= esc($item['objective_group_name']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php
                        $roleClass = $item['role'] === 'Leader' ? 'badge-primary' : 'badge-light-primary';
                        ?>
                        <div class="badge <?= $roleClass ?>"><?= esc($item['role']) ?></div>
                    </td>
                    <td>
                        <?php if ($item['progress_percentage'] !== null): ?>
                        <div class="d-flex align-items-center">
                            <div class="progress h-6px w-100px me-2">
                                <div class="progress-bar bg-success" style="width: <?= $item['progress_percentage'] ?>%"></div>
                            </div>
                            <span class="fw-bold"><?= round($item['progress_percentage'], 1) ?>%</span>
                        </div>
                        <?php else: ?>
                        <span class="text-muted">ยังไม่มีรายงาน</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-semibold"><?= $item['updated_date'] ? date('d M Y', strtotime($item['updated_date'])) : '-' ?></span>
                    </td>
                    <td class="text-end">
                        <a href="<?= base_url('keyresult/view/' . $item['id']) ?>" class="btn btn-sm btn-light-primary">
                            View Details
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>