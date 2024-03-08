<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('./connection.php');
$submittedData = '';
$currentYear = date('Y'); // ปีปัจจุบัน
$currentMonth = date('m'); // เดือนปัจจุบัน
$defaultEndMonth = $currentYear . '-12'; // ตั้งค่าเดือนเริ่มต้นเป็นธันวาคม

if (isset($_POST['submit'])) {
    $departmentId = $_POST['departmentID'] ?? null;
    $sectionId = $_POST['sectionID'] ?? null;

    // $receivedStartMonth = $_POST['startMonth'] ?? $currentYear. '-01' ; // ถ้าไม่มีค่าส่งมา ก็ใช้เดือนมกราคมของปีปัจจุบัน
    $receivedStartMonth = isset($_POST['startMonth']) && $_POST['startMonth'] ? $_POST['startMonth'] : $currentYear . '-01';
    // $receivedEndMonth_December = $_POST['endMonth'] ?? $defaultEndMonth; // ถ้าไม่มีค่าส่งมา ก็ใช้เดือนธันวาคมของปีปัจจุบัน
    $receivedEndMonth_December = isset($_POST['endMonth']) && $_POST['endMonth'] ? $_POST['endMonth'] : $currentYear . '-12';
    // $receivedEndMonth_Current = $_POST['endMonth'] ?? $currentYear . '-' . $currentMonth; // ถ้าไม่มีค่าส่งมา ก็ใช้เดือนปัจจุบัน
    $receivedEndMonth_Current = isset($_POST['endMonth']) && $_POST['endMonth'] ? $_POST['endMonth'] : $currentYear . '-' . $currentMonth;


    // กรณี 1: สร้างวันที่แบบ YYYY-MM-DD
    $startMonthDate = $receivedStartMonth . '-01'; // ตัวอย่าง: 2024-01-01
    $endMonthDate_December = date('Y-m-t', strtotime($receivedEndMonth_December));
    $endMonthDate_Current = date('Y-m-t', strtotime($receivedEndMonth_Current));

    // กรณี 2: แยกค่าปีและเดือน
    $startYear = date('Y', strtotime($receivedStartMonth));
    $startMonth = date('m', strtotime($receivedStartMonth));
    $endYearDecember = date('Y', strtotime($receivedEndMonth_December));
    $endMonthDecember = date('m', strtotime($receivedEndMonth_December));
    $endYearCurrent = date('Y', strtotime($receivedEndMonth_Current));
    $endMonthCurrent = date('m', strtotime($receivedEndMonth_Current));

    
    // เก็บข้อมูลไว้ใน session
    $_SESSION['filter'] = [
        'startMonthDate' => $startMonthDate,
        'endMonthDateDecember' => $endMonthDate_December,
        'endMonthDateCurrent' => $endMonthDate_Current,
        'startYear' => $startYear,
        'startMonth' => $startMonth,
        'endYearDecember' => $endYearDecember,
        'endMonthDecember' => $endMonthDecember,
        'endYearCurrent' => $endYearCurrent,
        'endMonthCurrent' => $endMonthCurrent,
        'divisionId' => $_POST['divisionID'] ?? null,
        'departmentId' => $_POST['departmentID'] ?? null,
        'sectionId' => $_POST['sectionID'] ?? null,
        'type' => $_POST['otTypeID'] ?? null
    ];


}
?>


<?php include('./include/head.php') ?>



<body>
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#filterModal" style="margin-right: 10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter" viewBox="0 0 16 16">
            <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5" />
        </svg>
        <i class="bi bi-filter"></i>Filter
    </button>


    <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Options</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="">
                    <section>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <label for="start-month" class="form-label">เลือกช่วงเดือนเริ่มต้น:</label>
                                    <input type="month" id="start-month" name="startMonth" class="form-control" placeholder="From Date">
                                </div>
                                <div class="col-md-6">
                                    <label for="end-month" class="form-label">เลือกช่วงเดือนสิ้นสุด:</label>
                                    <input type="month" id="end-month" name="endMonth" class="form-control" placeholder="To Date">
                                </div>
                            </div>

                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-label">Division:</label>
                                    <select id="divisionID" name="divisionID" class="custom-select form-control" aria-label="Default select example" autocomplete="off" data-live-search="true">
                                        <option value="" selected>All</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-label">Department:</label>
                                    <select id="departmentID" name="departmentID" class="custom-select form-control" aria-label="Default select example" autocomplete="off" data-live-search="true">
                                        <option value="" selected>All</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" style="margin-bottom: 10px">
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-label">Section:</label>
                                    <select id="sectionID" name="sectionID" class="custom-select form-control" aria-label="Default select example" autocomplete="off" data-live-search="true">
                                        <option value="" selected>All</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <label class="form-label">OT Type:</label>
                                    <select id="otTypeID" name="otTypeID" class="custom-select form-control" aria-label="Default select example" autocomplete="off">
                                        <option value="" selected>Choose OT Type</option>
                                        <option value="OT FIX">OT FIX</option>
                                        <option value="OT NON FIX">OT NON FIX</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row justify-content-end" style="margin-bottom: 10px">
                                <div class="col-md-3 col-sm-12">
                                    <button type="submit" class="btn btn-primary" style="font-size: 15px; padding: 7px 7px;" name="submit">Submit</button>
                                </div>
                            </div>

                    </section>
                </form>
            </div>
        </div>
    </div>


    <?php
    if (!empty($submittedData)) {
        echo "<p>{$submittedData}</p>";
    }
    ?>


</body>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // เรียกฟังก์ชันเหล่านี้เพื่อโหลดข้อมูลเริ่มต้น
        updateDivisions();
        updateDepartments();
        updateSections();

        document.getElementById('divisionID').addEventListener('change', function() {
            // เมื่อมีการเลือก Division ใหม่ โหลดข้อมูล Department ใหม่
            updateDepartments(this.value);
            // รีเซ็ตข้อมูล Section หลังจาก Division ถูกเปลี่ยน
            updateSections();
        });

        document.getElementById('departmentID').addEventListener('change', function() {
            // เมื่อมีการเลือก Department ใหม่ โหลดข้อมูล Section ใหม่
            updateSections(this.value);
        });
    });

    function updateDivisions() {
        fetch('get_division.php')
            .then(response => response.json())
            .then(divisions => {
                var divisionSelect = document.getElementById('divisionID');
                divisionSelect.innerHTML = '<option value="">เลือกทั้งหมด</option>';
                divisions.forEach(function(division) {
                    divisionSelect.innerHTML += '<option value="' + division.division_id + '">' + division.name + '</option>';
                });
                $(divisionSelect).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function updateDepartments(divisionId = '') {
        fetch('get_department.php' + (divisionId ? '?divisionId=' + divisionId : ''))
            .then(response => response.json())
            .then(departments => {
                var departmentSelect = document.getElementById('departmentID');
                departmentSelect.innerHTML = '<option value="">เลือกทั้งหมด</option>';
                departments.forEach(function(department) {
                    departmentSelect.innerHTML += '<option value="' + department.department_id + '">' + department.name + '</option>';
                });
                $(departmentSelect).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function updateSections(departmentId = '') {
        fetch('get_section.php' + (departmentId ? '?departmentId=' + departmentId : ''))
            .then(response => response.json())
            .then(sections => {
                var sectionSelect = document.getElementById('sectionID');
                sectionSelect.innerHTML = '<option value="">เลือกทั้งหมด</option>';
                sections.forEach(function(section) {
                    sectionSelect.innerHTML += '<option value="' + section.section_id + '">' + section.name + '</option>';
                });
                $(sectionSelect).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>


</html>