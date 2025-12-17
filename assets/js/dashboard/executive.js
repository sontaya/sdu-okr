"use strict";

// Executive Dashboard JavaScript
var ExecutiveDashboard = function () {

    // Private variables (Mock version - fixed values)
    var refreshInterval;
    var currentYear = '2568';
    var currentQuarter = '4';

    // Get mode from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentMode = urlParams.get('mode');

    // Private functions
    var initSelectors = function () {
        // Disable selectors for mock version (fixed Q4 2568)
        $('#yearSelector, #quarterSelector').prop('disabled', true);

        // Refresh button (mock refresh)
        $('#refreshData').on('click', function () {
            // Simulate refresh with animation
            $(this).addClass('spinner spinner-sm spinner-white').prop('disabled', true);

            setTimeout(function () {
                $('#refreshData').removeClass('spinner spinner-sm spinner-white').prop('disabled', false);

                // Add pulse animation to cards
                $('.card').addClass('animate__animated animate__pulse');
                setTimeout(function () {
                    $('.card').removeClass('animate__animated animate__pulse');
                }, 1000);

                // Show success message
                Swal.fire({
                    text: "ข้อมูลได้รับการอัพเดทแล้ว",
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false
                });
            }, 1000);
        });


    };

    var refreshDashboard = function (forceRefresh = false) {
        if (forceRefresh) {
            KTApp.showPageLoading();
        }

        // Build URL with current filters
        var url = '/dashboard/executive?year=' + currentYear;
        if (currentQuarter) {
            url += '&quarter=' + currentQuarter;
        }

        if (forceRefresh) {
            // Full page reload for force refresh
            window.location.href = url;
        } else {
            // AJAX refresh for specific components
            refreshOverviewData();
            // loadTrendChart($('#trendType').val()); // Removed as Trends section is removed
            refreshDepartmentList();
        }
    };

    var refreshOverviewData = function () {
        $.ajax({
            url: '/dashboard/api/overview',
            method: 'GET',
            data: {
                year: currentYear,
                quarter: currentQuarter,
                mode: currentMode
            },
            success: function (response) {
                if (response.success && response.data) {
                    updateOverviewDisplay(response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing overview data:', error);
            }
        });
    };

    var updateOverviewDisplay = function (data) {
        // Update overall progress
        $('#overallProgress').text(Math.round(data.overall_progress || 0) + '%');

        // Update status counts (you can add more specific selectors)
        // This would require adding IDs to the status elements in the HTML

        // Add animation
        $('#overallProgress').addClass('animate__animated animate__pulse');
        setTimeout(function () {
            $('#overallProgress').removeClass('animate__animated animate__pulse');
        }, 1000);
    };

    var refreshDepartmentList = function () {
        $.ajax({
            url: '/dashboard/api/departments',
            method: 'GET',
            data: {
                year: currentYear,
                quarter: currentQuarter,
                limit: 10,
                sort: 'progress_desc',
                mode: currentMode
            },
            success: function (response) {
                if (response.success && response.data) {
                    updateDepartmentList(response.data);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error refreshing department data:', error);
            }
        });
    };

    var updateDepartmentList = function (departments) {
        var container = $('#departmentList');
        var html = '';

        departments.forEach(function (dept, index) {
            var progressClass = dept.avg_progress >= 80 ? 'bg-success' :
                (dept.avg_progress >= 60 ? 'bg-warning' : 'bg-danger');

            var badgeHtml = '';
            if (dept.avg_progress >= 80) {
                badgeHtml = '<span class="badge badge-light-success fw-bold">Excellent</span>';
            } else if (dept.avg_progress >= 60) {
                badgeHtml = '<span class="badge badge-light-warning fw-bold">Good</span>';
            } else {
                badgeHtml = '<span class="badge badge-light-danger fw-bold">Needs Improv.</span>';
            }

            html += `
                <tr>
                    <td>
                        <div class="symbol symbol-35px">
                            <span class="symbol-label fw-bold bg-light-primary text-primary">${index + 1}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <a href="#" class="text-dark fw-bold text-hover-primary fs-6">${dept.short_name}</a>
                            <span class="text-muted fw-semibold fs-7">${dept.name}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-light fw-bold fs-7">${dept.total_key_results}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-light-info fs-8 me-1" title="Leader">${dept.leader_count} L</span>
                        <span class="badge badge-light-success fs-8" title="Co-Working">${dept.coworking_count} C</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-6 me-2">${Math.round(dept.avg_progress * 10) / 10}%</span>
                            <div class="progress h-6px w-100 bg-light">
                                <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${dept.avg_progress}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-end">
                        ${badgeHtml}
                    </td>
                </tr>
            `;
        });

        container.html(html);
    };

    var initAutoRefresh = function () {
        // Auto refresh every 5 minutes
        refreshInterval = setInterval(function () {
            refreshOverviewData();
        }, 300000); // 5 minutes
    };

    // Public methods
    return {
        currentYear: currentYear,
        currentQuarter: currentQuarter,
        currentMode: currentMode,

        init: function () {
            initSelectors();
            initAutoRefresh();
        },

        refresh: function () {
            refreshDashboard(true);
        },

        destroy: function () {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }

        }
    };
}();

// Global functions (accessible from HTML)
window.sortDepartments = function (sortType) {
    $.ajax({
        url: '/dashboard/api/departments',
        method: 'GET',
        data: {
            year: ExecutiveDashboard.currentYear || '2568',
            quarter: ExecutiveDashboard.currentQuarter || '',
            limit: 10,
            sort: sortType,
            mode: ExecutiveDashboard.currentMode || ''
        },
        success: function (response) {
            if (response.success && response.data) {
                updateDepartmentList(response.data);
            }
        }
    });
};

window.viewKeyResult = function (keyResultId) {
    KTApp.showPageLoading();

    $.ajax({
        url: '/dashboard/api/keyresult/' + keyResultId,
        method: 'GET',
        data: {
            year: ExecutiveDashboard.currentYear || '2568',
            quarter: ExecutiveDashboard.currentQuarter || ''
        },
        success: function (response) {
            if (response.success && response.data) {
                showKeyResultModal(response.data);
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                text: "เกิดข้อผิดพลาดในการโหลดข้อมูล Key Result",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "ตกลง!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        },
        complete: function () {
            KTApp.hidePageLoading();
        }
    });
};

window.showKeyResultModal = function (data) {
    var modalBody = document.getElementById('keyResultModalBody');

    var html = `
        <div class="row g-5">
            <div class="col-md-8">
                <div class="d-flex flex-column">
                    <h3 class="fw-bold text-gray-900 mb-3">${data.name}</h3>
                    <div class="text-muted fw-semibold fs-6 mb-5">${data.template_name}</div>

                    <div class="row g-3 mb-5">
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light-primary rounded">
                                <div class="fs-2x fw-bold text-primary">${Math.round(data.progress_percentage || 0)}%</div>
                                <div class="fs-7 text-muted">ความคืบหน้า</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light-info rounded">
                                <div class="fs-2x fw-bold text-info">${data.target_value || 'N/A'}</div>
                                <div class="fs-7 text-muted">เป้าหมาย</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light-success rounded">
                                <div class="fs-7 fw-bold text-success">${data.last_progress_update ? new Date(data.last_progress_update).toLocaleDateString('th-TH') : 'N/A'}</div>
                                <div class="fs-7 text-muted">อัพเดทล่าสุด</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="fw-bold text-gray-900 mb-3">Strategic Context</h6>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-light-primary me-2">${data.objective_group_name}</span>
                            <i class="ki-duotone ki-arrow-right fs-4 text-muted me-2"><span class="path1"></span><span class="path2"></span></i>
                            <span class="text-muted fs-6">${data.objective_name}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-flush">
                    <div class="card-header">
                        <h6 class="card-title">หน่วยงานที่เกี่ยวข้อง</h6>
                    </div>
                    <div class="card-body pt-0">
    `;

    if (data.departments && data.departments.length > 0) {
        data.departments.forEach(function (dept) {
            var badgeClass = dept.role === 'Leader' ? 'badge-light-primary' : 'badge-light-secondary';
            html += `
                <div class="d-flex align-items-center mb-3">
                    <span class="badge ${badgeClass} me-2">${dept.role}</span>
                    <span class="fw-semibold fs-6">${dept.short_name}</span>
                </div>
            `;
        });
    }

    html += `
                    </div>
                </div>

                <div class="mt-5">
                    <h6 class="fw-bold text-gray-900 mb-3">Progress History</h6>
                    <div class="timeline-label">
    `;

    if (data.progress_history && data.progress_history.length > 0) {
        data.progress_history.forEach(function (history) {
            html += `
                <div class="timeline-item">
                    <div class="timeline-label fw-bold text-gray-800 fs-7">${history.quarter_name}</div>
                    <div class="timeline-content">
                        <span class="fw-bold text-primary">${Math.round(history.progress_percentage)}%</span>
                        <span class="text-muted fs-7 ms-2">${new Date(history.updated_date).toLocaleDateString('th-TH')}</span>
                    </div>
                </div>
            `;
        });
    } else {
        html += '<div class="text-muted fs-6">ยังไม่มีประวัติการรายงาน</div>';
    }

    html += `
                    </div>
                </div>
            </div>
        </div>
    `;

    modalBody.innerHTML = html;

    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('keyResultModal'));
    modal.show();
};

window.assignAction = function (keyResultId) {
    Swal.fire({
        title: 'Assign Action Item',
        text: 'คุณต้องการกำหนดการดำเนินการสำหรับ Key Result นี้หรือไม่?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ใช่, กำหนดเลย!',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Here you would typically open an action assignment modal
            // or redirect to an action management page
            window.location.href = '/keyresult/view/' + keyResultId;
        }
    });
};

window.exportDashboard = function () {
    Swal.fire({
        title: 'Export Dashboard Report',
        html: `
            <div class="mb-5">
                <label class="form-label">รูปแบบการ Export:</label>
                <select class="form-select" id="exportType">
                    <option value="excel">Excel (.xlsx)</option>
                    <option value="pdf">PDF (.pdf)</option>
                    <option value="csv">CSV (.csv)</option>
                </select>
            </div>
            <div class="mb-5">
                <label class="form-label">ขอบเขตข้อมูล:</label>
                <select class="form-select" id="exportScope">
                    <option value="overview">ภาพรวมเท่านั้น</option>
                    <option value="strategic">Strategic Goals</option>
                    <option value="department">รายหน่วยงาน</option>
                    <option value="all">ทั้งหมด</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Export',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        preConfirm: () => {
            const exportType = document.getElementById('exportType').value;
            const exportScope = document.getElementById('exportScope').value;

            if (!exportType || !exportScope) {
                Swal.showValidationMessage('กรุณาเลือกรูปแบบและขอบเขตการ Export');
                return false;
            }

            return { exportType, exportScope };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { exportType, exportScope } = result.value;

            // Create download link
            const downloadUrl = `/dashboard/api/export?year=${ExecutiveDashboard.currentYear || '2568'}&quarter=${ExecutiveDashboard.currentQuarter || ''}&type=${exportType}&scope=${exportScope}`;

            // Trigger download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            Swal.fire({
                title: 'สำเร็จ!',
                text: 'กำลังดาวน์โลดรายงาน...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
};

// On document ready
KTUtil.onDOMContentLoaded(function () {
    ExecutiveDashboard.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function () {
    ExecutiveDashboard.destroy();
});