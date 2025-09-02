<!-- สร้างไฟล์: app/Views/admin/manage-permissions.php -->

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
        </div>
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_add_user">
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
                <tr data-user-id="<?= $user['id'] ?>">
                    <td class="d-flex align-items-center">
                        <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                            <div class="symbol-label">
                                <div class="symbol-label fs-3 bg-light-primary text-primary">
                                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
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

<!-- Modal เพิ่มผู้ใช้ใหม่ -->
<div class="modal fade" id="kt_modal_add_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">เพิ่มผู้ใช้ใหม่</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>
            <div class="modal-body py-10">
                <form id="kt_modal_add_user_form">
                    <div class="mb-10">
                        <label class="required form-label">UID ผู้ใช้</label>
                        <input type="text" name="uid" class="form-control" placeholder="เช่น john.doe" required />
                        <div class="form-text">UID ที่ใช้ล็อกอินเข้าระบบ</div>
                    </div>
                    <div class="mb-10">
                        <label class="required form-label">ชื่อ-นามสกุล</label>
                        <input type="text" name="full_name" class="form-control" placeholder="เช่น นายจอห์น โด" required />
                    </div>
                    <div class="mb-10">
                        <label class="required form-label">เลขบัตรประชาชน</label>
                        <input type="text" name="citizen_id" class="form-control" placeholder="1234567890123" maxlength="13" required />
                    </div>
                    <div class="mb-10">
                        <label class="form-label">สิทธิ์เริ่มต้น</label>
                        <div class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="Reporter" id="role_reporter" />
                            <label class="form-check-label" for="role_reporter">ผู้รายงาน</label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid mb-2">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="Approver" id="role_approver" />
                            <label class="form-check-label" for="role_approver">ผู้อนุมัติ</label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="Admin" id="role_admin" />
                            <label class="form-check-label" for="role_admin">ผู้ดูแลระบบ</label>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">เพิ่มผู้ใช้</span>
                            <span class="indicator-progress">กำลังเพิ่ม...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grant Role
    document.querySelectorAll('.grant-role-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const role = this.dataset.role;

            if (confirm(`คุณต้องการเพิ่มสิทธิ์ "${getUserRoleNames(role)}" ให้ผู้ใช้นี้ใช่หรือไม่?`)) {
                grantRole(userId, role);
            }
        });
    });

    // Remove Role
    document.querySelectorAll('.remove-role').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = this.dataset.userId;
            const role = this.dataset.role;

            if (confirm(`คุณต้องการเพิกถอนสิทธิ์ "${getUserRoleNames(role)}" จากผู้ใช้นี้ใช่หรือไม่?`)) {
                revokeRole(userId, role);
            }
        });
    });

    // Revoke All Roles
    document.querySelectorAll('.revoke-all-roles-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;

            if (confirm('คุณต้องการเพิกถอนสิทธิ์ทั้งหมดจากผู้ใช้นี้ใช่หรือไม่?')) {
                revokeAllRoles(userId);
            }
        });
    });

    // Search functionality
    const searchInput = document.querySelector('[data-kt-user-table-filter="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#kt_table_users_permissions tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});

function grantRole(userId, role) {
    fetch('<?= base_url('admin/grant-role') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `user_id=${userId}&role_type=${role}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            location.reload(); // รีโหลดหน้าเพื่อแสดงการเปลี่ยนแปลง
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'เกิดข้อผิดพลาด');
        console.error('Error:', error);
    });
}

function revokeRole(userId, role) {
    fetch('<?= base_url('admin/revoke-role') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `user_id=${userId}&role_type=${role}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            location.reload();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'เกิดข้อผิดพลาด');
        console.error('Error:', error);
    });
}

function revokeAllRoles(userId) {
    const roles = ['Reporter', 'Approver', 'Admin'];
    let promises = [];

    roles.forEach(role => {
        promises.push(
            fetch('<?= base_url('admin/revoke-role') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `user_id=${userId}&role_type=${role}`
            })
        );
    });

    Promise.all(promises).then(() => {
        showAlert('success', 'เพิกถอนสิทธิ์ทั้งหมดสำเร็จ');
        location.reload();
    });
}

function getUserRoleNames(role) {
    const roleMap = {
        'Admin': 'ผู้ดูแลระบบ',
        'Approver': 'ผู้อนุมัติ',
        'Reporter': 'ผู้รายงาน'
    };
    return roleMap[role] || role;
}

function showAlert(type, message) {
    // ใช้ SweetAlert หรือ notification library ที่มีในระบบ
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type === 'success' ? 'สำเร็จ!' : 'ข้อผิดพลาด!',
            text: message,
            icon: type,
            confirmButtonText: 'ตกลง'
        });
    } else {
        alert(message);
    }
}
</script>