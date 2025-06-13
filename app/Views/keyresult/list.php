<div class="card card-flush">
        <!--begin::Card header-->
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <!--begin::Card title-->
            <div class="card-title">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                    <input type="text" data-kt-keyresults-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Keyresult" />
                </div>
                <!--end::Search-->
            </div>
            <!--end::Card title-->

        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_keyresults_table">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_category_table .form-check-input" value="1" />
                            </div>
                        </th>
                        <th class="min-w-250px">Key Result</th>
                        <th class="min-w-150px">Progress</th>
                        <th class="min-w-150px">Role</th>
                        <th class="text-end min-w-70px">Actions</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    <?php foreach ($keyresults as $item): ?>
                        <tr>
                            <td>
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" />
                                </div>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <!--begin::Thumbnail-->
                                    <a href="#" class="symbol symbol-50px">
                                        <?php
                                        // ตรวจสอบ og_id และกำหนดภาพ badge ที่เหมาะสม
                                        $og_id = isset($item['og_id']) ? (int)$item['og_id'] : 1;
                                        // ตรวจสอบว่า og_id อยู่ในช่วง 1-5 หรือไม่
                                        if ($og_id < 1 || $og_id > 5) {
                                            $og_id = 1; // ใช้ค่าเริ่มต้นเป็น 1 หาก og_id ไม่อยู่ในช่วงที่กำหนด
                                        }
                                        $badge_image = base_url('assets/images/badge-goal' . $og_id . '.png');
                                        ?>
                                        <span class="symbol-label" style="background-image:url(<?= $badge_image ?>); background-size: contain; background-repeat: no-repeat; background-position: center;"></span>

                                    </a>
                                    <!--end::Thumbnail-->
                                    <div class="ms-5">
                                        <!--begin::Goal Badge-->
                                        <?php
                                        // กำหนดสีตาม og_id
                                        $goal_colors = [
                                            1 => '#F4B400', // Goal 1 - Yellow
                                            2 => '#2196F3', // Goal 2 - Blue
                                            3 => '#D32F2F', // Goal 3 - Red
                                            4 => '#388E3C', // Goal 4 - Green
                                            5 => '#7B1FA2'  // Goal 5 - Purple
                                        ];
                                        $goal_color = isset($goal_colors[$og_id]) ? $goal_colors[$og_id] : $goal_colors[1];
                                        ?>
                                        <div class="mb-2">
                                            <span class="badge fw-bold px-3 py-2 fs-7" style="background-color: <?= $goal_color ?>; color: white; border-radius: 20px;">
                                                <?= esc($item['og_name']) ?>
                                            </span>
                                        </div>
                                        <!--end::Goal Badge-->
                                        <!--begin::Title-->
                                        <div class="mb-1">
                                            <a href="<?= base_url('keyresult/view/' . $item['key_result_id']) ?>" class="text-gray-800 text-hover-primary fs-5 fw-bold" data-kt-keyresults-filter="category_name">
                                                <?= esc($item['key_result_name']) ?>
                                            </a>
                                        </div>
                                        <!--end::Title-->
                                        <!--begin::Description-->
                                        <div class="text-muted fs-7 fw-semibold">
                                            <span class="text-gray-600"><?= esc($item['objective_name']) ?></span>
                                            <span class="text-gray-400 mx-1">|</span>
                                            <span class="text-gray-500"><?= esc($item['key_result_template_name']) ?></span>
                                        </div>
                                        <!--end::Description-->
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="badge badge-light-success fs-base">
                                    9.2%
                                </div>
                            </td>
                            <td>
                                <!--begin::Badges-->
                                <div class="badge badge-light-success fs-base">
                                    <?= esc($item['key_result_dep_role']) ?>
                                </div>
                                <!--end::Badges-->
                            </td>
                            <td class="text-end">
                                <a href="#" class="btn btn-sm btn-light btn-active-light-primary btn-flex btn-center" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions
                                <i class="ki-outline ki-down fs-5 ms-1"></i></a>
                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="apps/ecommerce/catalog/add-category.html" class="menu-link px-3">Edit</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" data-kt-ecommerce-category-filter="delete_row">Delete</a>
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
        </div>
        <!--end::Card body-->
    </div>