"use strict";

var KTKeyresultView = function () {
    var table;
    var datatable;

    // Initialize table
    var initTable = function () {
        table = document.querySelector('#kt_entries_table');

        if (!table) {
            return;
        }

        // ✅ ตรวจสอบว่ามีข้อมูลในตารางหรือไม่
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const hasData = rows.length > 0 && !rows[0].querySelector('td[colspan]');

        if (!hasData) {
            console.log('No data found, skipping DataTable initialization');
            return;
        }

        // Initialize datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
            'columnDefs': [
                { orderable: false, targets: 0 }, // Disable ordering on column 0 (checkbox)
                { orderable: false, targets: 6 }, // Disable ordering on column 6 (actions)
            ]
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-entries-filter="search"]');
        if (!filterSearch || !datatable) return;

        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Filter Datatable
    var handleFilterDatatable = () => {
        // Select filter options
        const filterStatus = document.querySelector('[data-kt-entries-filter="status"]');
        if (!filterStatus || !datatable) return;

        // Filter datatable on submit
        filterStatus.addEventListener('change', function () {
            let filterValue = this.value;

            if (filterValue === 'all') {
                filterValue = '';
            }

            datatable.column(4).search(filterValue).draw();
        });
    }

    // Handle delete entry buttons with AJAX
    var handleDeleteEntryAjax = () => {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-entry-btn')) {
                e.preventDefault();

                const entryId = e.target.getAttribute('data-entry-id');
                const row = e.target.closest('tr');
                const entryName = row.querySelector('td:nth-child(2) a').textContent.trim();

                Swal.fire({
                    title: 'แน่ใจหรือไม่?',
                    text: "ต้องการลบรายการ '" + entryName + "' ใช่หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ✅ เปลี่ยนจาก POST เป็น DELETE
                        fetch(BASE_URL + '/keyresult/delete-entry/' + entryId, {
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('ลบแล้ว!', 'รายการถูกลบเรียบร้อยแล้ว', 'success')
                                .then(() => {
                                    // Remove row from datatable
                                    if (datatable) {
                                        datatable.row(row).remove().draw();
                                    } else {
                                        row.remove();
                                    }

                                    // Reload หากไม่มีข้อมูลเหลือ
                                    const remainingRows = document.querySelectorAll('#kt_entries_table tbody tr');
                                    if (remainingRows.length === 0) {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire('ข้อผิดพลาด!', data.message || 'ไม่สามารถลบรายการได้', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('ข้อผิดพลาด!', 'เกิดข้อผิดพลาดในการลบรายการ', 'error');
                        });
                    }
                });
            }
        });
    };

        // Public methods
        return {
            init: function () {
                initTable();
                handleSearchDatatable();
                handleFilterDatatable();
                handleDeleteEntryAjax();
            }
        }
    }();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTKeyresultView.init();
});