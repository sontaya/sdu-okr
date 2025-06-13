"use strict";

const csrfToken = "<?= csrf_hash() ?>";

var KTKeyresultEntry = function () {
    let quillInstance;
    let validator;
    let uploadedFiles = []; // เก็บไฟล์ที่อัปโหลดแล้วแบบ async
    let uploadedFilesInfo = {}; // ✅ เก็บข้อมูลไฟล์รวมทั้งชื่อเดิม
    let tagifyInstance;

    const initQuill = () => {
        const el = document.querySelector('#entry_description_editor');
        if (!el) return;

        quillInstance = new Quill(el, {
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    ['image', 'code-block']
                ]
            },
            placeholder: 'Type your text here...',
            theme: 'snow'
        });

        // ✅ โหลดข้อมูลเดิมเข้า Quill (สำหรับโหมดแก้ไข)
        const textarea = document.getElementById('entry_description');
        if (textarea && textarea.value) {
            quillInstance.root.innerHTML = textarea.value;
        }
    }

    const initTagify = () => {
        const el = document.querySelector('#entry_tag');
        if (!el) return;

        // ✅ สร้าง Tagify instance
        tagifyInstance = new Tagify(el, {
            whitelist: ["sdg1", "sdg2", "sdg3"],
            dropdown: {
                maxItems: 20,
                enabled: 0,
                closeOnSelect: false
            }
        });

        // ✅ โหลด tags เดิม (ถ้าเป็นโหมดแก้ไข)
        if (typeof initialTags !== 'undefined' && Array.isArray(initialTags) && initialTags.length > 0) {
            tagifyInstance.addTags(initialTags);
        }
    }

    const initDropzone = () => {
        const dropzoneElement = document.getElementById('kt_dropzone_attachments');
        if (!dropzoneElement) {
            console.warn('❗ Dropzone container not found');
            return;
        }

        // ตรวจว่า Dropzone ถูก bind แล้วหรือยัง
        if (Dropzone.instances.length > 0) {
            Dropzone.instances.forEach(dz => dz.destroy());
        }

        // ✅ สร้าง Dropzone ใหม่
        new Dropzone("#kt_dropzone_attachments", {
            url: BASE_URL + "/upload/temp",
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            paramName: "file",
            maxFilesize: 10,
            maxFiles: 10,
            addRemoveLinks: true,
            acceptedFiles: ".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png",

            success: function (file, response) {
                if (response.success && response.filename) {
                    uploadedFiles.push(response.filename); // ✅ เก็บชื่อไฟล์ไว้
                    uploadedFilesInfo[response.filename] = {
                        original_name: response.original_name || file.name,
                        filename: response.filename
                    }; // ✅ เก็บข้อมูลไฟล์ทั้งหมด
                    file.uploadedFilename = response.filename;
                    console.log("✅ Upload success:", response.filename, "Original:", response.original_name);
                } else {
                    console.error("❌ Upload failed:", response.message || "Unknown error");
                }
            },
            removedfile: function (file) {
                // ✅ ลบจาก uploadedFiles array
                if (file.uploadedFilename) {
                    const index = uploadedFiles.indexOf(file.uploadedFilename);
                    if (index > -1) {
                        uploadedFiles.splice(index, 1);
                    }
                    // ✅ ลบจาก uploadedFilesInfo
                    delete uploadedFilesInfo[file.uploadedFilename];
                }

                const preview = file.previewElement;
                if (preview) preview.remove();
                console.log("🗑 Removed file:", file.name);
            }
        });

        console.log("✅ Dropzone initialized");
    };

    // ✅ แก้ไขฟังก์ชันลบไฟล์เดิม - เปลี่ยนเป็น POST
    const handleDeleteExistingFiles = () => {
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-file-btn')) {
                e.preventDefault();

                const fileId = e.target.getAttribute('data-file-id');
                const listItem = e.target.closest('li');

                Swal.fire({
                    title: 'แน่ใจหรือไม่?',
                    text: "ต้องการลบไฟล์นี้ใช่หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // ✅ เปลี่ยนจาก DELETE เป็น POST
                        fetch(BASE_URL + '/keyresult/delete-file/' + fileId, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                listItem.remove();
                                Swal.fire('ลบแล้ว!', 'ไฟล์ถูกลบเรียบร้อยแล้ว', 'success');
                            } else {
                                Swal.fire('ข้อผิดพลาด!', data.message || 'ไม่สามารถลบไฟล์ได้', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('ข้อผิดพลาด!', 'เกิดข้อผิดพลาดในการลบไฟล์', 'error');
                        });
                    }
                });
            }
        });
    };

    const handleStatus = () => {
        const target = document.getElementById('entry_status');
        const select = document.getElementById('entry_status_select');
        const statusClasses = ['bg-success', 'bg-warning', 'bg-danger', 'bg-primary'];

        if (!select || !target) return;

        // ✅ Set initial status color based on current value
        const initialValue = select.value;
        if (initialValue) {
            target.classList.remove(...statusClasses);
            if (initialValue === 'published') {
                target.classList.add('bg-success');
            } else if (initialValue === 'inactive') {
                target.classList.add('bg-danger');
            } else {
                target.classList.add('bg-primary');
            }
        }

        select.addEventListener('change', function (e) {
            const value = e.target.value;

            target.classList.remove(...statusClasses);
            if (value === 'published') {
                target.classList.add('bg-success');
            } else if (value === 'inactive') {
                target.classList.add('bg-danger');
            } else {
                target.classList.add('bg-primary');
            }
        });
    }

    const initFormValidation = () => {
        const form = document.getElementById('kt_keyresult_entries_form');
        if (!form) return;

        validator = FormValidation.formValidation(form, {
            fields: {
                'entry_name': {
                    validators: {
                        notEmpty: {
                            message: 'กรุณาระบุชื่อรายการ'
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap: new FormValidation.plugins.Bootstrap5({
                    rowSelector: '.fv-row',
                    eleInvalidClass: '',
                    eleValidClass: ''
                })
            }
        });
    }

    const handleFormSubmit = () => {
        const form = document.getElementById('kt_keyresult_entries_form');
        const submitButton = document.getElementById('kt_keyresult_entries_submit');

        if (!form || !submitButton) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // ✅ Sync Quill content
            const textarea = document.getElementById('entry_description');
            if (quillInstance && textarea) {
                textarea.value = quillInstance.root.innerHTML;
                console.log('✅ Quill content synced to textarea');
            }

            validator.validate().then(function (status) {
                if (status === 'Valid') {
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;

                    setTimeout(function () {
                        submitButton.removeAttribute('data-kt-indicator');

                        const isEdit = typeof isEditMode !== 'undefined' && isEditMode;
                        const successMessage = isEdit ? "อัปเดตข้อมูลสำเร็จ!" : "บันทึกข้อมูลสำเร็จ!";

                        Swal.fire({
                            text: successMessage,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                // ✅ แทรก hidden input สำหรับแต่ละไฟล์ที่อัปโหลดไว้
                                uploadedFiles.forEach(filename => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'attachments[]';
                                    input.value = filename;
                                    form.appendChild(input);

                                    // ✅ เพิ่ม hidden input สำหรับชื่อเดิม
                                    const originalInput = document.createElement('input');
                                    originalInput.type = 'hidden';
                                    originalInput.name = 'original_names[]';
                                    originalInput.value = uploadedFilesInfo[filename]?.original_name || filename;
                                    form.appendChild(originalInput);
                                });

                                form.submit(); // ✅ ส่งฟอร์มจริง
                            }
                        });
                    }, 500);
                } else {
                    Swal.fire({
                        text: "กรุณากรอกข้อมูลให้ครบถ้วน",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "ตกลง",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            });
        });
    }

    return {
        init: function () {
            initQuill();
            initTagify();
            initDropzone();
            handleDeleteExistingFiles(); // ✅ เพิ่มการจัดการลบไฟล์เดิม
            handleStatus();
            initFormValidation();
            handleFormSubmit();
        }
    };
}();

// Run on DOM ready
KTUtil.onDOMContentLoaded(function () {
    KTKeyresultEntry.init();
});