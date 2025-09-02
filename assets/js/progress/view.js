"use strict";

// Class definition
var KTProgressView = function () {

    // Handle view progress details
    var handleViewProgress = function () {
        const viewButtons = document.querySelectorAll('.view-progress-btn');
        const modal = document.querySelector('#kt_modal_view_progress');
        const modalContent = document.querySelector('#progress-details-content');

        if (!modal || !modalContent) return;

        viewButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const progressId = this.getAttribute('data-progress-id');

                // Show loading
                modalContent.innerHTML = `
                    <div class="text-center py-10">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <div class="mt-3">กำลังโหลดข้อมูล...</div>
                    </div>
                `;

                // Show modal
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();

                // Fetch progress details
                fetch(BASE_URL + 'progress/get-details/' + progressId, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderProgressDetails(data.progress);
                    } else {
                        modalContent.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="ki-outline ki-information-5 fs-3x text-danger"></i>
                                <div class="mt-3">${data.message}</div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="ki-outline ki-cross-circle fs-3x text-danger"></i>
                            <div class="mt-3">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>
                        </div>
                    `;
                });
            });
        });

        // Handle modal close
        const closeButton = modal.querySelector('[data-kt-progress-modal-action="close"]');
        if (closeButton) {
            closeButton.addEventListener('click', function () {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
            });
        }
    };

    // Render progress details in modal
    var renderProgressDetails = function (progress) {
        const modalContent = document.querySelector('#progress-details-content');

        const statusConfig = {
            'draft': { badge: 'badge-light-warning', text: 'ฉบับร่าง' },
            'submitted': { badge: 'badge-light-info', text: 'ส่งรายงานแล้ว' },
            'approved': { badge: 'badge-light-success', text: 'อนุมัติแล้ว' },
            'rejected': { badge: 'badge-light-danger', text: 'ปฏิเสธ' }
        };

        const config = statusConfig[progress.status] || statusConfig['draft'];

        let html = `
            <div class="d-flex flex-column gap-7">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="fw-bold text-gray-800 mb-2">${escapeHtml(progress.quarter_name)} ปี ${progress.year}</h3>
                        <div class="text-muted fs-6">เวอร์ชัน ${progress.version}</div>
                    </div>
                    <div class="text-end">
                        <div class="badge ${config.badge} fs-base mb-2">${config.text}</div>
                        <div class="fs-2x fw-bold text-success">${parseFloat(progress.progress_percentage || 0).toFixed(1)}%</div>
                        <div class="text-muted fs-7">${progress.progress_value || 0} / ${progress.target_value || 0} ${progress.target_unit || ''}</div>
                    </div>
                </div>

                <!-- Progress Description -->
                ${progress.progress_description ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title">รายละเอียดความคืบหน้า</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-gray-700">${progress.progress_description}</div>
                    </div>
                </div>
                ` : ''}

                <!-- Challenges -->
                ${progress.challenges ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title text-warning">อุปสรรคและปัญหา</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-gray-700">${progress.challenges}</div>
                    </div>
                </div>
                ` : ''}

                <!-- Solutions -->
                ${progress.solutions ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title text-info">แนวทางแก้ไข</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-gray-700">${progress.solutions}</div>
                    </div>
                </div>
                ` : ''}

                <!-- Next Actions -->
                ${progress.next_actions ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title text-primary">แผนการดำเนินงานต่อไป</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-gray-700">${progress.next_actions}</div>
                    </div>
                </div>
                ` : ''}

                <!-- Related Entries -->
                ${progress.entries && progress.entries.length > 0 ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title">รายการข้อมูลที่เกี่ยวข้อง</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            ${progress.entries.map(entry => `
                                <div class="badge badge-light-info fs-7 p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-document fs-6 me-2"></i>
                                        <div>
                                            <div class="fw-bold">${escapeHtml(entry.entry_name)}</div>
                                            <div class="text-muted fs-8">${escapeHtml(entry.entry_status)}</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Files -->
                ${progress.files && progress.files.length > 0 ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title">เอกสารแนบ</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            ${progress.files.map(file => `
                                <div class="d-flex align-items-center">
                                    <i class="ki-outline ki-document text-primary fs-2x me-4"></i>
                                    <div class="flex-grow-1">
                                        <a href="${BASE_URL}/${file.file_path}" target="_blank" class="fw-bold text-gray-800 text-hover-primary">
                                            ${escapeHtml(file.original_name || file.file_name)}
                                        </a>
                                        <div class="text-muted fs-7">
                                            อัปโหลดเมื่อ: ${formatDate(file.uploaded_date)}
                                            ${file.file_size ? ` | ขนาด: ${formatFileSize(file.file_size)}` : ''}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Comments -->
                ${progress.comments && progress.comments.length > 0 ? `
                <div class="card card-flush">
                    <div class="card-header">
                        <h4 class="card-title">ความคิดเห็น</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-5">
                            ${progress.comments.map(comment => `
                                <div class="d-flex">
                                    <div class="symbol symbol-50px me-5">
                                        <div class="symbol-label bg-light-primary text-primary fw-bold">
                                            ${comment.commenter_role.charAt(0).toUpperCase()}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="fw-bold text-gray-800">${comment.commenter_role}</div>
                                            <div class="text-muted fs-7">${formatDate(comment.created_date)}</div>
                                        </div>
                                        <div class="text-gray-700">${escapeHtml(comment.comment_text)}</div>
                                        <div class="badge badge-light-${getCommentTypeColor(comment.comment_type)} fs-8 mt-2">
                                            ${getCommentTypeText(comment.comment_type)}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        modalContent.innerHTML = html;
    };

    // Handle submit progress
    var handleSubmitProgress = function () {
        const submitButtons = document.querySelectorAll('.submit-progress-btn');

        submitButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const progressId = this.getAttribute('data-progress-id');

                Swal.fire({
                    text: "คุณต้องการส่งรายงานนี้เพื่อขออนุมัติใช่หรือไม่?",
                    icon: "question",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "ส่งรายงาน",
                    cancelButtonText: "ยกเลิก",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                        cancelButton: "btn fw-bold btn-secondary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.setAttribute('data-kt-indicator', 'on');
                        button.disabled = true;

                        fetch(BASE_URL + '/progress/submit/' + progressId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "เกิดข้อผิดพลาดในการส่งรายงาน",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        })
                        .finally(() => {
                            button.removeAttribute('data-kt-indicator');
                            button.disabled = false;
                        });
                    }
                });
            });
        });
    };

    // Handle approve progress
    var handleApproveProgress = function () {
        const approveButtons = document.querySelectorAll('.approve-progress-btn');

        approveButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const progressId = this.getAttribute('data-progress-id');

                Swal.fire({
                    text: "คุณต้องการอนุมัติรายงานนี้ใช่หรือไม่?",
                    icon: "question",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "อนุมัติ",
                    cancelButtonText: "ยกเลิก",
                    customClass: {
                        confirmButton: "btn fw-bold btn-success",
                        cancelButton: "btn fw-bold btn-secondary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.setAttribute('data-kt-indicator', 'on');
                        button.disabled = true;

                        fetch(BASE_URL + '/progress/approve/' + progressId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "เกิดข้อผิดพลาดในการอนุมัติรายงาน",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        })
                        .finally(() => {
                            button.removeAttribute('data-kt-indicator');
                            button.disabled = false;
                        });
                    }
                });
            });
        });
    };

    // Handle delete progress
    var handleDeleteProgress = function () {
        const deleteButtons = document.querySelectorAll('.delete-progress-btn');

        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const progressId = this.getAttribute('data-progress-id');

                Swal.fire({
                    text: "คุณต้องการลบรายงานนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถยกเลิกได้",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "ลบ",
                    cancelButtonText: "ยกเลิก",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-secondary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.setAttribute('data-kt-indicator', 'on');
                        button.disabled = true;

                        fetch(BASE_URL + 'progress/delete/' + progressId, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-HTTP-Method-Override': 'DELETE'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "ตกลง",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "เกิดข้อผิดพลาดในการลบรายงาน",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        })
                        .finally(() => {
                            button.removeAttribute('data-kt-indicator');
                            button.disabled = false;
                        });
                    }
                });
            });
        });
    };

    // Utility functions
    var escapeHtml = function (text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    };

    var formatDate = function (dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('th-TH', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    var formatFileSize = function (bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    var getCommentTypeColor = function (type) {
        const colors = {
            'feedback': 'info',
            'suggestion': 'warning',
            'question': 'primary',
            'approval_note': 'success'
        };
        return colors[type] || 'secondary';
    };

    var getCommentTypeText = function (type) {
        const texts = {
            'feedback': 'ข้อเสนอแนะ',
            'suggestion': 'คำแนะนำ',
            'question': 'คำถาม',
            'approval_note': 'หมายเหตุการอนุมัติ'
        };
        return texts[type] || 'ความคิดเห็น';
    };

    // Public methods
    return {
        init: function () {
            handleViewProgress();
            handleSubmitProgress();
            handleApproveProgress();
            handleDeleteProgress();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProgressView.init();
});