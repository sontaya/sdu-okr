"use strict";

// Class definition
var KTProgressForm = function () {
    // Shared variables
    var validator;
    var form;
    var submitButton;
    var submitProgressButton;
    var cancelButton;

    // Quill editors
    var progressDescriptionQuill;
    var challengesQuill;
    var solutionsQuill;
    var nextActionsQuill;


    // Init form inputs
    var initForm = function () {
        // Progress value input change handler
        const progressValueInput = document.getElementById('progress_value_input');
        const progressPercentageDisplay = document.getElementById('progress_percentage_display');

        if (progressValueInput && progressPercentageDisplay) {
            progressValueInput.addEventListener('input', function() {
                const currentValue = parseFloat(this.value) || 0;
                const percentage = targetValue > 0 ? (currentValue / targetValue) * 100 : 0;
                progressPercentageDisplay.textContent = `(${percentage.toFixed(1)}%)`;

                // Update color based on percentage
                if (percentage >= 100) {
                    progressPercentageDisplay.className = 'fw-bold text-success ms-3';
                } else if (percentage >= 75) {
                    progressPercentageDisplay.className = 'fw-bold text-info ms-3';
                } else if (percentage >= 50) {
                    progressPercentageDisplay.className = 'fw-bold text-warning ms-3';
                } else {
                    progressPercentageDisplay.className = 'fw-bold text-danger ms-3';
                }
            });

            // Trigger calculation on page load
            progressValueInput.dispatchEvent(new Event('input'));
        }

        // Status indicator
        const statusSelect = document.getElementById('progress_status_select');
        const statusIndicator = document.getElementById('progress_status_indicator');

        if (statusSelect && statusIndicator) {
            const updateStatusIndicator = function() {
                const status = statusSelect.value;
                statusIndicator.className = 'rounded-circle w-15px h-15px';

                switch(status) {
                    case 'draft':
                        statusIndicator.classList.add('bg-warning');
                        break;
                    case 'submitted':
                        statusIndicator.classList.add('bg-info');
                        break;
                    case 'approved':
                        statusIndicator.classList.add('bg-success');
                        break;
                    case 'rejected':
                        statusIndicator.classList.add('bg-danger');
                        break;
                    default:
                        statusIndicator.classList.add('bg-secondary');
                }
            };

            statusSelect.addEventListener('change', updateStatusIndicator);
            updateStatusIndicator(); // Init
        }

        if (challengesQuill) {
            challengesQuill.on('text-change', function() {
                document.querySelector('#challenges').value = challengesQuill.root.innerHTML;
            });
        }

        if (solutionsQuill) {
            solutionsQuill.on('text-change', function() {
                document.querySelector('#solutions').value = solutionsQuill.root.innerHTML;
            });
        }

        if (nextActionsQuill) {
            nextActionsQuill.on('text-change', function() {
                document.querySelector('#next_actions').value = nextActionsQuill.root.innerHTML;
            });
        }

        // ✅ Force setup event listeners สำหรับทุก mode
        setTimeout(function() {
            setupQuillEventListeners();
        }, 500);

    };

    var setupQuillEventListeners = function() {
        if (progressDescriptionQuill) {
            progressDescriptionQuill.on('text-change', function() {
                document.querySelector('#progress_description').value = progressDescriptionQuill.root.innerHTML;
                console.log('Progress desc auto-synced:', progressDescriptionQuill.root.innerHTML.length, 'chars');
            });
        }

        if (challengesQuill) {
            challengesQuill.on('text-change', function() {
                document.querySelector('#challenges').value = challengesQuill.root.innerHTML;
                console.log('Challenges auto-synced:', challengesQuill.root.innerHTML.length, 'chars');
            });
        }

        if (solutionsQuill) {
            solutionsQuill.on('text-change', function() {
                document.querySelector('#solutions').value = solutionsQuill.root.innerHTML;
                console.log('Solutions auto-synced:', solutionsQuill.root.innerHTML.length, 'chars');
            });
        }

        if (nextActionsQuill) {
            nextActionsQuill.on('text-change', function() {
                document.querySelector('#next_actions').value = nextActionsQuill.root.innerHTML;
                console.log('Next actions auto-synced:', nextActionsQuill.root.innerHTML.length, 'chars');
            });
        }
    };


    // Init Quill editors
    var initQuillEditors = function () {
        // Progress Description Editor
        var progressDescriptionElement = document.querySelector('#progress_description_editor');
        if (progressDescriptionElement) {
            progressDescriptionQuill = new Quill('#progress_description_editor', {
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'อธิบายรายละเอียดความคืบหน้า...',
                theme: 'snow'
            });

            // Set initial content if editing
            const progressDescriptionTextarea = document.querySelector('#progress_description');
            if (progressDescriptionTextarea && progressDescriptionTextarea.value) {
                progressDescriptionQuill.root.innerHTML = progressDescriptionTextarea.value;
            }
        }

        // Challenges Editor
        var challengesElement = document.querySelector('#challenges_editor');
        if (challengesElement) {
            challengesQuill = new Quill('#challenges_editor', {
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                },
                placeholder: 'ระบุอุปสรรคและปัญหาที่พบ...',
                theme: 'snow'
            });

            const challengesTextarea = document.querySelector('#challenges');
            if (challengesTextarea && challengesTextarea.value) {
                challengesQuill.root.innerHTML = challengesTextarea.value;
            }
        }

        // Solutions Editor
        var solutionsElement = document.querySelector('#solutions_editor');
        if (solutionsElement) {
            solutionsQuill = new Quill('#solutions_editor', {
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                },
                placeholder: 'ระบุแนวทางแก้ไข...',
                theme: 'snow'
            });

            // ✅ แก้ไข: เปลี่ยนจาก progressDescriptionElement เป็น solutionsElement
            // solutionsElement.querySelector('.ql-editor').style.minHeight = '350px'; // ลบบรรทัดนี้ออก

            const solutionsTextarea = document.querySelector('#solutions');
            if (solutionsTextarea && solutionsTextarea.value) {
                solutionsQuill.root.innerHTML = solutionsTextarea.value;
            }
        }

        // Next Actions Editor
        var nextActionsElement = document.querySelector('#next_actions_editor');
        if (nextActionsElement) {
            nextActionsQuill = new Quill('#next_actions_editor', {
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                },
                placeholder: 'ระบุแผนการดำเนินงานต่อไป...',
                theme: 'snow'
            });

            const nextActionsTextarea = document.querySelector('#next_actions');
            if (nextActionsTextarea && nextActionsTextarea.value) {
                nextActionsQuill.root.innerHTML = nextActionsTextarea.value;
            }
        }
    };

    // Init Dual Listbox
    var initDualListbox = function () {
        const availableEntries = document.getElementById('available_entries');
        const selectedEntries = document.getElementById('selected_entries');
        const entryDetails = document.getElementById('entry_details');
        const entryTitle = document.getElementById('entry_title');
        const entryDescription = document.getElementById('entry_description');
        const entryStatus = document.getElementById('entry_status');

        if (!availableEntries || !selectedEntries) return;

        // ปุ่มเพิ่มรายการที่เลือก
        document.getElementById('btn_add_entries').addEventListener('click', function() {
            moveSelectedOptions(availableEntries, selectedEntries);
        });

        // ปุ่มเพิ่มทั้งหมด
        document.getElementById('btn_add_all_entries').addEventListener('click', function() {
            moveAllOptions(availableEntries, selectedEntries);
        });

        // ปุ่มลบรายการที่เลือก
        document.getElementById('btn_remove_entries').addEventListener('click', function() {
            moveSelectedOptions(selectedEntries, availableEntries);
        });

        // ปุ่มลบทั้งหมด
        document.getElementById('btn_remove_all_entries').addEventListener('click', function() {
            moveAllOptions(selectedEntries, availableEntries);
        });

        // แสดงรายละเอียดเมื่อคลิกรายการ
        function showEntryDetails(selectElement) {
            selectElement.addEventListener('click', function(e) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                if (selectedOption) {
                    entryTitle.textContent = selectedOption.textContent;
                    entryDescription.textContent = selectedOption.getAttribute('data-description') || 'ไม่มีรายละเอียด';
                    entryStatus.textContent = 'สถานะ: ' + (selectedOption.getAttribute('data-status') || 'ไม่ระบุ');
                    entryDetails.style.display = 'block';
                }
            });
        }

        showEntryDetails(availableEntries);
        showEntryDetails(selectedEntries);

        // ซ่อนรายละเอียดเมื่อคลิกที่อื่น
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#available_entries') && !e.target.closest('#selected_entries')) {
                entryDetails.style.display = 'none';
            }
        });

        // ฟังก์ชันย้ายรายการที่เลือก
        function moveSelectedOptions(fromSelect, toSelect) {
            const selectedOptions = Array.from(fromSelect.selectedOptions);
            selectedOptions.forEach(function(option) {
                toSelect.appendChild(option);
            });
            sortSelectOptions(toSelect);
        }

        // ฟังก์ชันย้ายทั้งหมด
        function moveAllOptions(fromSelect, toSelect) {
            const allOptions = Array.from(fromSelect.options);
            allOptions.forEach(function(option) {
                toSelect.appendChild(option);
            });
            sortSelectOptions(toSelect);
        }

        // ฟังก์ชันเรียงลำดับ options
        function sortSelectOptions(selectElement) {
            const options = Array.from(selectElement.options);
            options.sort(function(a, b) {
                return a.text.localeCompare(b.text);
            });
            options.forEach(function(option) {
                selectElement.appendChild(option);
            });
        }
    };

    // Init validation
    var initValidation = function () {
        // Init form validation rules
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'progress_value': {
                        validators: {
                            notEmpty: {
                                message: 'กรุณาระบุค่าความคืบหน้า'
                            },
                            numeric: {
                                message: 'กรุณาระบุตัวเลขที่ถูกต้อง'
                            }
                        }
                    },
                    'reporting_period_id': {
                        validators: {
                            notEmpty: {
                                message: 'กรุณาเลือกรอบการรายงาน'
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
            }
        );
    };

    var selectAllEntriesBeforeSubmit = function() {
        // เลือกทุก option ใน selected_entries ก่อนส่งฟอร์ม
        const selectedEntries = document.getElementById('selected_entries');
        if (selectedEntries) {
            for (let i = 0; i < selectedEntries.options.length; i++) {
                selectedEntries.options[i].selected = true;
            }
        }
    };

    // Handle form submit
    var handleSubmit = function () {
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            console.log('Form submit - isEditMode:', typeof isEditMode !== 'undefined' ? isEditMode : 'undefined');

            // ✅ เพิ่มการตรวจสอบว่า Quill editors พร้อมหรือไม่
            const quillReady = checkQuillEditorsReady();
            console.log('Quill editors ready:', quillReady);

            // ✅ เพิ่ม delay และ force sync
            setTimeout(function() {
                // Sync Quill content to hidden textareas
                syncQuillContent();

                // ✅ เพิ่มการตรวจสอบว่า sync สำเร็จหรือไม่
                validateSyncedContent();

                // debug หลัง sync
                console.log('=== Form Data After Sync ===');
                console.log('Progress description:', document.querySelector('#progress_description').value.length, 'chars');
                console.log('Challenges:', document.querySelector('#challenges').value.length, 'chars');
                console.log('Solutions:', document.querySelector('#solutions').value.length, 'chars');
                console.log('Next actions:', document.querySelector('#next_actions').value.length, 'chars');

                // Select all entries before submit
                selectAllEntriesBeforeSubmit();

                // debug สำหรับ selected entries
                const selectedEntries = document.getElementById('selected_entries');
                if (selectedEntries) {
                    console.log('Selected entries count:', selectedEntries.options.length);
                }

                // Validate form before submit
                if (validator) {
                    validator.validate().then(function (status) {
                        console.log('Validation status:', status);

                        if (status == 'Valid') {
                            submitButton.setAttribute('data-kt-indicator', 'on');
                            submitButton.disabled = true;
                            form.submit();
                        }
                    });
                } else {
                    console.log('No validator, submitting directly');
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;
                    form.submit();
                }
            }, 200); // ✅ เพิ่ม delay เพื่อให้ Quill ready
        });

        // Handle Submit Progress Button (สำหรับ edit mode)
        if (submitProgressButton) {
            submitProgressButton.addEventListener('click', function (e) {
                e.preventDefault();

                console.log('Submit progress button clicked');

                // ตรวจสอบว่าเป็น edit mode และมี progressId
                if (!isEditMode || !progressId) {
                    console.error('Not in edit mode or missing progressId');
                    return;
                }

                // แสดง loading
                submitProgressButton.setAttribute('data-kt-indicator', 'on');
                submitProgressButton.disabled = true;

                // เพิ่ม delay เช่นเดียวกัน
                setTimeout(function() {
                    syncQuillContent();
                    selectAllEntriesBeforeSubmit();

                    // ใช้ AJAX แทน form.submit()
                    fetch(`${BASE_URL}progress/submit/${progressId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            // ส่งข้อมูลที่จำเป็นถ้ามี
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // แสดงข้อความสำเร็จ
                            Swal.fire({
                                text: data.message || "ส่งรายงานเพื่อขออนุมัติสำเร็จ",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "ตกลง",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            }).then(function () {
                                // กลับไปหน้า view
                                window.location.href = `${BASE_URL}progress/view/${keyResultId}`;
                            });
                        } else {
                            // แสดงข้อความผิดพลาด
                            Swal.fire({
                                text: data.message || "เกิดข้อผิดพลาด",
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
                        console.error('Submit error:', error);
                        Swal.fire({
                            text: "เกิดข้อผิดพลาดในการส่งรายงาน",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    })
                    .finally(function() {
                        // ปิด loading
                        submitProgressButton.removeAttribute('data-kt-indicator');
                        submitProgressButton.disabled = false;
                    });
                }, 200);
            });
        }
    };

    // Sync Quill editors content to hidden textareas
    var syncQuillContent = function () {
        console.log('=== Syncing Quill Content ===');

        // ✅ ใช้ root.innerHTML แทน getText() และเช็คให้แน่ใจ
        if (progressDescriptionQuill && progressDescriptionQuill.root) {
            let content = progressDescriptionQuill.root.innerHTML;
            // ✅ เช็คว่าเป็นเนื้อหาว่างหรือไม่
            if (content === '<p><br></p>' || content === '<p></p>') {
                content = '';
            }
            document.querySelector('#progress_description').value = content;
            console.log('✅ Progress description synced:', content.length, 'chars');
        } else {
            console.error('❌ Progress description Quill not ready!');
        }

        if (challengesQuill && challengesQuill.root) {
            let content = challengesQuill.root.innerHTML;
            if (content === '<p><br></p>' || content === '<p></p>') {
                content = '';
            }
            document.querySelector('#challenges').value = content;
            console.log('✅ Challenges synced:', content.length, 'chars');
        } else {
            console.error('❌ Challenges Quill not ready!');
        }

        if (solutionsQuill && solutionsQuill.root) {
            let content = solutionsQuill.root.innerHTML;
            if (content === '<p><br></p>' || content === '<p></p>') {
                content = '';
            }
            document.querySelector('#solutions').value = content;
            console.log('✅ Solutions synced:', content.length, 'chars');
        } else {
            console.error('❌ Solutions Quill not ready!');
        }

        if (nextActionsQuill && nextActionsQuill.root) {
            let content = nextActionsQuill.root.innerHTML;
            if (content === '<p><br></p>' || content === '<p></p>') {
                content = '';
            }
            document.querySelector('#next_actions').value = content;
            console.log('✅ Next actions synced:', content.length, 'chars');
        } else {
            console.error('❌ Next actions Quill not ready!');
        }

        // ✅ ตรวจสอบผลลัพธ์หลัง sync
        console.log('=== Final Check ===');
        console.log('Progress desc value:', document.querySelector('#progress_description').value.substring(0, 100));
        console.log('Challenges value:', document.querySelector('#challenges').value.substring(0, 100));
        console.log('Solutions value:', document.querySelector('#solutions').value.substring(0, 100));
        console.log('Next actions value:', document.querySelector('#next_actions').value.substring(0, 100));
    };

    // function ตรวจสอบว่า Quill editors พร้อมหรือไม่
    var checkQuillEditorsReady = function() {
        const editors = [
            progressDescriptionQuill,
            challengesQuill,
            solutionsQuill,
            nextActionsQuill
        ];

        let ready = 0;
        editors.forEach(function(editor, index) {
            if (editor && editor.root) {
                ready++;
                console.log(`Quill editor ${index} ready`);
            } else {
                console.warn(`Quill editor ${index} NOT ready`);
            }
        });

        return ready === 4;
    };

    // function ตรวจสอบ content หลัง sync
    var validateSyncedContent = function() {
        const fields = ['progress_description', 'challenges', 'solutions', 'next_actions'];

        fields.forEach(function(fieldName) {
            const element = document.querySelector('#' + fieldName);
            const content = element ? element.value : '';

            if (content.length === 0 || content === '<p><br></p>') {
                console.warn(`${fieldName} appears empty after sync`);
            } else {
                console.log(`${fieldName} sync successful:`, content.substring(0, 50) + '...');
            }
        });
    };

    // Public methods
    return {
        init: function () {
            form = document.querySelector('#kt_progress_form');
            submitButton = document.querySelector('#kt_progress_save_btn');
            submitProgressButton = document.querySelector('#kt_progress_submit_btn');
            cancelButton = document.querySelector('#kt_progress_cancel_btn');

            if (!form) {
                return;
            }

            initForm();
            initQuillEditors();
            initDualListbox();
            initValidation();
            handleSubmit();

            // Backup form submit event listener
            form.addEventListener('submit', function(e) {
                console.log('Form submitted via form event');
                syncQuillContent();
                selectAllEntriesBeforeSubmit();
            });
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProgressForm.init();
});