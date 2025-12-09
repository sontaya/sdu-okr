// File: assets/js/strategic/overview.js
// Strategic Dashboard JavaScript

"use strict";

var StrategicDashboard = function () {
    var table;
    var filterForm;

    // Initialize DataTable
    var initTable = function () {
        table = $('#kt_strategic_table').DataTable({
            "info": true,
            "order": [], // Sort by Key Result name
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "columnDefs": [
                { "orderable": false, "targets": [0, 6] }, // Disable ordering on checkbox and actions
                { "searchable": false, "targets": [0, 6] }
            ],
            "language": {
                "search": "ค้นหา:",
                "lengthMenu": "แสดง _MENU_ รายการ",
                "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(กรองจาก _MAX_ รายการทั้งหมด)",
                "paginate": {
                    "first": "หน้าแรก",
                    "last": "หน้าสุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                },
                "emptyTable": "ไม่มีข้อมูลในตาราง"
            },
            "responsive": true,
            "dom": '<"top"<"d-flex justify-content-between"<"d-flex align-items-center gap-3"l><"d-flex align-items-center gap-2"f>>>t<"bottom"<"d-flex justify-content-between"ip>>',
        });

        // Custom search
        $('#kt-strategic-search').on('keyup change', function () {
            table.search(this.value).draw();
        });

        // Handle view mode toggle
        $('input[name="view_mode"]').change(function() {
            if ($(this).val() === 'cards') {
                $('#strategic-table-view').addClass('d-none');
                $('#strategic-cards-view').removeClass('d-none');
                generateCardsView();
            } else {
                $('#strategic-table-view').removeClass('d-none');
                $('#strategic-cards-view').addClass('d-none');
            }
        });
    };

    // Initialize Filters
    var initFilters = function () {
        filterForm = $('#strategic-filters-form');

        // Auto-submit on filter change
        filterForm.find('select, input').change(function() {
            if ($(this).attr('name') !== 'search') {
                filterForm.submit();
            }
        });

        // Clear filters
        $('#btn-clear-filters').click(function() {
            filterForm.find('select').val('').trigger('change');
            filterForm.find('input[name="search"]').val('');
            filterForm.submit();
        });

        // Enhanced filtering with DataTable
        setupAdvancedFilters();
    };

    // Setup advanced client-side filtering
    var setupAdvancedFilters = function() {
        // Custom filter for department
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var selectedDept = $('select[name="dept"]').val();
            var selectedGroup = $('select[name="group"]').val();
            var selectedStatus = $('select[name="status"]').val();

            var row = $(settings.nTable).DataTable().row(dataIndex).node();
            var rowDept = $(row).data('dept');
            var rowGroup = $(row).data('group');
            var rowStatus = $(row).data('status');

            // Check department filter
            if (selectedDept && selectedDept != rowDept) {
                return false;
            }

            // Check objective group filter
            if (selectedGroup && selectedGroup != rowGroup) {
                return false;
            }

            // Check status filter
            if (selectedStatus && selectedStatus != rowStatus) {
                return false;
            }

            return true;
        });

        // Trigger redraw when filters change
        $('select[name="dept"], select[name="group"], select[name="status"]').change(function() {
            if (table) {
                table.draw();
            }
        });
    };

    // Generate Cards View
    var generateCardsView = function() {
        var cardsContainer = $('#strategic-cards-view .row');
        cardsContainer.empty();

        var tableData = table.rows({search: 'applied'}).data();

        tableData.each(function(rowData, index) {
            var row = table.row(index).node();
            var card = generateCardHTML(row);
            cardsContainer.append(card);
        });
    };

    // Generate individual card HTML
    var generateCardHTML = function(row) {
        // Extract data from table row
        var $row = $(row);
        var keyResultId = $row.find('input[type="checkbox"]').val();
        var dept = $row.data('dept');
        var group = $row.data('group');
        var status = $row.data('status');

        // This is a simplified version - you'd extract more data from the row
        return `
        <div class="col-md-6 col-lg-4 mb-6">
            <div class="card card-flush">
                <div class="card-body">
                    <h5 class="card-title">Key Result ${keyResultId}</h5>
                    <p class="card-text">Department: ${dept}</p>
                    <p class="card-text">Status: ${status}</p>
                    <button class="btn btn-primary btn-sm" onclick="viewKeyResultDetails(${keyResultId})">
                        View Details
                    </button>
                </div>
            </div>
        </div>
        `;
    };

    // Export functionality
    var initExport = function() {
        $('#btn-export-excel').click(function() {
            exportData('excel');
        });
    };

    // Export data
    var exportData = function(format) {
        var currentUrl = new URL(window.location);
        currentUrl.pathname = BASE_URL + 'strategic/export';
        currentUrl.searchParams.set('format', format);

        // Add current filters to export URL
        var filters = new FormData(filterForm[0]);
        for (let [key, value] of filters) {
            if (value) {
                currentUrl.searchParams.set(key, value);
            }
        }

        // Show loading
        Swal.fire({
            title: 'กำลังเตรียมไฟล์...',
            text: 'กรุณารอสักครู่',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Make AJAX request
        $.get(currentUrl.toString())
            .done(function(response) {
                Swal.close();
                if (response.success) {
                    // Simulate download - in real implementation, you'd handle actual file download
                    Swal.fire('สำเร็จ!', 'ไฟล์พร้อมสำหรับดาวน์โหลด: ' + response.filename, 'success');
                } else {
                    Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถส่งออกข้อมูลได้', 'error');
                }
            })
            .fail(function() {
                Swal.close();
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
            });
    };

    // Refresh data
    var initRefresh = function() {
        $('#btn-refresh-data').click(function() {
            location.reload();
        });
    };

    // Initialize tooltips and popovers
    var initBootstrapComponents = function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize Select2
        $('[data-control="select2"]').select2({
            minimumResultsForSearch: Infinity
        });
    };

    // Public methods
    return {
        init: function () {
            initTable();
            initFilters();
            initExport();
            initRefresh();
            initBootstrapComponents();
        }
    };
}();


// Initialize when DOM is ready
$(document).ready(function () {
    StrategicDashboard.init();
});

// Real-time updates (optional)
$(document).ready(function() {
    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        refreshStats();
    }, 300000); // 5 minutes
});

function refreshStats() {
    var currentUrl = new URL(window.location);
    currentUrl.pathname = BASE_URL + 'strategic/api';
    currentUrl.searchParams.set('action', 'refresh_stats');

    $.get(currentUrl.toString())
        .done(function(response) {
            if (response.success) {
                updateStatsCards(response.stats);
            }
        })
        .fail(function() {
            console.log('Failed to refresh stats');
        });
}

function updateStatsCards(stats) {
    // Update summary cards with new data
    $('.fs-1.fw-bold.text-primary').first().text(stats.total_key_results);
    $('.fs-1.fw-bold.text-success').text(stats.progress_summary.avg_progress + '%');
    $('.fs-1.fw-bold.text-info').text(stats.progress_summary.on_track);
    $('.fs-1.fw-bold.text-warning').text(stats.reporting_activity.last_7_days);
}