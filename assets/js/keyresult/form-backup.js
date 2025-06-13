"use strict";

const csrfToken = "<?= csrf_hash() ?>";

var KTKeyresultEntry = function () {
    let quillInstance;
    let validator;
    let uploadedFiles = []; // เก็บไฟล์ที่อัปโหลดแล้วแบบ async

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
    }

    const initTagify = () => {
        const el = document.querySelector('#entry_tag');
        if (!el) return;

        new Tagify(el, {
            whitelist: ["sdg1", "sdg2", "sdg3"],
            dropdown: {
                maxItems: 20,
                enabled: 0,
                closeOnSelect: false
            }
        });
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
                    file.uploadedFilename = response.filename;
                    console.log("✅ Upload success:", response.filename);
                } else {
                    console.error("❌ Upload failed:", response.message || "Unknown error");
                }
            },
            removedfile: function (file) {
                const preview = file.previewElement;
                if (preview) preview.remove();
                console.log("🗑 Removed file:", file.name);
            }
        });

        console.log("✅ Dropzone initialized");
    };

    const handleStatus = () => {
        const target = document.getElementById('entry_status');
        const select = document.getElementById('entry_status_select');
        const statusClasses = ['bg-success', 'bg-warning', 'bg-danger', 'bg-primary'];

        if (!select || !target) return;

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

                        Swal.fire({
                            text: "บันทึกข้อมูลสำเร็จ!",
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
            handleStatus();
            initFormValidation();
            handleFormSubmit(); // ✅ แบบใหม่: intercept `form.submit`
        }
    };
}();

// Run on DOM ready
KTUtil.onDOMContentLoaded(function () {
    KTKeyresultEntry.init();
});
