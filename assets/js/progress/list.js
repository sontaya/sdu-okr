"use strict";

// Class definition
var KTProgressList = function () {
    // Shared variables
    var table;
    var datatable;
    var filterStatus;

    // Private functions
    var initDatatable = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            "pageLength": 10,
            "lengthChange": false,
            'columnDefs': [
                { orderable: false, targets: 0 }, // Disable ordering on column 0 (checkbox)
                { orderable: false, targets: 5 }, // Disable ordering on column 5 (actions)
            ]
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-progress-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        filterStatus = document.querySelector('[data-kt-progress-filter="status"]');

        // Filter datatable on submit
        filterStatus.addEventListener('change', function () {
            let filterValue = this.value;

            if (filterValue === 'all') {
                filterValue = '';
            }

            datatable.column(3).search(filterValue).draw();
        });
    }

    // Reset Filter
    var handleResetFilter = function () {
        // Select reset button
        const resetButton = document.querySelector('[data-kt-progress-filter="reset"]');

        if (resetButton) {
            // Reset datatable
            resetButton.addEventListener('click', function () {
                // Reset payment type
                filterStatus.val(null).trigger('change');

                // Reset datatable --- official docs reference: https://datatables.net/reference/api/search()
                datatable.search('').draw();
            });
        }
    }

    // Delete progress
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = document.querySelectorAll('[data-kt-progress-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get progress name
                const progressName = parent.querySelectorAll('td')[1].innerText;
                const progressId = this.getAttribute('data-progress-id');

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "คุณแน่ใจหรือไม่ที่จะลบ " + progressName + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "ใช่, ลบเลย!",
                    cancelButtonText: "ไม่, ยกเลิก",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Send delete request
                        fetch(base_url + '/progress/delete/' + progressId, {
                            method: 'POST',
                            headers: {
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
                                    confirmButtonText: "ตกลง!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // Remove current row
                                    datatable.row($(parent)).remove().draw();
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
                                text: "เกิดข้อผิดพลาดในการลบข้อมูล",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        });
                    } else if (result.dismiss === 'cancel') {
                        Swal.fire({
                            text: progressName + " ไม่ได้ถูกลบ.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
        });
    }

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const container = document.querySelector('#kt_progress_table');
        const checkboxes = container.querySelectorAll('[type="checkbox"]');

        // Select elements
        const deleteSelected = document.querySelector('[data-kt-progress-filter="delete_selected"]');

        // Toggle delete selected toolbar
        checkboxes.forEach(c => {
            // Checkbox on click event
            c.addEventListener('click', function () {
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });

        // Deleted selected rows
        if (deleteSelected) {
            deleteSelected.addEventListener('click', function () {
                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "คุณแน่ใจหรือไม่ที่จะลบรายการที่เลือก?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "ใช่, ลบเลย!",
                    cancelButtonText: "ไม่, ยกเลิก",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Simulate delete request -- for demo purpose only
                        Swal.fire({
                            text: "คุณได้ลบรายการที่เลือกแล้ว!.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        }).then(function () {
                            // Remove all selected customers
                            checkboxes.forEach(c => {
                                if (c.checked) {
                                    datatable.row($(c.closest('tbody tr'))).remove().draw();
                                }
                            });

                            // Remove header checked box
                            const headerCheckbox = container.querySelectorAll('[type="checkbox"]')[0];
                            headerCheckbox.checked = false;
                        });
                    } else if (result.dismiss === 'cancel') {
                        Swal.fire({
                            text: "รายการที่เลือกไม่ได้ถูกลบ.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            });
        }
    }

    const toggleToolbars = function () {
        // Define variables
        const container = document.querySelector('#kt_progress_table');
        const toolbarBase = document.querySelector('[data-kt-progress-filter="base"]');
        const toolbarSelected = document.querySelector('[data-kt-progress-filter="selected"]');
        const selectedCount = document.querySelector('[data-kt-progress-filter="selected_count"]');

        // Select refreshed checkbox DOM elements
        const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');

        // Detect checkboxes state & count
        let checkedState = false;
        let count = 0;

        // Count checked boxes
        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        // Toggle toolbars
        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_progress_table');

            if (!table) {
                return;
            }

            initDatatable();
            handleSearchDatatable();
            handleFilterDatatable();
            handleDeleteRows();
            handleResetFilter();
            initToggleToolbar();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProgressList.init();
});