"use strict";

var KTKeyResultForm = function () {
    var form, submitButton;
    var yearSelect, groupSelect, objectiveSelect, templateSelect, refTableBody, deptList;

    var handleForm = function () {
        // Initial Data (from Config)
        const initialGroupId = KeyResultFormConfig.initialGroupId;
        const initialObjectiveId = KeyResultFormConfig.initialObjectiveId;
        const initialTemplateId = KeyResultFormConfig.initialTemplateId;

        // Helper to load data
        var loadRelatedData = function(type, parentId, targetSelect, initialValue = null, callback = null) {
            if (!parentId) {
                targetSelect.empty().append('<option></option>').prop('disabled', true);
                if (callback) callback();
                return;
            }

            $.ajax({
                url: KeyResultFormConfig.relatedDataUrl,
                type: 'POST',
                data: { type: type, parent_id: parentId },
                success: function(response) {
                    if (response.success) {
                        targetSelect.empty().append('<option></option>');
                        response.data.forEach(function(item) {
                            const isSelected = item.id == initialValue ? 'selected' : '';
                            const text = (item.sequence_no ? item.sequence_no + '. ' : '') + item.name;
                            targetSelect.append(`<option value="${item.id}" ${isSelected}>${text}</option>`);
                        });
                        targetSelect.prop('disabled', false);
                        if (callback) callback();
                    }
                }
            });
        }

        // Helper to update reference table
        var updateReferenceTable = function() {
            const year = yearSelect.val();
            const groupId = groupSelect.val();
            const objectiveId = objectiveSelect.val();

            if (!groupId) {
                refTableBody.html('<tr><td colspan="2" class="text-center text-muted">กรุณาเลือก Group/Objective</td></tr>');
                return;
            }

            $.ajax({
                url: KeyResultFormConfig.relatedDataUrl,
                type: 'POST',
                data: {
                    type: 'key_results',
                    year: year,
                    objective_group_id: groupId,
                    objective_id: objectiveId
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        refTableBody.empty();
                        response.data.forEach(function(item) {
                           refTableBody.append(`
                                <tr>
                                    <td>${item.sequence_no}</td>
                                    <td>
                                        <span class="text-gray-800 fw-bold d-block text-hover-primary mb-1 text-truncate" style="max-width: 200px;" title="${item.name}">${item.name}</span>
                                        <span class="text-muted small">${item.template_name}</span>
                                    </td>
                                </tr>
                           `);
                        });
                    } else {
                        refTableBody.html('<tr><td colspan="2" class="text-center text-muted">ไม่พบข้อมูล</td></tr>');
                    }
                }
            });
        }

        // Events
        groupSelect.on('change', function() {
            loadRelatedData('objectives', $(this).val(), objectiveSelect, null, function() {
                 templateSelect.empty().append('<option></option>').prop('disabled', true); // Reset template
                 updateReferenceTable();
            });
        });

        objectiveSelect.on('change', function() {
            loadRelatedData('templates', $(this).val(), templateSelect, null, function() {
                updateReferenceTable();
            });
        });

        yearSelect.on('change', function() {
            updateReferenceTable();
        });

        // Initialize logic if Edit mode
        if (initialGroupId) {
            loadRelatedData('objectives', initialGroupId, objectiveSelect, initialObjectiveId, function() {
                if (initialObjectiveId) {
                    loadRelatedData('templates', initialObjectiveId, templateSelect, initialTemplateId);
                    updateReferenceTable();
                }
            });
        }
    }

    var handleDepartments = function() {
        // Department Repeater logic
        $('#add_department').on('click', function() {
            const newRow = `
                <div class="row mb-2 department-item">
                    <div class="col-7">
                        <select name="department_ids[]" class="form-select form-select-solid" data-control="select2" data-placeholder="เลือกหน่วยงาน">
                            ${KeyResultFormConfig.departmentOptions}
                        </select>
                    </div>
                    <div class="col-4">
                        <select name="department_roles[]" class="form-select form-select-solid">
                            <option value="Leader">Leader</option>
                            <option value="CoWorking" selected>CoWorking</option>
                        </select>
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-icon btn-light-danger remove-dept">
                            <i class="ki-outline ki-trash fs-3"></i>
                        </button>
                    </div>
                </div>
            `;

            const $row = $(newRow);
            deptList.append($row);

            // Initialize Select2
            $row.find('[data-control="select2"]').select2();
        });

        deptList.on('click', '.remove-dept', function() {
            if (deptList.find('.department-item').length > 1) {
                $(this).closest('.department-item').remove();
            } else {
                // If only one row, just clear it
                 const row = $(this).closest('.department-item');
                 row.find('select').val(null).trigger('change');
                 row.find('select[name="department_roles[]"]').val('CoWorking');
            }
        });
    }

    var handleSubmit = function() {
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;

            const formData = new FormData(form);

            $.ajax({
                url: KeyResultFormConfig.saveUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    submitButton.removeAttribute('data-kt-indicator');
                    submitButton.disabled = false;

                    if (response.success) {
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                window.location.href = KeyResultFormConfig.redirectUrl;
                            }
                        });
                    } else {
                        Swal.fire({
                            text: response.error || response.messages || "เกิดข้อผิดพลาด",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "ตกลง",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                },
                error: function(xhr) {
                    submitButton.removeAttribute('data-kt-indicator');
                    submitButton.disabled = false;

                    Swal.fire({
                        text: "เกิดข้อผิดพลาดในการเชื่อมต่อ (Status: " + xhr.status + ")",
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
            form = document.getElementById('kt_keyresult_form');
            submitButton = document.getElementById('kt_keyresult_submit');

            // Selectors
            yearSelect = $('#select_year');
            groupSelect = $('#select_group');
            objectiveSelect = $('#select_objective');
            templateSelect = $('#select_template');
            refTableBody = $('#table_reference tbody');
            deptList = $('#department_list');

            handleForm();
            handleDepartments();
            handleSubmit();
        }
    };
}();

document.addEventListener('DOMContentLoaded', function () {
    KTKeyResultForm.init();
});
