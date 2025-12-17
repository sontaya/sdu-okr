<!-- Executive Dashboard View -->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

        <!-- Dashboard Header -->
        <div class="card mb-5 mb-xl-8">
            <div class="card-body pt-9 pb-0">
                <div class="d-flex flex-wrap flex-sm-nowrap">
                    <div class="me-7 mb-4">
                        <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                            <i class="ki-duotone ki-chart-pie-simple fs-2x text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>

                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                            <div class="d-flex flex-column">
                                <div class="d-flex align-items-center mb-2">
                                    <h1 class="text-gray-900 fs-2qx fw-bold me-1">🎯 OKR Executive Dashboard</h1>
                                </div>
                                <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                    <span class="d-flex align-items-center text-gray-400 me-5 mb-2">
                                        <i class="ki-duotone ki-geolocation fs-4 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        มหาวิทยาลัยสวนดุสิต
                                    </span>
                                    <span class="d-flex align-items-center text-gray-400 me-5 mb-2">
                                        <i class="ki-duotone ki-calendar fs-4 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        ปีงบประมาณ <?= $year ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Period Selector -->
                            <div class="d-flex my-4">
                                <div class="me-4">
                                    <select class="form-select form-select-sm" id="yearSelector" data-control="select2" data-hide-search="true" disabled>
                                        <option value="2568" selected>2568</option>
                                    </select>
                                </div>
                                <div class="me-4">
                                    <select class="form-select form-select-sm" id="quarterSelector" data-control="select2" data-hide-search="true" disabled>
                                        <option value="4" selected>Q4</option>
                                    </select>
                                </div>
                                <button class="btn btn-sm btn-primary" id="refreshData">
                                    <i class="ki-duotone ki-arrows-circle fs-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    รีเฟรช
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="row g-5 g-xl-8">

            <!-- 1. ภาพรวมองค์กร -->
            <div class="col-xl-4">
                <div class="card card-xl-stretch mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">🏢 ภาพรวมองค์กร</span>
                            <span class="text-muted fw-semibold fs-7">สถานะปัจจุบัน</span>
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge badge-light-success fs-8">Live</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-7">
                            <div class="col bg-light-primary px-6 py-8 rounded-2 me-7 mb-7">
                                <i class="ki-duotone ki-medal-star fs-3x text-primary d-block my-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                <span class="text-primary fw-semibold fs-6">ความคืบหน้ารวม</span>
                            </div>
                            <div class="col">
                                <div class="fs-1 fw-bold text-dark mb-0" id="overallProgress"><?= round($overview['overall_progress'] ?? 0, 1) ?>%</div>
                                <div class="fs-7 fw-semibold text-gray-400">จากทั้งหมด <?= $overview['total_key_results'] ?? 0 ?> KRs</div>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-5"></div>

                        <div class="row g-5">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-30px me-5">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-duotone ki-check fs-5 text-success">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-6 text-gray-800"><?= $overview['on_track'] ?? 0 ?></span>
                                        <span class="fw-semibold fs-7 text-gray-400">เป้าหมาย</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-30px me-5">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-duotone ki-warning-2 fs-5 text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-6 text-gray-800"><?= $overview['at_risk'] ?? 0 ?></span>
                                        <span class="fw-semibold fs-7 text-gray-400">เสี่ยง</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-30px me-5">
                                        <span class="symbol-label bg-light-danger">
                                            <i class="ki-duotone ki-cross-circle fs-5 text-danger">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-6 text-gray-800"><?= $overview['behind'] ?? 0 ?></span>
                                        <span class="fw-semibold fs-7 text-gray-400">ล่าช้า</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-30px me-5">
                                        <span class="symbol-label bg-light-info">
                                            <i class="ki-duotone ki-time fs-5 text-info">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold fs-6 text-gray-800"><?= $overview['not_started'] ?? 0 ?></span>
                                        <span class="fw-semibold fs-7 text-gray-400">ยังไม่เริ่ม</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-5"></div>

                        <div class="d-flex align-items-center">
                            <span class="fw-semibold fs-7 text-gray-400 me-2">อัตราการรายงาน:</span>
                            <span class="fw-bold fs-7 text-primary"><?= $overview['reporting_rate'] ?? 0 ?>%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Strategic Goals Progress -->
            <div class="col-xl-8">
                <div class="card card-xl-stretch mb-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">🎯 ความคืบหน้าตาม Strategic Goals</span>
                            <span class="text-muted fw-semibold fs-7">แยกตามกลุ่มวัตถุประสงค์</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($strategic_goals as $goal): ?>
                        <div class="d-flex flex-stack mb-8">
                            <div class="d-flex">
                                <div class="d-flex flex-column">
                                    <div class="fs-6 fw-bold text-dark text-hover-primary"><?= $goal['name'] ?></div>
                                    <div class="fs-7 fw-semibold text-muted"><?= $goal['total_key_results'] ?> Key Results</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="text-gray-900 fw-bolder fs-6 me-2"><?= round($goal['avg_progress'], 1) ?>%</span>
                                <div class="progress h-6px w-100px">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $goal['avg_progress'] ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-5 mb-xl-8">

            <!-- 3. Department Performance -->
            <div class="col-xl-12">
                <div class="card card-xl-stretch mb-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">🏛️ ความคืบหน้าตามหน่วยงาน</span>
                            <span class="text-muted fw-semibold fs-7">ภาพรวมผลการดำเนินงานของทุกหน่วยงาน</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-50px">#</th>
                                        <th class="min-w-150px">หน่วยงาน</th>
                                        <th class="min-w-120px text-center">Key Results</th>
                                        <th class="min-w-120px text-center">บทบาท</th>
                                        <th class="min-w-200px">ความคืบหน้า</th>
                                        <th class="min-w-100px text-end">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody id="departmentList">
                                    <?php
                                    $index = 1;
                                    // Show more departments since it's full width now
                                    foreach (array_slice($departments['departments'], 0, 10) as $dept):
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="symbol symbol-35px">
                                                <span class="symbol-label fw-bold bg-light-primary text-primary"><?= $index ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <a href="#" class="text-dark fw-bold text-hover-primary fs-6"><?= $dept['short_name'] ?></a>
                                                <span class="text-muted fw-semibold fs-7"><?= $dept['name'] ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light fw-bold fs-7"><?= $dept['total_key_results'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light-info fs-8 me-1" title="Leader"><?= $dept['leader_count'] ?> L</span>
                                            <span class="badge badge-light-success fs-8" title="Co-Working"><?= $dept['coworking_count'] ?> C</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="text-gray-800 fw-bold fs-6 me-2"><?= round($dept['avg_progress'], 1) ?>%</span>
                                                <div class="progress h-6px w-100 bg-light">
                                                    <div class="progress-bar <?= $dept['avg_progress'] >= 80 ? 'bg-success' : ($dept['avg_progress'] >= 60 ? 'bg-warning' : 'bg-danger') ?>"
                                                         role="progressbar" style="width: <?= $dept['avg_progress'] ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <?php if($dept['avg_progress'] >= 80): ?>
                                                <span class="badge badge-light-success fw-bold">Excellent</span>
                                            <?php elseif($dept['avg_progress'] >= 60): ?>
                                                <span class="badge badge-light-warning fw-bold">Good</span>
                                            <?php else: ?>
                                                <span class="badge badge-light-danger fw-bold">Needs Improv.</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $index++;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Result Detail Modal -->
<div class="modal fade" id="keyResultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">รายละเอียด Key Result</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body" id="keyResultModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>