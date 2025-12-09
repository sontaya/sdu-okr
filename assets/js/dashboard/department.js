"use strict";

var DepartmentDashboard = function () {
    var table;

    var initTable = function () {
        table = $('#kt_department_krs_table').DataTable({
            "info": false,
            "order": [],
            "pageLength": 10,
            "lengthChange": false,
            "language": {
                "search": "ค้นหา:",
                "emptyTable": "ไม่พบข้อมูล Key Result"
            }
        });

        $('#kt-department-search').on('keyup', function () {
            table.search(this.value).draw();
        });
    };

    return {
        init: function () {
            initTable();
        }
    };
}();

$(document).ready(function() {
    DepartmentDashboard.init();
});