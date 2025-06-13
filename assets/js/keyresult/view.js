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

    // Handle view entry details
    var handleViewEntryDetails = () => {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-entry-btn')) {
                e.preventDefault();
                console.log('✅ View button clicked!'); // Debug

                const entryId = e.target.getAttribute('data-entry-id');
                console.log('📝 Entry ID:', entryId); // Debug

                showEntryDetailsModal(entryId);
            }
        });
    };

    // Show entry details in modal
    var showEntryDetailsModal = (entryId) => {
        console.log('🔍 Opening modal for entry:', entryId); // Debug

        const modalElement = document.getElementById('kt_modal_entry_details');
        if (!modalElement) {
            console.error('❌ Modal element not found!');
            return;
        }

        const modal = new bootstrap.Modal(modalElement);
        const loadingDiv = document.getElementById('modal-loading');
        const contentDiv = document.getElementById('modal-content');

        // Show loading
        loadingDiv.style.display = 'flex';
        contentDiv.style.display = 'none';

        // Show modal
        console.log('🚀 Showing modal...'); // Debug
        modal.show();

        // Check BASE_URL
        if (typeof BASE_URL === 'undefined') {
            console.error('❌ BASE_URL is not defined!');
            Swal.fire('ข้อผิดพلาด!', 'BASE_URL ไม่ได้กำหนด', 'error');
            return;
        }

        const url = BASE_URL + '/keyresult/get-entry-details/' + entryId;
        console.log('🌐 Fetching URL:', url); // Debug

        // Fetch entry details
        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('📡 Response status:', response.status); // Debug
            return response.json();
        })
        .then(data => {
            console.log('📊 Response data:', data); // Debug

            if (data.success) {
                populateModal(data.entry);

                // Hide loading, show content
                loadingDiv.style.display = 'none';
                contentDiv.style.display = 'block';
            } else {
                console.error('❌ API Error:', data.message);
                Swal.fire('ข้อผิดพลาด!', data.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                modal.hide();
            }
        })
        .catch(error => {
            console.error('❌ Fetch Error:', error);
            Swal.fire('ข้อผิดพลาด!', 'เกิดข้อผิดพลาดในการโหลดข้อมูล', 'error');
            modal.hide();
        });
    };

    // Populate modal with entry data
    var populateModal = (entry) => {
        // Title
        document.getElementById('modal-entry-title').textContent = entry.entry_name;

        // Status
        const statusBadge = document.getElementById('modal-entry-status');
        statusBadge.className = 'badge '; // Reset classes
        switch (entry.entry_status) {
            case 'published':
                statusBadge.className += 'badge-light-success';
                statusBadge.textContent = 'Published';
                break;
            case 'draft':
                statusBadge.className += 'badge-light-warning';
                statusBadge.textContent = 'Draft';
                break;
            case 'inactive':
                statusBadge.className += 'badge-light-danger';
                statusBadge.textContent = 'Inactive';
                break;
            default:
                statusBadge.className += 'badge-light-secondary';
                statusBadge.textContent = 'Unknown';
        }

        // Date
        const date = new Date(entry.created_date);
        document.getElementById('modal-entry-date').textContent =
            date.toLocaleDateString('th-TH') + ' ' + date.toLocaleTimeString('th-TH');

        // Description
        document.getElementById('modal-entry-description').innerHTML =
            entry.entry_description || '<span class="text-muted">ไม่มีรายละเอียด</span>';

        // Tags
        const tagsContainer = document.getElementById('modal-entry-tags');
        tagsContainer.innerHTML = '';
        if (entry.tags && entry.tags.length > 0) {
            entry.tags.forEach(tag => {
                const tagBadge = document.createElement('span');
                tagBadge.className = 'badge badge-light-info fs-7';
                tagBadge.textContent = tag;
                tagsContainer.appendChild(tagBadge);
            });
        } else {
            tagsContainer.innerHTML = '<span class="text-muted">ไม่มีคำสำคัญ</span>';
        }

        // Files
        const filesContainer = document.getElementById('modal-entry-files');
        filesContainer.innerHTML = '';
        if (entry.files && entry.files.length > 0) {
            const filesList = document.createElement('div');
            filesList.className = 'list-group list-group-flush';

            entry.files.forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'list-group-item d-flex justify-content-between align-items-center px-0';

                const fileInfo = document.createElement('div');
                fileInfo.className = 'd-flex flex-column';

                const fileName = document.createElement('a');
                fileName.href = BASE_URL + '/' + file.file_path;
                fileName.target = '_blank';
                fileName.className = 'fw-bold text-primary text-hover-primary';
                fileName.textContent = file.orginal_name || file.file_name;

                const fileDate = document.createElement('small');
                fileDate.className = 'text-muted';
                const uploadDate = new Date(file.uploaded_date);
                fileDate.textContent = 'อัปโหลดเมื่อ: ' + uploadDate.toLocaleDateString('th-TH') + ' ' + uploadDate.toLocaleTimeString('th-TH');

                fileInfo.appendChild(fileName);
                fileInfo.appendChild(fileDate);

                const downloadBtn = document.createElement('a');
                downloadBtn.href = BASE_URL + '/' + file.file_path;
                downloadBtn.target = '_blank';
                downloadBtn.className = 'btn btn-sm btn-light-primary';
                downloadBtn.innerHTML = '<i class="ki-outline ki-down fs-4"></i>';

                fileItem.appendChild(fileInfo);
                fileItem.appendChild(downloadBtn);
                filesList.appendChild(fileItem);
            });

            filesContainer.appendChild(filesList);
        } else {
            filesContainer.innerHTML = '<span class="text-muted">ไม่มีไฟล์แนบ</span>';
        }

        // Set edit button link
        document.getElementById('modal-edit-btn').onclick = function() {
            window.location.href = BASE_URL + '/keyresult/edit-entry/' + entry.id;
        };
    };

    // Handle modal close events
    var handleModalEvents = () => {
        const modal = document.getElementById('kt_modal_entry_details');
        const closeButtons = modal.querySelectorAll('[data-kt-modal-action="close"]');

        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        });
    };

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
                        fetch(BASE_URL + '/keyresult/delete-entry/' + entryId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
            handleViewEntryDetails();
            handleDeleteEntryAjax();
            handleModalEvents();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTKeyresultView.init();
});