// Enhanced manage-permissions.js with eProfile API integration

document.addEventListener('DOMContentLoaded', function() {
    initializeEventHandlers();
    initializeEprofileModal();
});

function initializeEventHandlers() {
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

    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            openEditUserModal(userId);
        });
    });

    // Department Filter
    const departmentFilter = document.getElementById('department-filter');
    if (departmentFilter) {
        departmentFilter.addEventListener('change', function() {
            filterTable();
        });
    }

    // Search functionality
    const searchInput = document.querySelector('[data-kt-user-table-filter="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            filterTable();
        });
    }
}

function filterTable() {
    const searchFilter = document.querySelector('[data-kt-user-table-filter="search"]').value.toLowerCase();
    const departmentFilter = document.getElementById('department-filter').value;
    const rows = document.querySelectorAll('#kt_table_users_permissions tbody tr');

    let visibleCount = 0;

    rows.forEach(row => {
        const userId = row.dataset.userId;
        const text = row.textContent.toLowerCase();
        const departmentCell = row.querySelector('td:nth-child(2)'); // คอลัมน์หน่วยงาน

        // ตรวจสอบ search filter
        const matchesSearch = text.includes(searchFilter);

        // ตรวจสอบ department filter
        let matchesDepartment = true;
        if (departmentFilter) {
            const rowDepartmentId = row.dataset.departmentId;
            matchesDepartment = (rowDepartmentId === departmentFilter);
        }

        // แสดง/ซ่อนแถว
        const shouldShow = matchesSearch && matchesDepartment;
        row.style.display = shouldShow ? '' : 'none';

        if (shouldShow) visibleCount++;
    });

    // แสดงจำนวนผลลัพธ์ (ถ้าต้องการ)
    updateResultsCount(visibleCount);
}

function updateResultsCount(count) {
    let countElement = document.getElementById('results-count');
    if (!countElement) {
        // สร้าง element ใหม่หากยังไม่มี
        countElement = document.createElement('div');
        countElement.id = 'results-count';
        countElement.className = 'text-muted fs-7 mt-2';

        const cardHeader = document.querySelector('.card-header');
        cardHeader.appendChild(countElement);
    }

    countElement.textContent = count > 0 ? `แสดง ${count} รายการ` : 'ไม่พบข้อมูลที่ตรงตามเงื่อนไข';
}

function initializeEprofileModal() {
    const modal = document.getElementById('kt_modal_add_user');
    const searchBtn = document.getElementById('search-eprofile-btn');
    const searchInput = document.getElementById('eprofile-search');
    const backBtn = document.getElementById('back-to-search');
    const addUserForm = document.getElementById('kt_modal_add_user_form');

    const editUserForm = document.getElementById('kt_modal_edit_user_form');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitEditUser();
        });
    }


    // Reset modal when opened
    modal.addEventListener('show.bs.modal', function() {
        resetModal();
    });

    // Search button click
    searchBtn.addEventListener('click', function() {
        const searchKey = searchInput.value.trim();
        if (searchKey.length < 2) {
            showAlert('warning', 'กรุณาใส่คำค้นหาอย่างน้อย 2 ตัวอักษร');
            return;
        }
        searchEprofileUsers(searchKey);
    });

    // Search on Enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // Back to search
    backBtn.addEventListener('click', function() {
        showSearchStep();
    });

    // Form submission
    addUserForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddUser();
    });
}

function openEditUserModal(userId) {
    fetch(BASE_URL + 'admin/edit-user/' + userId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateEditModal(data.user, data.current_roles);
            const modal = new bootstrap.Modal(document.getElementById('kt_modal_edit_user'));
            modal.show();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
        console.error('Edit user error:', error);
    });
}

function populateEditModal(user, currentRoles) {
    // แสดงข้อมูลผู้ใช้
    document.getElementById('edit-display-full-name').textContent = user.full_name || '-';
    document.getElementById('edit-faculty').textContent = user.name_faculty || '-';

    // ตั้งค่าหน่วยงาน
    document.getElementById('edit-department-select').value = user.department_id || '';

    // ตั้งค่าสิทธิ์
    document.getElementById('edit_role_reporter').checked = currentRoles.includes('Reporter');
    document.getElementById('edit_role_approver').checked = currentRoles.includes('Approver');
    document.getElementById('edit_role_admin').checked = currentRoles.includes('Admin');

    // เก็บ user ID
    document.getElementById('edit-user-id').value = user.id;
}

function submitEditUser() {
    const form = document.getElementById('kt_modal_edit_user_form');
    const updateBtn = document.getElementById('update-user-btn');
    const userId = document.getElementById('edit-user-id').value;

    const formData = new FormData(form);

    // Show loading state
    updateBtn.querySelector('.indicator-label').style.display = 'none';
    updateBtn.querySelector('.indicator-progress').style.display = 'inline-flex';
    updateBtn.disabled = true;

    fetch(BASE_URL + 'admin/update-user/' + userId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        updateBtn.querySelector('.indicator-label').style.display = 'inline-flex';
        updateBtn.querySelector('.indicator-progress').style.display = 'none';
        updateBtn.disabled = false;

        if (data.success) {
            showAlert('success', data.message);
            // Close modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_edit_user'));
            modal.hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        // Reset button state
        updateBtn.querySelector('.indicator-label').style.display = 'inline-flex';
        updateBtn.querySelector('.indicator-progress').style.display = 'none';
        updateBtn.disabled = false;

        showAlert('error', 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล');
        console.error('Update user error:', error);
    });
}

function resetModal() {
    showSearchStep();
    document.getElementById('eprofile-search').value = '';
    document.getElementById('search-results').style.display = 'none';
    document.getElementById('users-list').innerHTML = '';
    document.getElementById('kt_modal_add_user_form').reset();
}

function showSearchStep() {
    document.getElementById('search-step').style.display = 'block';
    document.getElementById('user-details-step').style.display = 'none';
}

function showUserDetailsStep(userData) {
    // Populate user data
    document.getElementById('display-full-name').textContent = userData.ACADEMIC_FULLNAME_TH || '-';
    // Store user data
    document.getElementById('selected-user-data').value = JSON.stringify(userData);

    // Show step
    document.getElementById('search-step').style.display = 'none';
    document.getElementById('user-details-step').style.display = 'block';
}

function searchEprofileUsers(searchKey) {
    const loadingDiv = document.getElementById('search-loading');
    const resultsDiv = document.getElementById('search-results');

    // Show loading
    loadingDiv.style.display = 'block';
    resultsDiv.style.display = 'none';

    // Make API call
    fetch(BASE_URL + 'admin/search-eprofile-users', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `search_key=${encodeURIComponent(searchKey)}`
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.style.display = 'none';

        if (data.success) {
            displaySearchResults(data.data);
            console.log(data.data); // Debug log
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        loadingDiv.style.display = 'none';
        showAlert('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อ');
        console.error('Search error:', error);
    });
}

function displaySearchResults(users) {
    const resultsDiv = document.getElementById('search-results');
    const usersList = document.getElementById('users-list');

    // เพิ่ม debug info ถ้ามี
    let debugInfo = '';
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        debugInfo = `
            <div class="alert alert-info mb-3">
                <strong>Debug Info:</strong><br>
                ค้นหาเสร็จแล้ว พบ ${users.length} รายการ
            </div>
        `;
    }

    if (users.length === 0) {
        usersList.innerHTML = debugInfo + `
            <div class="text-center py-5">
                <div class="symbol symbol-100px mx-auto mb-4">
                    <div class="symbol-label fs-1 bg-light-warning text-warning">
                        <i class="ki-outline ki-information-2"></i>
                    </div>
                </div>
                <div class="text-muted fs-4 mb-3">ไม่พบผู้ใช้ที่ตรงตามเงื่อนไข</div>
                <div class="text-muted fs-6">
                    กรุณาลองใช้คำค้นหาอื่น เช่น:<br>
                    • ชื่อ หรือ นามสกุล<br>
                    • USER_ID (รหัสพนักงาน)<br>
                    • บางส่วนของชื่อ
                </div>
            </div>
        `;
    } else {
        let html = debugInfo;
        users.forEach(user => {
            const isExisting = user.existing_user;
            html += `
                <div class="card mb-3 ${isExisting ? 'border-warning' : ''}">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            ${user.USER_ID ? user.USER_ID.charAt(0).toUpperCase() : 'U'}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5">${user.ACADEMIC_FULLNAME_TH || 'ไม่ระบุชื่อ'}</div>
                                        <div class="text-muted fs-7"> <i class="ki-outline ki-profile-user fs-6 me-1"></i> ${user.NAME_FACULTY}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="ms-3">
                                ${isExisting ?
                                    '<span class="badge badge-warning">มีในระบบแล้ว</span>' :
                                    '<button type="button" class="btn btn-primary btn-sm select-user-btn" data-user=\'' + JSON.stringify(user) + '\'>เลือกผู้ใช้นี้</button>'
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        usersList.innerHTML = html;

        // Bind select user events
        document.querySelectorAll('.select-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const userData = JSON.parse(this.getAttribute('data-user'));
                showUserDetailsStep(userData);
            });
        });
    }

    resultsDiv.style.display = 'block';
}

function submitAddUser() {
    const form = document.getElementById('kt_modal_add_user_form');
    const addBtn = document.getElementById('add-user-btn');

    // ตรวจสอบว่าเลือกหน่วยงานแล้วหรือไม่
    const departmentSelect = document.getElementById('department-select');
    if (!departmentSelect.value) {
        showAlert('warning', 'กรุณาเลือกหน่วยงาน');
        return;
    }

    const formData = new FormData(form);

    // Show loading state
    addBtn.querySelector('.indicator-label').style.display = 'none';
    addBtn.querySelector('.indicator-progress').style.display = 'inline-flex';
    addBtn.disabled = true;

    fetch(BASE_URL + 'admin/add-user-from-eprofile', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Reset button state
        addBtn.querySelector('.indicator-label').style.display = 'inline-flex';
        addBtn.querySelector('.indicator-progress').style.display = 'none';
        addBtn.disabled = false;

        if (data.success) {
            showAlert('success', data.message);
            // Close modal and refresh page
            const modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_add_user'));
            modal.hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        // Reset button state
        addBtn.querySelector('.indicator-label').style.display = 'inline-flex';
        addBtn.querySelector('.indicator-progress').style.display = 'none';
        addBtn.disabled = false;

        showAlert('error', 'เกิดข้อผิดพลาดในการเพิ่มผู้ใช้');
        console.error('Add user error:', error);
    });
}

// Existing functions remain the same
function grantRole(userId, role) {
    fetch(BASE_URL + 'admin/grant-role', {
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

function revokeRole(userId, role) {
    fetch(BASE_URL + 'admin/revoke-role', {
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
            fetch(BASE_URL + 'admin/revoke-role', {
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
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type === 'success' ? 'สำเร็จ!' : (type === 'warning' ? 'คำเตือน!' : 'ข้อผิดพลาด!'),
            text: message,
            icon: type,
            confirmButtonText: 'ตกลง'
        });
    } else {
        alert(message);
    }
}
