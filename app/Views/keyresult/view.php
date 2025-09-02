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
            View KR
        </span>
    </div>
    <!--end::Path-->

</div>
<!--end::Breadcrumb-->


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
                            <!--begin::Actions-->
                            <div class="d-flex mb-4">
                                <a href="<?= base_url('keyresult/form/'.esc($keyresult['key_result_id'])); ?>" class="btn btn-sm btn-primary me-3">เพิ่มรายการข้อมูล</a>
                                <!--begin::Menu-->
                                <div class="me-0">
                                    <button class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="ki-solid ki-dots-horizontal fs-2x"></i>
                                    </button>
                                    <!--begin::Menu 3-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                                        <!--begin::Heading-->
                                        <div class="menu-item px-3">
                                            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Actions</div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Menu item-->
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3">Create Invoice</a>
                                        </div>
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu 3-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::KR Heading-->
                        <!--begin::Info-->
                        <div class="d-flex flex-wrap justify-content-start">
                            <!--begin::Stats-->
                            <div class="d-flex flex-wrap">
                                <!--begin::Stat-->
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <!--begin::Number-->
                                    <div class="d-flex align-items-center">
                                        <div class="fs-4 fw-bold"><?= count($entries) ?> รายการ</div>
                                    </div>
                                    <!--end::Number-->
                                    <!--begin::Label-->
                                    <div class="fw-semibold fs-6 text-gray-500">รายการข้อมูล</div>
                                    <!--end::Label-->
                                </div>
                                <!--end::Stat-->
                                <!--begin::Stat-->
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <!--begin::Number-->
                                    <div class="d-flex align-items-center">
                                        <div class="fs-4 fw-bold"><?= array_sum(array_column($entries, 'file_count')) ?> ไฟล์</div>
                                    </div>
                                    <!--end::Number-->
                                    <!--begin::Label-->
                                    <div class="fw-semibold fs-6 text-gray-500">เอกสารแนบ</div>
                                    <!--end::Label-->
                                </div>
                                <!--end::Stat-->

                            </div>
                            <!--end::Stats-->

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
                        <a class="nav-link text-active-primary py-5 me-6 active" href="#">ปี 2568</a>
                    </li>
                    <!--end::Nav item-->
                </ul>
                <!--end::Nav-->
            </div>
        </div>

        <!--begin::รายการข้อมูล-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">รายการข้อมูล</h3>
                </div>
                <!--end::Card title-->
                <?php if (!empty($entries)): ?>
                <!--begin::Card toolbar-->
                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                        <input type="text" data-kt-entries-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="ค้นหารายการข้อมูล" />
                    </div>
                    <!--end::Search-->
                    <!--begin::Filter-->
                    <div class="w-150px">
                        <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="สถานะ" data-kt-entries-filter="status">
                            <option></option>
                            <option value="all">ทั้งหมด</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <!--end::Filter-->
                </div>
                <!--end::Card toolbar-->
                <?php endif; ?>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <?php if (!empty($entries)): ?>
                <!--begin::Table-->
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_entries_table">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-10px pe-2">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_entries_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="min-w-300px">รายการข้อมูล</th>
                            <th class="min-w-150px">คำสำคัญ</th>
                            <th class="min-w-100px">ไฟล์แนบ</th>
                            <th class="min-w-100px">สถานะ</th>
                            <th class="min-w-100px">วันที่สร้าง</th>
                            <th class="text-end min-w-70px">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        <?php foreach ($entries as $entry): ?>
                            <tr data-entry-id="<?= $entry['id'] ?>">
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="<?= $entry['id'] ?>" />
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <!--begin::Title-->
                                        <a href="#" class="text-gray-800 text-hover-primary fs-6 fw-bold mb-1">
                                            <?= esc($entry['entry_name']) ?>
                                        </a>
                                        <!--end::Title-->
                                        <!--begin::Description-->
                                        <?php if (!empty($entry['entry_description'])): ?>
                                            <div class="text-muted fs-7 fw-normal">
                                                <?= mb_substr(strip_tags($entry['entry_description']), 0, 100) . (mb_strlen(strip_tags($entry['entry_description'])) > 100 ? '...' : '') ?>
                                            </div>
                                        <?php endif; ?>
                                        <!--end::Description-->
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($entry['tags'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php
                                            $displayTags = array_slice($entry['tags'], 0, 3); // แสดงแค่ 3 tags แรก
                                            foreach ($displayTags as $tag):
                                            ?>
                                                <span class="badge badge-light-info fs-8"><?= esc($tag) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($entry['tags']) > 3): ?>
                                                <span class="badge badge-light-secondary fs-8">+<?= count($entry['tags']) - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fs-7">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($entry['file_count'] > 0): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="ki-outline ki-file fs-4 text-primary me-2"></i>
                                            <span class="fw-bold text-primary"><?= $entry['file_count'] ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fs-7">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';
                                    switch ($entry['entry_status']) {
                                        case 'published':
                                            $statusClass = 'badge-light-success';
                                            $statusText = 'Published';
                                            break;
                                        case 'draft':
                                            $statusClass = 'badge-light-warning';
                                            $statusText = 'Draft';
                                            break;
                                        case 'inactive':
                                            $statusClass = 'badge-light-danger';
                                            $statusText = 'Inactive';
                                            break;
                                        default:
                                            $statusClass = 'badge-light-secondary';
                                            $statusText = 'Unknown';
                                    }
                                    ?>
                                    <span class="badge <?= $statusClass ?> fw-bold px-4 py-3"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?= date('d/m/Y', strtotime($entry['created_date'])) ?></span>
                                        <span class="fs-7 text-muted"><?= date('H:i', strtotime($entry['created_date'])) ?></span>
                                    </div>
                                </td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary btn-flex btn-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                            Actions
                                            <i class="ki-outline ki-down fs-5 ms-1"></i>
                                        </a>
                                        <!--begin::Menu-->
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                                            <!--begin::Menu item - แสดงรายละเอียด-->
                                            <div class="menu-item px-3">
                                                <a href="<?= base_url('keyresult/view-entry/' . $entry['id']) ?>" class="menu-link px-3">
                                                    <i class="ki-outline ki-eye fs-5 me-2"></i>แสดงรายละเอียด
                                                </a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item - แก้ไข-->
                                            <div class="menu-item px-3">
                                                <a href="<?= base_url('keyresult/edit-entry/' . $entry['id']) ?>" class="menu-link px-3">
                                                    <i class="ki-outline ki-pencil fs-5 me-2"></i>แก้ไข
                                                </a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item - ลบ-->
                                            <div class="menu-item px-3">
                                                <a href="#" class="menu-link px-3 text-danger delete-entry-btn" data-entry-id="<?= $entry['id'] ?>">
                                                    <i class="ki-outline ki-trash fs-5 me-2"></i>ลบ
                                                </a>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu-->
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->
                <?php else: ?>
                <!--begin::Empty state-->
                <div class="d-flex flex-column flex-center text-center p-10">
                    <div class="card-px text-center py-20 my-10">
                        <h2 class="fs-2x fw-bold mb-10">ยังไม่มีรายการข้อมูล</h2>
                        <p class="text-gray-400 fs-4 fw-semibold mb-10">
                            เริ่มต้นโดยการเพิ่มรายการข้อมูลแรกของคุณ<br>
                            เพื่อติดตามความก้าวหน้าของ Key Result นี้
                        </p>
                        <a href="<?= base_url('keyresult/form/' . $keyresult['key_result_id']) ?>" class="btn btn-primary">
                            <i class="ki-outline ki-plus fs-2"></i>เพิ่มรายการแรก
                        </a>
                    </div>
                    <img class="mw-300px" src="<?= base_url('assets/images/no-data-illustration.svg') ?>" alt="ไม่มีข้อมูล" />
                </div>
                <!--end::Empty state-->
                <?php endif; ?>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::รายการข้อมูล-->

        <!--begin::Modal แสดงรายละเอียด Entry-->
        <div class="modal fade" id="kt_modal_entry_details" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered mw-900px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header" id="kt_modal_entry_details_header">
                        <!--begin::Modal title-->
                        <h2 class="fw-bold" id="modal-entry-title">รายละเอียดข้อมูล</h2>
                        <!--end::Modal title-->
                        <!--begin::Close-->
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-modal-action="close">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body px-5 my-7">
                        <!--begin::Loading-->
                        <div id="modal-loading" class="d-flex align-items-center justify-content-center py-10">
                            <span class="spinner-border spinner-border-lg text-primary me-3"></span>
                            <span class="text-muted">กำลังโหลดข้อมูล...</span>
                        </div>
                        <!--end::Loading-->

                        <!--begin::Content-->
                        <div id="modal-content" style="display: none;">
                            <!--begin::Entry Info-->
                            <div class="card card-flush mb-6">
                                <div class="card-header">
                                    <h3 class="card-title">ข้อมูลทั่วไป</h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label class="fw-bold text-muted">สถานะ:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <span id="modal-entry-status" class="badge"></span>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label class="fw-bold text-muted">วันที่สร้าง:</label>
                                        </div>
                                        <div class="col-md-9" id="modal-entry-date"></div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Entry Info-->

                            <!--begin::Description-->
                            <div class="card card-flush mb-6">
                                <div class="card-header">
                                    <h3 class="card-title">รายละเอียด</h3>
                                </div>
                                <div class="card-body pt-0" id="modal-entry-description">
                                    <!-- Content will be loaded here -->
                                </div>
                            </div>
                            <!--end::Description-->

                            <!--begin::Tags-->
                            <div class="card card-flush mb-6">
                                <div class="card-header">
                                    <h3 class="card-title">คำสำคัญ</h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="modal-entry-tags" class="d-flex flex-wrap gap-2">
                                        <!-- Tags will be loaded here -->
                                    </div>
                                </div>
                            </div>
                            <!--end::Tags-->

                            <!--begin::Files-->
                            <div class="card card-flush">
                                <div class="card-header">
                                    <h3 class="card-title">ไฟล์แนบ</h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="modal-entry-files">
                                        <!-- Files will be loaded here -->
                                    </div>
                                </div>
                            </div>
                            <!--end::Files-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Modal body-->
                    <!--begin::Modal footer-->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-kt-modal-action="close">ปิด</button>
                        <button type="button" class="btn btn-primary" id="modal-edit-btn">แก้ไขข้อมูล</button>
                    </div>
                    <!--end::Modal footer-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal แสดงรายละเอียด Entry-->