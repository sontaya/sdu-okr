<div class="card card-flush">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
                <input type="text" data-kt-keyresult-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="ค้นหา Key Result" value="<?= esc($current_filters['search']) ?>" />
            </div>
        </div>
        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
            <!-- Year Filter -->
            <div class="w-150px">
                <select class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="ปี" data-kt-keyresult-filter="year">
                    <option value="all">ทั้งหมด</option>
                    <?php foreach ($filter_options['years'] as $y): ?>
                        <option value="<?= $y ?>" <?= $current_filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <!-- Objective Group Filter -->
             <div class="w-200px">
                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Strategic Goal" data-kt-keyresult-filter="objective_group_id">
                    <option value="all">ทั้งหมด</option>
                    <?php foreach ($filter_options['objective_groups'] as $og): ?>
                        <option value="<?= $og['id'] ?>" <?= $current_filters['objective_group_id'] == $og['id'] ? 'selected' : '' ?>>
                            <?= $og['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <a href="<?= base_url('admin/keyresult/form') ?>" class="btn btn-primary">
                <i class="ki-outline ki-plus fs-2"></i> เพิ่ม Key Result
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_keyresults">
            <thead>
                <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_keyresults .form-check-input" value="1" />
                        </div>
                    </th>
                    <th class="min-w-300px">Key Result</th>
                    <th class="text-end min-w-100px">การดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="fw-semibold text-gray-600">
                <?php foreach ($key_results as $item): ?>
                <tr>
                    <td>
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="<?= $item['id'] ?>" />
                        </div>
                    </td>
                    <td>
                        <div class="d-flex">
                            <div class="d-flex flex-column align-items-center me-4">
                                <!-- Thumbnail -->
                                <div class="symbol symbol-50px mb-2">
                                    <?php
                                    $og_id = isset($item['og_id']) ? (int)$item['og_id'] : 1;
                                    if ($og_id < 1 || $og_id > 5) $og_id = 1;
                                    $badge_image = base_url('assets/images/badge-goal' . $og_id . '.png');
                                    ?>
                                    <span class="symbol-label" style="background-image:url(<?= $badge_image ?>); background-size: contain; background-repeat: no-repeat; background-position: center;"></span>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <!-- Goal Badge -->
                                <?php $goal_class = 'goal-badge-' . $og_id; ?>
                                <div class="mb-2">
                                    <span class="badge goal-badge <?= $goal_class ?> fs-7">
                                        <?= esc($item['og_name'] ?? 'Undefined') ?>
                                    </span>
                                </div>

                                <!-- Objective -->
                                <div class="fs-7 fw-bold obj-color-<?= $og_id ?>">
                                    <?= esc($item['objective_name']) ?>
                                </div>

                                <!-- Key Results -->
                                <div class="mb-1">
                                    <span class="text-gray-600 fs-7 fw-semibold">
                                        KR: <?= esc($item['template_name'] ?? '') ?>
                                    </span>
                                </div>

                                <!-- Title -->
                                <div class="mb-1">
                                    <a href="<?= base_url('admin/keyresult/form/' . $item['id']) ?>" class="text-gray-600 text-hover-primary fs-7 fw-semibold">
                                        KR<?= esc($item['key_result_year']) ?>/<?= esc($item['sequence_no']) ?>: <?= esc($item['name']) ?>
                                    </a>
                                </div>

                                <!-- KR Description -->
                                <div class="mb-1 text-muted fs-8">
                                    <span class="text-gray-500">เป้าหมาย: <?= esc($item['target_value']) ?> <?= esc($item['target_unit']) ?></span>
                                </div>

                                <!-- KR Departments -->
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <?php if (!empty($item['departments'])): ?>
                                        <?php foreach ($item['departments'] as $dept): ?>
                                            <?php
                                            $badgeClass = ($dept['role'] === 'Leader') ? 'badge-primary' : 'badge-light-gray';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>" title="<?= esc($dept['department_name']) ?>">
                                                <?= esc($dept['short_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </td>

                    <td class="text-end">
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            การดำเนินการ <i class="ki-outline ki-down fs-5 ms-1"></i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="<?= base_url('admin/keyresult/form/' . $item['id']) ?>" class="menu-link px-3">
                                    แก้ไข
                                </a>
                            </div>
                            <div class="menu-item px-3">
                                <a href="#" class="menu-link px-3 text-danger" data-kt-keyresult-filter="delete_row" data-id="<?= $item['id'] ?>" data-name="<?= esc($item['name']) ?>">
                                    ลบ
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var table = document.querySelector('#kt_table_keyresults');
    var datatable;

    if (!table) {
        return;
    }

    datatable = $(table).DataTable({
        "info": false,
        'order': [],
        'pageLength': 10,
        'lengthChange': true,
        'columnDefs': [
            { orderable: false, targets: 0 },
            { orderable: false, targets: 2 }
        ]
    });

    // Client-side Search
    const filterSearch = document.querySelector('[data-kt-keyresult-filter="search"]');
    if (filterSearch) {
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Filters Change Event
    const yearFilter = document.querySelector('[data-kt-keyresult-filter="year"]');
    const ogFilter = document.querySelector('[data-kt-keyresult-filter="objective_group_id"]');

    const handlFilterChange = () => {
        let year = yearFilter.value;
        let og = ogFilter.value;

        let url = new URL(window.location.href);
        if (year && year !== 'all') {
             url.searchParams.set('year', year);
        } else {
             url.searchParams.delete('year');
        }

        if (og && og !== 'all') {
            url.searchParams.set('objective_group_id', og);
        } else {
            url.searchParams.delete('objective_group_id');
        }

        window.location.href = url.toString();
    }

    $(yearFilter).on('change', handlFilterChange);
    $(ogFilter).on('change', handlFilterChange);


    // Delete Action
    $(document).on('click', '[data-kt-keyresult-filter="delete_row"]', function (e) {
        e.preventDefault();
        const parent = $(this).closest('tr');
        const id = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
            text: "คุณแน่ใจว่าต้องการลบ \"" + name + "\" หรือไม่?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "ใช่, ลบเลย!",
            cancelButtonText: "ยกเลิก",
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function (result) {
            if (result.value) {
                // Send Delete Request
                $.ajax({
                    url: '<?= base_url('admin/keyresult/delete') ?>/' + id,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                text: "ลบข้อมูลสำเร็จ!",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            }).then(function () {
                                datatable.row(parent).remove().draw();
                            });
                        } else {
                            Swal.fire({
                                text: response.message || "ลบข้อมูลไม่สำเร็จ",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            text: "เกิดข้อผิดพลาด: " + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText),
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            }
        });
    });
});
</script>
