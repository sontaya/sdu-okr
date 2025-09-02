// ✅ แก้ไข Role และ Status filters - เก็บ filter states แทนการใช้ ext.search
document.addEventListener('DOMContentLoaded', function() {
    // Global filter states
    let currentFilters = {
        role: '',
        status: ''
    };

    // Initialize DataTable (เดิม)
    const table = $('#kt_keyresults_table').DataTable({
        responsive: true,
        pageLength: 10,
        order: [],
        columnDefs: [
            { orderable: false, targets: [0, 5] },
            { className: 'text-center', targets: [2, 3, 4] },
            { orderable: true, targets: [4] }
        ],
        language: {
            "lengthMenu": "Show _MENU_ entries",
            "zeroRecords": "ไม่พบข้อมูล",
            "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
            "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
            "search": "ค้นหา:",
            "paginate": {
                "first": "หน้าแรก",
                "last": "หน้าสุดท้าย",
                "next": "ถัดไป",
                "previous": "ก่อนหน้า"
            }
        }
    });

    // ✅ สร้าง master filter function เดียว
    function applyCustomFilters(settings, data, dataIndex) {
        const row = table.row(dataIndex).node();
        const roleData = $(row).attr('data-role');
        const statusData = $(row).attr('data-progress-status');

        // ตรวจสอบ Role filter
        let roleMatch = true;
        if (currentFilters.role && currentFilters.role !== 'all') {
            roleMatch = (roleData === currentFilters.role);
        }

        // ตรวจสอบ Status filter
        let statusMatch = true;
        if (currentFilters.status && currentFilters.status !== 'all') {
            statusMatch = (statusData === currentFilters.status);
        }

        const finalResult = roleMatch && statusMatch;

        // Debug log (เฉพาะเมื่อมี filter)
        if (currentFilters.role || currentFilters.status) {
            console.log(`🔍 Row ${dataIndex}: Role(${roleData}${roleMatch?'✓':'✗'}) Status(${statusData}${statusMatch?'✓':'✗'}) = ${finalResult}`);
        }

        return finalResult;
    }

    // ลงทะเบียน master filter function ครั้งเดียว
    $.fn.dataTable.ext.search.push(applyCustomFilters);

    // ✅ Role Filter สำหรับ Select2
    $('[data-kt-keyresults-filter="role"]').on('change', function() {
        const value = this.value;
        console.log('🔍 Role filter changed to:', value);

        // อัพเดท filter state
        currentFilters.role = value;

        console.log('🔍 Current filters:', currentFilters);
        console.log('🔍 Redrawing table...');

        table.draw();

        // Log results
        setTimeout(() => {
            const totalRows = table.rows().count();
            const visibleRows = table.rows({ search: 'applied' }).count();
            console.log('🔍 Filter results - Total:', totalRows, 'Visible:', visibleRows);
        }, 100);
    });

    // ✅ Status Filter สำหรับ Select2
    $('[data-kt-keyresults-filter="progress_status"]').on('change', function() {
        const value = this.value;
        console.log('🔍 Status filter changed to:', value);

        // อัพเดท filter state
        currentFilters.status = value;

        console.log('🔍 Current filters:', currentFilters);
        console.log('🔍 Redrawing table...');

        table.draw();

        // Log results
        setTimeout(() => {
            const totalRows = table.rows().count();
            const visibleRows = table.rows({ search: 'applied' }).count();
            console.log('🔍 Status filter results - Total:', totalRows, 'Visible:', visibleRows);
        }, 100);
    });

    // Search functionality (เดิม)
    const searchInput = document.querySelector('[data-kt-keyresults-filter="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            table.search(this.value).draw();
        });
    }

    // ✅ Debug function
    function debugCurrentState() {
        console.log('🔍 === CURRENT STATE ===');
        console.log('🔍 Current filters:', currentFilters);
        console.log('🔍 Total ext.search functions:', $.fn.dataTable.ext.search.length);
        console.log('🔍 Total rows:', table.rows().count());
        console.log('🔍 Visible rows:', table.rows({ search: 'applied' }).count());

        // Show first 3 rows data
        for (let i = 0; i < Math.min(3, table.rows().count()); i++) {
            const row = table.row(i).node();
            const roleData = $(row).attr('data-role');
            const statusData = $(row).attr('data-progress-status');
            console.log(`🔍 Row ${i}: role="${roleData}" status="${statusData}"`);
        }
        console.log('🔍 === END STATE ===');
    }

    // เรียก debug หลังจากโหลดเสร็จ
    setTimeout(debugCurrentState, 1000);

    // เพิ่ม debug button (temporary)
    console.log('🔍 To debug current state, run: debugCurrentState()');
    window.debugCurrentState = debugCurrentState;

    // Submit Report functionality (เดิม)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('submit-report-btn')) {
            e.preventDefault();
            const progressId = e.target.dataset.progressId;

            Swal.fire({
                title: 'ยืนยันการส่งรายงาน?',
                text: 'เมื่อส่งรายงานแล้ว จะไม่สามารถแก้ไขได้',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ส่งรายงาน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitProgress(progressId);
                }
            });
        }
    });

    // Approve Report functionality (เดิม)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('approve-report-btn')) {
            e.preventDefault();
            const progressId = e.target.dataset.progressId;

            Swal.fire({
                title: 'ยืนยันการอนุมัติ?',
                text: 'คุณต้องการอนุมัติรายงานนี้หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ใช่, อนุมัติ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    approveProgress(progressId);
                }
            });
        }
    });

    // Reject Report functionality (เดิม)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('reject-report-btn')) {
            e.preventDefault();
            const progressId = e.target.dataset.progressId;

            Swal.fire({
                title: 'ปฏิเสธรายงาน',
                html: `
                    <div class="mb-3">
                        <label class="form-label">เหตุผลในการปฏิเสธ:</label>
                        <textarea class="form-control" id="reject_reason" rows="3" placeholder="กรุณาระบุเหตุผล..." required></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ปฏิเสธ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545',
                preConfirm: () => {
                    const reason = document.getElementById('reject_reason').value;
                    if (!reason.trim()) {
                        Swal.showValidationMessage('กรุณาระบุเหตุผลในการปฏิเสธ');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    rejectProgress(progressId, result.value);
                }
            });
        }
    });

    // Master checkbox functionality (เดิม)
    const masterCheckbox = document.querySelector('[data-kt-check="true"]');
    if (masterCheckbox) {
        masterCheckbox.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('#kt_keyresults_table .form-check-input');
            checkboxes.forEach(checkbox => {
                if (checkbox !== masterCheckbox) {
                    checkbox.checked = masterCheckbox.checked;
                }
            });
        });
    }
});