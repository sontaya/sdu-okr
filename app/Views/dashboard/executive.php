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
            <div class="col-xl-6">
                <div class="card card-xl-stretch">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">🏛️ ความคืบหน้าตามหน่วยงาน</span>
                            <span class="text-muted fw-semibold fs-7">Top 10 หน่วยงาน</span>
                        </h3>
                        <div class="card-toolbar">
                            <button class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-duotone ki-dots-horizontal fs-5">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true">
                                <div class="px-7 py-5">
                                    <div class="fs-5 text-dark fw-bold">เรียงตาม</div>
                                </div>
                                <div class="separator border-gray-200"></div>
                                <div class="px-7 py-5">
                                    <a href="#" class="d-flex align-items-center p-3" onclick="sortDepartments('progress_desc')">
                                        <i class="ki-duotone ki-arrow-down fs-4 me-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-6 fw-bold">ความคืบหน้าสูง-ต่ำ</div>
                                    </a>
                                    <a href="#" class="d-flex align-items-center p-3" onclick="sortDepartments('name_asc')">
                                        <i class="ki-duotone ki-sort-asc fs-4 me-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="fs-6 fw-bold">ชื่อหน่วยงาน A-Z</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="departmentList" class="scroll-y me-n5 pe-5" data-kt-scroll="true" data-kt-scroll-height="400px">
                            <?php
                            $index = 1;
                            foreach (array_slice($departments['departments'], 0, 10) as $dept):
                            ?>
                            <div class="d-flex flex-stack py-4 border-bottom border-gray-300 border-bottom-dashed">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-4">
                                        <span class="symbol-label fs-6 fw-bold bg-light-primary text-primary"><?= $index ?></span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="#" class="fs-5 text-dark text-hover-primary fw-bold"><?= $dept['short_name'] ?></a>
                                        <div class="fs-6 fw-semibold text-gray-400"><?= $dept['total_key_results'] ?> KRs (<?= $dept['leader_count'] ?> Leader, <?= $dept['coworking_count'] ?> CoWork)</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="text-gray-900 fw-bolder fs-6 me-2"><?= round($dept['avg_progress'], 1) ?>%</span>
                                    <div class="progress h-6px w-60px">
                                        <div class="progress-bar <?= $dept['avg_progress'] >= 80 ? 'bg-success' : ($dept['avg_progress'] >= 60 ? 'bg-warning' : 'bg-danger') ?>"
                                             role="progressbar" style="width: <?= $dept['avg_progress'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $index++;
                            endforeach;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Trend Analysis -->
            <div class="col-xl-6">
                <div class="card card-xl-stretch">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">📈 เทรนด์ความคืบหน้า</span>
                            <span class="text-muted fw-semibold fs-7">เปรียบเทียบแต่ละไตรมาส</span>
                        </h3>
                        <div class="card-toolbar">
                            <select class="form-select form-select-sm" id="trendType">
                                <option value="quarterly">รายไตรมาส</option>
                                <option value="monthly">รายเดือน</option>
                                <option value="yearly">รายปี</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="trendChart" style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5 g-xl-8 mb-5 mb-xl-8">

            <!-- 5. Risk Analysis & Alerts -->
            <div class="col-xl-8">
                <div class="card card-xl-stretch">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">⚠️ การแจ้งเตือนและความเสี่ยง</span>
                            <span class="text-muted fw-semibold fs-7">รายการที่ต้องให้ความสำคัญ</span>
                        </h3>
                        <div class="card-toolbar">
                            <button class="btn btn-sm btn-light-warning">
                                <i class="ki-duotone ki-notification-bing fs-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <?= count($risks['critical_key_results']) + count($risks['overdue_reports']) ?> รายการ
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <!-- Summary Alerts -->
                        <div class="row g-3 mb-7">
                            <?php if ($risks['summary']['critical_count'] > 0): ?>
                            <div class="col-md-6">
                                <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                                    <i class="ki-duotone ki-information-5 fs-2tx text-danger me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-stack flex-grow-1">
                                        <div class="fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">🔴 Key Results ล่าช้า</h4>
                                            <div class="fs-6 text-gray-700">มี <?= $risks['summary']['critical_count'] ?> Key Results ที่มีความคืบหน้าต่ำกว่า 50%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($risks['summary']['overdue_count'] > 0): ?>
                            <div class="col-md-6">
                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                    <i class="ki-duotone ki-calendar-2 fs-2tx text-warning me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                    </i>
                                    <div class="d-flex flex-stack flex-grow-1">
                                        <div class="fw-semibold">
                                            <h4 class="text-gray-900 fw-bold">🟡 รายงานล่าช้า</h4>
                                            <div class="fs-6 text-gray-700">มี <?= $risks['summary']['overdue_count'] ?> รายงานที่ยังไม่ได้ส่ง</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Critical Key Results -->
                        <?php if (!empty($risks['critical_key_results'])): ?>
                        <div class="mb-7">
                            <h5 class="fw-bold text-gray-900 mb-4">Key Results ที่ต้องให้ความสำคัญ</h5>
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 gy-7">
                                    <thead>
                                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                            <th>Key Result</th>
                                            <th>หน่วยงาน</th>
                                            <th>Strategic Goal</th>
                                            <th>ความคืบหน้า</th>
                                            <th>การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($risks['critical_key_results'], 0, 5) as $critical): ?>
                                        <tr>
                                            <td>
                                                <a href="#" class="text-dark fw-bold text-hover-primary fs-6" onclick="viewKeyResult(<?= $critical['id'] ?>)">
                                                    <?= substr($critical['name'], 0, 60) ?><?= strlen($critical['name']) > 60 ? '...' : '' ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-primary"><?= $critical['department'] ?></span>
                                            </td>
                                            <td>
                                                <span class="text-muted fw-semibold fs-7"><?= substr($critical['objective_group'], 0, 20) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-danger fw-bold fs-6 me-2"><?= round($critical['progress_percentage'], 1) ?>%</span>
                                                    <div class="progress h-6px w-60px">
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $critical['progress_percentage'] ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-light-primary" onclick="assignAction(<?= $critical['id'] ?>)">
                                                    <i class="ki-duotone ki-notepad-edit fs-4">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Assign Action
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 6. Deep Analytics -->
            <div class="col-xl-4">
                <div class="card card-xl-stretch">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">🔍 การวิเคราะห์เชิงลึก</span>
                            <span class="text-muted fw-semibold fs-7">Insights & Analytics</span>
                        </h3>
                    </div>
                    <div class="card-body">

                        <!-- Key Metrics -->
                        <div class="row g-3 mb-7">
                            <div class="col-6">
                                <div class="bg-light-info px-4 py-3 rounded-2 text-center">
                                    <div class="fs-2 fw-bold text-info"><?= round($analytics['reporting_stats']['avg_updates_per_kr'] ?? 0, 1) ?></div>
                                    <div class="fs-7 fw-semibold text-gray-600">เฉลี่ยการอัพเดท/KR</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light-warning px-4 py-3 rounded-2 text-center">
                                    <div class="fs-2 fw-bold text-warning"><?= round($analytics['reporting_stats']['avg_reporting_delay'] ?? 0) ?></div>
                                    <div class="fs-7 fw-semibold text-gray-600">วัน เฉลี่ยล่าช้า</div>
                                </div>
                            </div>
                        </div>

                        <!-- Best Performers -->
                        <div class="mb-7">
                            <h6 class="fw-bold text-gray-900 mb-3">🏆 Best Performers</h6>
                            <?php foreach ($analytics['best_performers'] as $performer): ?>
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-40px me-4">
                                    <span class="symbol-label bg-light-success text-success fs-6 fw-bold">
                                        <?= round($performer['avg_progress']) ?>%
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-6 fw-bold text-gray-800"><?= $performer['name'] ?></span>
                                    <span class="fs-7 fw-semibold text-gray-400"><?= $performer['total_key_results'] ?> Key Results</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Insights -->
                        <div class="separator separator-dashed mb-5"></div>
                        <div>
                            <h6 class="fw-bold text-gray-900 mb-3">💡 Insights</h6>
                            <?php foreach ($analytics['insights'] as $insight): ?>
                            <div class="notice d-flex bg-light-<?= $insight['type'] === 'success' ? 'success' : ($insight['type'] === 'warning' ? 'warning' : 'info') ?> rounded border-dashed p-4 mb-4">
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-semibold">
                                        <div class="fs-6 text-gray-700"><?= $insight['icon'] ?> <?= $insight['message'] ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline และกิจกรรมสำคัญ -->
        <div class="row g-5 g-xl-8">
            <div class="col-xl-12">
                <div class="card card-xl-stretch">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">📅 Timeline และกิจกรรมสำคัญ</span>
                            <span class="text-muted fw-semibold fs-7">ภาพรวมเหตุการณ์สำคัญ</span>
                        </h3>
                        <div class="card-toolbar">
                            <button class="btn btn-sm btn-primary" onclick="exportDashboard()">
                                <i class="ki-duotone ki-exit-down fs-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Export Report
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-gray-900 mb-4">📋 กำหนดการสำคัญ</h6>
                                <div class="timeline-label">
                                    <div class="timeline-item">
                                        <div class="timeline-label fw-bold text-gray-800 fs-6">30 ก.ย.</div>
                                        <div class="timeline-content text-muted fw-semibold fs-6">ส่งรายงาน Q4 2568 (กำหนดสุดท้าย)</div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-label fw-bold text-gray-800 fs-6">15 ต.ค.</div>
                                        <div class="timeline-content text-muted fw-semibold fs-6">ประชุมทบทวน OKR ประจำปี 2568</div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-label fw-bold text-gray-800 fs-6">01 พ.ย.</div>
                                        <div class="timeline-content text-muted fw-semibold fs-6">เริ่มวางแผน OKR ปี 2569</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-gray-900 mb-4">📈 กิจกรรมล่าสุด</h6>
                                <div class="timeline-label">
                                    <?php foreach (array_slice($recent_activities, 0, 3) as $activity): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-label fw-bold text-gray-800 fs-6"><?= date('j M', strtotime($activity['updated_date'])) ?></div>
                                        <div class="timeline-content text-muted fw-semibold fs-6">
                                            <?= $activity['department'] ?> อัพเดทความคืบหน้า <?= round($activity['progress_percentage'], 1) ?>%
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
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