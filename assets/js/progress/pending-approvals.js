const pendingApprovalsElement = document.getElementById('pending-approvals-data');
const pendingApprovals = pendingApprovalsElement ? JSON.parse(pendingApprovalsElement.value) : [];

function getCSRFTokens() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const tokenNameMeta = document.querySelector('meta[name="csrf-token-name"]');

    return {
        token: tokenMeta ? tokenMeta.getAttribute('content') : '',
        tokenName: tokenNameMeta ? tokenNameMeta.getAttribute('content') : 'csrf_test_name'
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // เตรียม CSRF tokens
    const csrf = getCSRFTokens();
    console.log('BASE_URL:', BASE_URL);
    console.log('CSRF Token Name:', csrf.tokenName);
    console.log('CSRF Token:', csrf.token ? 'Found' : 'Not Found');

    // Initialize DataTable
    if (pendingApprovals.length > 0) {
        const table = $('#kt_pending_approvals_table').DataTable({
            responsive: true,
            searchDelay: 500,
            processing: true,
            order: [[4, 'asc']], // เรียงตามวันที่ส่ง
            columnDefs: [
                { orderable: false, targets: 6 } // ไม่ให้เรียงคอลัมน์ Actions
            ],
            language: {
                lengthMenu: "แสดง _MENU_ รายการ",
                zeroRecords: "ไม่พบข้อมูล",
                info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
                infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
                search: "ค้นหา:",
                paginate: {
                    first: "หน้าแรก",
                    last: "หน้าสุดท้าย",
                    next: "ถัดไป",
                    previous: "ก่อนหน้า"
                }
            }
        });

        // Custom search
        $('#search-reports').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Refresh button
        $('#refresh-table').on('click', function() {
            location.reload();
        });
    }

    // Approve button handler
    $(document).on('click', '.approve-btn', function() {
        const progressId = $(this).data('progress-id');
        const keyResultName = $(this).data('key-result');

        $('#approve-key-result-name').text(keyResultName);
        $('#kt_approve_form').data('progress-id', progressId);
        $('#kt_approve_modal').modal('show');
    });

    // Reject button handler
    $(document).on('click', '.reject-btn', function() {
        const progressId = $(this).data('progress-id');
        const keyResultName = $(this).data('key-result');

        $('#reject-key-result-name').text(keyResultName);
        $('#kt_reject_form').data('progress-id', progressId);
        $('#kt_reject_modal').modal('show');
    });

    // Handle approve form submission
    $('#kt_approve_form').on('submit', function(e) {
        e.preventDefault();
        const progressId = $(this).data('progress-id');
        const comment = $('textarea[name="approve_comment"]').val();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.attr('data-kt-indicator', 'on');

        // ✅ สร้าง data object แบบธรรมดา
        const postData = {};
        postData[csrf.tokenName] = csrf.token;
        postData['approve_comment'] = comment;

        console.log('Approve URL:', BASE_URL + '/progress/approve/' + progressId);
        console.log('Approve Data:', postData);

        $.ajax({
            url: BASE_URL + '/progress/approve/' + progressId,
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: postData,
            success: function(response) {
                console.log('Approve Response:', response);
                if (response.success) {
                    Swal.fire({
                        text: response.message || 'อนุมัติรายงานสำเร็จ',
                        icon: 'success',
                        buttonsStyling: false,
                        confirmButtonText: 'ตกลง',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        text: response.message || 'เกิดข้อผิดพลาด',
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: 'ตกลง',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);

                let errorMessage = 'เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์';

                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    if (xhr.status === 0) {
                        errorMessage = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
                    } else if (xhr.status === 404) {
                        errorMessage = 'ไม่พบหน้าที่ต้องการ (404)';
                    } else if (xhr.status === 500) {
                        errorMessage = 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์ (500)';
                    } else {
                        errorMessage += ' (Status: ' + xhr.status + ')';
                    }
                }

                Swal.fire({
                    text: errorMessage,
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: 'ตกลง',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            },
            complete: function() {
                submitBtn.removeAttr('data-kt-indicator');
                $('#kt_approve_modal').modal('hide');
            }
        });
    });

    // Handle reject form submission
    $('#kt_reject_form').on('submit', function(e) {
        e.preventDefault();
        const progressId = $(this).data('progress-id');
        const reason = $('textarea[name="reject_reason"]').val();

        if (!reason.trim()) {
            Swal.fire({
                text: 'กรุณาระบุเหตุผลในการปฏิเสธ',
                icon: 'warning',
                buttonsStyling: false,
                confirmButtonText: 'ตกลง',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
            return;
        }

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.attr('data-kt-indicator', 'on');

        // ✅ สร้าง data object
        const postData = {};
        postData[csrf.tokenName] = csrf.token;
        postData['reject_reason'] = reason;

        console.log('Reject URL:', BASE_URL + '/progress/reject/' + progressId);
        console.log('Reject Data:', postData);

        $.ajax({
            url: BASE_URL + '/progress/reject/' + progressId,
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: postData,
            success: function(response) {
                console.log('Reject Response:', response);
                if (response.success) {
                    Swal.fire({
                        text: response.message || 'ปฏิเสธรายงานสำเร็จ',
                        icon: 'success',
                        buttonsStyling: false,
                        confirmButtonText: 'ตกลง',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        text: response.message || 'เกิดข้อผิดพลาด',
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: 'ตกลง',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);

                let errorMessage = 'เกิดข้อผิดพลาดในการติดต่อเซิร์ฟเวอร์';

                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    if (xhr.status === 0) {
                        errorMessage = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
                    } else if (xhr.status === 404) {
                        errorMessage = 'ไม่พบหน้าที่ต้องการ (404)';
                    } else if (xhr.status === 500) {
                        errorMessage = 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์ (500)';
                    } else {
                        errorMessage += ' (Status: ' + xhr.status + ')';
                    }
                }

                Swal.fire({
                    text: errorMessage,
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: 'ตกลง',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            },
            complete: function() {
                submitBtn.removeAttr('data-kt-indicator');
                $('#kt_reject_modal').modal('hide');
            }
        });
    });
});