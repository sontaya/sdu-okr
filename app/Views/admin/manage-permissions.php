<!-- Enhanced manage-permissions.php with eProfile integration -->

<div class="row g-6 g-xl-9">
    <!-- Summary Cards -->
    <div class="col-md-6 col-xl-4">
        <div class="card bg-light-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-primary">
                            <i class="ki-outline ki-people fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-primary"><?= count($users) ?></div>
                        <div class="fs-7 text-muted">ผู้ใช้ทั้งหมด</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card bg-light-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-warning">
                            <i class="ki-outline ki-notification-status fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-warning"><?= $pending_approvals_count ?></div>
                        <div class="fs-7 text-muted">รออนุมัติ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card bg-light-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-40px me-5">
                        <span class="symbol-label bg-success">
                            <i class="ki-outline ki-shield-tick fs-1 text-white"></i>
                        </span>
                    </div>
                    <div>
                        <div class="fs-1 fw-bold text-success">
                            <?= count(array_filter($users, function($user) { return !empty($user['current_roles']); })) ?>
                        </div>
                        <div class="fs-7 text-muted">มีสิทธิ์</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="card mt-6">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                <input type="text" data-kt-user-table-filter="search"
                       class="form-control form-control-solid w-250px ps-13"
                       placeholder="ค้นหาผู้ใช้..." />
            </div>
            <!-- Department Filter -->
            <div class="d-flex align-items-center ms-3">
                <select id="department-filter" class="form-select form-select-solid w-200px">
                    <option value="">ทุกหน่วยงาน</option>
                    <?php foreach ($all_departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $dept['id'] == session('department') ? 'selected' : '' ?>>
                            <?= esc($dept['short_name']) ?> - <?= esc($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
                    <i class="ki-outline ki-plus fs-2"></i>
                    เพิ่มผู้ใช้ใหม่
                </button>
            </div>
        </div>
    </div>
    <div class="card-body py-4">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users_permissions">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">ผู้ใช้</th>
                    <th class="min-w-125px">หน่วยงาน</th>
                    <th class="min-w-125px">สิทธิ์ปัจจุบัน</th>
                    <th class="text-end min-w-100px">จัดการสิทธิ์</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                <?php foreach ($users as $user): ?>
                <tr data-user-id="<?= $user['id'] ?>" data-department-id="<?= $user['department_id'] ?>">
                    <td class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <div class="symbol-label">
                                <div class="symbol-label fs-3 bg-light-primary text-primary">
                                    <?= strtoupper(substr($user['uid'], 0, 1)) ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <a href="#" class="text-gray-800 text-hover-primary mb-1"><?= esc($user['full_name']) ?></a>
                            <span class="text-muted"><?= esc($user['uid']) ?></span>
                        </div>
                    </td>
                    <td><?= esc($user['department_name']) ?></td>
                    <td>
                        <div class="role-badges" data-user-id="<?= $user['id'] ?>">
                            <?php if (empty($user['current_roles'])): ?>
                                <span class="badge badge-light-secondary">ไม่มีสิทธิ์</span>
                            <?php else: ?>
                                <?php
                                $roles = explode(',', $user['current_roles']);
                                foreach ($roles as $role):
                                    $role = trim($role);
                                    $badgeClass = [
                                        'Admin' => 'badge-danger',
                                        'Approver' => 'badge-warning',
                                        'Reporter' => 'badge-primary'
                                    ][$role] ?? 'badge-secondary';
                                ?>
                                    <span class="badge <?= $badgeClass ?> me-1 role-badge" data-role="<?= $role ?>">
                                        <?= getUserRoleNames($role) ?>
                                        <?php if ($user['id'] != session('user_id')): ?>
                                        <i class="ki-outline ki-cross fs-7 ms-1 text-hover-danger cursor-pointer remove-role"
                                           data-user-id="<?= $user['id'] ?>"
                                           data-role="<?= $role ?>"
                                           title="เพิกถอนสิทธิ์"></i>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-end">
                        <?php if ($user['id'] != session('user_id')): ?>
                        <div class="dropdown">
                            <button class="btn btn-light btn-active-light-primary btn-sm"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                จัดการสิทธิ์
                                <i class="ki-outline ki-down fs-5 ms-1"></i>
                            </button>
                            <div class="dropdown-menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4">
                                <div class="menu-item px-3">
                                    <button class="menu-link px-3 edit-user-btn" data-user-id="<?= $user['id'] ?>">
                                        <i class="ki-outline ki-pencil fs-6 me-2"></i>
                                        แก้ไขข้อมูลผู้ใช้
                                    </button>
                                </div>
                                <div class="separator my-2"></div>

                                <div class="menu-item px-3">
                                    <button class="menu-link px-3 grant-role-btn"
                                            data-user-id="<?= $user['id'] ?>"
                                            data-role="Reporter">
                                        <i class="ki-outline ki-plus fs-6 me-2"></i>
                                        เพิ่มสิทธิ์ผู้รายงาน
                                    </button>
                                </div>
                                <div class="menu-item px-3">
                                    <button class="menu-link px-3 grant-role-btn"
                                            data-user-id="<?= $user['id'] ?>"
                                            data-role="Approver">
                                        <i class="ki-outline ki-shield-tick fs-6 me-2"></i>
                                        เพิ่มสิทธิ์ผู้อนุมัติ
                                    </button>
                                </div>
                                <div class="menu-item px-3">
                                    <button class="menu-link px-3 grant-role-btn"
                                            data-user-id="<?= $user['id'] ?>"
                                            data-role="Admin">
                                        <i class="ki-outline ki-crown fs-6 me-2"></i>
                                        เพิ่มสิทธิ์ผู้ดูแลระบบ
                                    </button>
                                </div>
                                <div class="separator my-2"></div>
                                <div class="menu-item px-3">
                                    <button class="menu-link px-3 text-danger revoke-all-roles-btn"
                                            data-user-id="<?= $user['id'] ?>">
                                        <i class="ki-outline ki-trash fs-6 me-2"></i>
                                        เพิกถอนสิทธิ์ทั้งหมด
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <span class="badge badge-light-info">ตัวคุณเอง</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Enhanced Modal เพิ่มผู้ใช้ใหม่ -->
<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">เพิ่มผู้ใช้ใหม่จากระบบ eProfile</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-10">
                <!-- Search Step -->
                <div id="search-step">
                    <div class="mb-10">
                        <label class="required form-label">ค้นหาผู้ใช้จาก eProfile</label>
                        <div class="input-group">
                            <input type="text" id="eprofile-search" class="form-control"
                                   placeholder="ค้นหาด้วยชื่อ, นามสกุล, หรือ USER_ID (อย่างน้อย 2 ตัวอักษร)" />
                            <button class="btn btn-primary" type="button" id="search-eprofile-btn">
                                <i class="ki-outline ki-magnifier fs-4"></i>
                                ค้นหา
                            </button>
                        </div>
                        <div class="form-text">ระบบจะค้นหาจากฐานข้อมูลบุคลากรมหาวิทยาลัย</div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="search-loading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังค้นหา...</span>
                        </div>
                        <div class="mt-3">กำลังค้นหาข้อมูลจาก eProfile...</div>
                    </div>

                    <!-- Search Results -->
                    <div id="search-results" style="display: none;">
                        <div class="separator my-5"></div>
                        <h5 class="mb-5">ผลการค้นหา:</h5>
                        <div id="users-list" class="max-h-400px overflow-auto">
                            <!-- Dynamic content -->
                        </div>
                    </div>
                </div>

                <!-- User Details Step -->
                <div id="user-details-step" style="display: none;">
                    <div class="d-flex align-items-center mb-5">
                        <button type="button" id="back-to-search" class="btn btn-sm btn-light-primary me-3">
                            <i class="ki-outline ki-arrow-left fs-4"></i>
                            ย้อนกลับ
                        </button>
                        <h5 class="mb-0">รายละเอียดผู้ใช้ใหม่</h5>
                    </div>

                    <form id="kt_modal_add_user_form">
                        <!-- User Info Display -->
                        <div class="card mb-8">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">ชื่อ-นามสกุล:</label>
                                            <div id="display-full-name" class="form-control-plaintext">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Department Selection -->
                        <div class="mb-8">
                            <label class="required form-label">หน่วยงาน</label>
                            <select name="department_id" id="department-select" class="form-select" required>
                                <option value="">-- เลือกหน่วยงาน --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $dept['id'] == session('department') ? 'selected' : '' ?>>
                                        <?= esc($dept['short_name']) ?> - <?= esc($dept['name'] ?? 'ไม่ระบุ') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">เลือกหน่วยงานที่ผู้ใช้สังกัด</div>
                        </div>

                        <!-- Initial Roles -->
                        <div class="mb-8">
                            <label class="form-label">สิทธิ์เริ่มต้น</label>
                            <div class="form-check form-check-custom form-check-solid mb-2">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="Reporter" id="role_reporter" />
                                <label class="form-check-label" for="role_reporter">
                                    <strong>ผู้รายงาน</strong> - สามารถบันทึกและส่งรายงานความคืบหน้า
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid mb-2">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="Approver" id="role_approver" />
                                <label class="form-check-label" for="role_approver">
                                    <strong>ผู้อนุมัติ</strong> - สามารถอนุมัติรายงาน + สิทธิ์ผู้รายงาน
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="Admin" id="role_admin" />
                                <label class="form-check-label" for="role_admin">
                                    <strong>ผู้ดูแลระบบ</strong> - จัดการผู้ใช้ + สิทธิ์ทั้งหมด
                                </label>
                            </div>
                            <div class="form-text mt-2">สามารถเลือกได้หลายสิทธิ์ หรือไม่เลือกเลยก็ได้ (เพิ่มทีหลังได้)</div>
                        </div>

                        <div class="text-center">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary" id="add-user-btn">
                                <span class="indicator-label">
                                    <i class="ki-outline ki-plus fs-4 me-2"></i>
                                    เพิ่มผู้ใช้
                                </span>
                                <span class="indicator-progress">กำลังเพิ่ม...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>

                        <!-- Hidden field for user data -->
                        <input type="hidden" id="selected-user-data" name="user_data" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal แก้ไขผู้ใช้ -->
<div class="modal fade" id="kt_modal_edit_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">แก้ไขข้อมูลผู้ใช้</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-10">
                <form id="kt_modal_edit_user_form">
                    <!-- User Info Display -->
                    <div class="card mb-8">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">USER ID:</label>
                                        <div id="edit-display-user-id" class="form-control-plaintext">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ชื่อ-นามสกุล:</label>
                                        <div id="edit-display-full-name" class="form-control-plaintext">-</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">เลขบัตรประชาชน:</label>
                                        <div id="edit-display-citizen-id" class="form-control-plaintext">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ชื่อย่อ:</label>
                                        <div id="edit-display-first-last-name" class="form-control-plaintext">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Selection -->
                    <div class="mb-8">
                        <label class="required form-label">หน่วยงาน</label>
                        <select name="department_id" id="edit-department-select" class="form-select" required>
                            <option value="">-- เลือกหน่วยงาน --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>">
                                    <?= esc($dept['short_name']) ?> - <?= esc($dept['name'] ?? 'ไม่ระบุ') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">เลือกหน่วยงานที่ผู้ใช้สังกัด</div>
                    </div>

                    <!-- Roles -->
                    <div class="mb-8">
                        <label class="form-label">สิทธิ์</label>
                        <div class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="Reporter" id="edit_role_reporter" />
                            <label class="form-check-label" for="edit_role_reporter">
                                <strong>ผู้รายงาน</strong> - สามารถบันทึกและส่งรายงานความคืบหน้า
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="Approver" id="edit_role_approver" />
                            <label class="form-check-label" for="edit_role_approver">
                                <strong>ผู้อนุมัติ</strong> - สามารถอนุมัติรายงาน + สิทธิ์ผู้รายงาน
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="Admin" id="edit_role_admin" />
                            <label class="form-check-label" for="edit_role_admin">
                                <strong>ผู้ดูแลระบบ</strong> - จัดการผู้ใช้ + สิทธิ์ทั้งหมด
                            </label>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="update-user-btn">
                            <span class="indicator-label">
                                <i class="ki-outline ki-check fs-4 me-2"></i>
                                บันทึกการแก้ไข
                            </span>
                            <span class="indicator-progress">กำลังบันทึก...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>

                    <!-- Hidden field for user ID -->
                    <input type="hidden" id="edit-user-id" name="user_id" />
                </form>
            </div>
        </div>
    </div>
</div>