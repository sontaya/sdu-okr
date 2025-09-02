"use strict";

// Class definition
var KTKeyResultViewEntry = function () {
    // Private functions
    var initDeleteButton = function () {
        const deleteBtn = document.getElementById('delete-entry-btn');

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function (e) {
                e.preventDefault();

                const entryId = this.getAttribute('data-entry-id');

                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: "การลบรายการนี้จะไม่สามารถกู้คืนได้!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // แสดง loading
                        Swal.fire({
                            title: 'กำลังลบ...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // ส่งคำขอลบ
                        fetch(`/keyresult/delete-entry/${entryId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'ลบสำเร็จ!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    // กลับไปหน้า Key Result
                                    window.location.href = '/keyresult/list';
                                });
                            } else {
                                Swal.fire({
                                    title: 'เกิดข้อผิดพลาด!',
                                    text: data.message,
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด!',
                                text: 'ไม่สามารถลบรายการได้ กรุณาลองใหม่อีกครั้ง',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                    }
                });
            });
        }
    };

    var initTooltips = function () {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    };

    var initFileDownload = function () {
        // เพิ่ม event listener สำหรับปุ่มดาวน์โหลด
        const downloadBtns = document.querySelectorAll('[data-bs-toggle="tooltip"][title="ดาวน์โหลดไฟล์"]');

        downloadBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // สามารถเพิ่ม tracking หรือ analytics ได้ที่นี่
                console.log('Downloading file:', this.href);
            });
        });
    };

    var initBackButton = function () {
        // เพิ่ม functionality สำหรับปุ่มย้อนกลับด้วย keyboard shortcut
        document.addEventListener('keydown', function(e) {
            // Alt + Left Arrow = กลับหน้าก่อน
            if (e.altKey && e.key === 'ArrowLeft') {
                e.preventDefault();
                const backBtn = document.querySelector('a[href*="/keyresult/view/"]');
                if (backBtn) {
                    window.location.href = backBtn.href;
                }
            }

            // Ctrl + E = แก้ไข
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                const editBtn = document.querySelector('a[href*="/keyresult/edit-entry/"]');
                if (editBtn) {
                    window.location.href = editBtn.href;
                }
            }
        });
    };

    // Public methods
    return {
        init: function () {
            initDeleteButton();
            initTooltips();
            initFileDownload();
            initBackButton();

            // เพิ่ม animation fade-in
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTKeyResultViewEntry.init();
});