<?php
//session_start();
require_once('./connection.php');

// เริ่มต้น ตั้งค่าสำหรับเงื่อนไขค้นหา
$currentYear = date('Y');
$currentDate = date('Y-m-d');
$filterData = $_SESSION['filter'] ?? null;

// ตั้งค่าเงื่อนไขค้นหาเริ่มต้น
$sqlConditions_actual = "date BETWEEN '{$currentYear}-01-01' AND '{$currentDate}'";

if ($filterData) {
    // ปรับปรุงเงื่อนไขค้นหาตามข้อมูลที่ได้รับจาก filter
    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_actual = "date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }

    // ปรับปรุงเงื่อนไขค้นหาตาม section, department และ division ถ้ามี
    if (!empty($filterData['sectionId'])) {
        $sqlConditions_actual .= " AND cc.section_id = '{$filterData['sectionId']}'";
    } elseif (!empty($filterData['departmentId'])) {
        $sqlConditions_actual .= " AND s.department_id = '{$filterData['departmentId']}'";
    } elseif (!empty($filterData['divisionId'])) {
        $sqlConditions_actual .= " AND d.division_id = '{$filterData['divisionId']}'";
    }
}

// คำสั่ง SQL สำหรับการดึงข้อมูล OT
$sql = "SELECT 
            e.employee_id,
            CONCAT(e.FIRSTNAME_T, ' ', e.LASTNAME_T) AS EMPLOYEE_NAME,
            d.name AS DEPARTMENT,
            s.name AS SECTION,
            SUM(CASE WHEN otr.TYPE_OT = 'OT FIX' THEN otr.attendance_hours ELSE 0 END) AS FIX,
            SUM(CASE WHEN otr.TYPE_OT = 'OT NON FIX' THEN otr.attendance_hours ELSE 0 END) AS NONFIX,
            SUM(otr.attendance_hours) AS TOTAL_HOURS,
            MAX(COALESCE(OverTime36.OverTime36Count, 0)) AS OT_EXCEEDS
        FROM 
            ot_record as otr
        INNER JOIN 
            employee as e ON otr.employee_id = e.employee_id
        INNER JOIN 
            costcenter as cc ON e.CostcenterID = cc.cost_center_id
        INNER JOIN 
            section as s ON cc.section_id = s.section_id
        INNER JOIN 
            department as d ON s.department_id = d.department_id
        INNER JOIN 
            division as dv ON d.division_id = dv.division_id
        LEFT JOIN (
            SELECT 
                employee_id, 
                COUNT(*) AS OverTime36Count
            FROM (
                SELECT 
                    employee_id, 
                    DATEPART(ISO_WEEK, date) as week_num,
                    SUM(attendance_hours) as weekly_hours
                FROM 
                    ot_record
                GROUP BY 
                    employee_id, 
                    DATEPART(ISO_WEEK, date)
                HAVING 
                    SUM(attendance_hours) > 36
            ) AS weekly_over_times
            GROUP BY 
                employee_id
            ) AS OverTime36 ON OverTime36.employee_id = otr.employee_id
        WHERE
            $sqlConditions_actual
        GROUP BY 
            e.employee_id, 
            CONCAT(e.FIRSTNAME_T, ' ', e.LASTNAME_T),
            d.name,
            s.name ";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// สร้าง array สำหรับข้อมูลที่จะแสดง
$employeeOTData = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employeeOTData[] = $row;
}

// คำสั่ง SQL สำหรับการดึงจำนวนวันทำงานจาก ot_plan
if ($filterData) {

    if (!empty($filterData['startYear']) && !empty($filterData['endYearDecember'])) {
        $sqlConditions_plan = "year BETWEEN '{$filterData['startYear']}' AND '{$filterData['endYearDecember']}'";
    }
    if (!empty($filterData['startMonth']) && !empty($filterData['endMonthDecember'])) {
        $sqlConditions_plan .= " AND month BETWEEN '{$filterData['startMonth']}' AND '{$filterData['endMonthDecember']}'";
    }
}
$sql = "SELECT 
	            SUM(otp.working_day) / COUNT(DISTINCT(otp.costcenter_id)) AS WorkingDay
            FROM
	            ot_plan otp
            WHERE
                {$sqlConditions_plan}";


$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$totalWorkingDays = $row['WorkingDay'] ?? 0;

// เรียงลำดับและเลือกเฉพาะ 10 รายการแรก
usort($employeeOTData, function ($a, $b) {
    return $b['TOTAL_HOURS'] <=> $a['TOTAL_HOURS'];
});

// เลือกพนักงาน 10 คนแรก
$top10Employees = array_slice($employeeOTData, 0, 50);

// คำนวณเปอร์เซ็นต์และรูปแบบข้อมูลชั่วโมงพร้อมเปอร์เซ็นต์ในวงเล็บ
foreach ($top10Employees as &$employee) {
    $employee['FIX_PERCENT'] = ($employee['TOTAL_HOURS'] > 0) ? ($employee['FIX'] / $employee['TOTAL_HOURS'] * 100) : 0;
    $employee['NONFIX_PERCENT'] = ($employee['TOTAL_HOURS'] > 0) ? ($employee['NONFIX'] / $employee['TOTAL_HOURS'] * 100) : 0;

    $employee['FIX'] = sprintf("%.2f (%.2f%%)", $employee['FIX'], $employee['FIX_PERCENT']);
    $employee['NONFIX'] = sprintf("%.2f (%.2f%%)", $employee['NONFIX'], $employee['NONFIX_PERCENT']);
}
unset($employee); // ลบการอ้างอิงเมื่อเสร็จสิ้นการใช้งาน



?>


<html>

<head>
    <style>
        .table {
            width: 90%;
            margin: auto;
        }

        thead th {
            font-size: 14px;

        }

        tbody {
            font-size: 12px;
        }

        th,
        td {
            padding: 3px;
            text-align: left;
        }

        .chart-container-table {

            border: 2px solid #3E4080;
            box-shadow: 2px 4px 5px #3E4080;
            border-radius: 25px;
        }   

        /* .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #4CAF50;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:active,
        .dataTables_wrapper .dataTables_paginate .paginate_button:focus {
            background-color: #4CAF50 !important;
            color: white !important;
        }

        .dataTables_top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .dataTables_filter label {
            display: flex;
            align-items: center;
            margin-bottom: 0;
        }

        .dataTables_filter input {
            margin-left: 0.5em;
        } */
    </style>

</head>

<body>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.1/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.0.1/js/dataTables.js"></script>
    <div class="chart-container-table">
        <div class="dataTables_top">
            <div class="dataTables_length"></div>
            <div class="dataTables_filter"></div>
        </div>
        <table class="data-table table striped hover nowrap" style="width: 100%; border-collapse: collapse; border: 2px solid #3E4080; box-shadow: 2px 4px 5px #3E4080; height: 100%; margin-top:10px">
            <thead style="background-color: #1C1D3A; color: white;">
                <tr>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Total Hours</th>
                    <th>OT เกิน 36 ชม/สัปดาห์ (ครั้ง)</th>
                    <th>OT FIX</th>
                    <th>OT NONFIX</th>
                    <th>AVG OT/Day</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($top10Employees as $employee) {
                    $avgOtPerDay = $totalWorkingDays > 0 ? $employee['TOTAL_HOURS'] / $totalWorkingDays : 0;
                    echo '<tr style="background-color: #D4E8E5; color: #757575;">';
                    echo '<td>' . htmlspecialchars($employee['EMPLOYEE_NAME']) . '</td>';
                    echo '<td>' . htmlspecialchars($employee['DEPARTMENT']) . '</td>';
                    echo '<td>' . number_format($employee['TOTAL_HOURS'], 0) . '</td>';
                    echo '<td>' . htmlspecialchars($employee['OT_EXCEEDS']) . '</td>';
                    // echo '<td>' . (is_numeric($employee['FIX']) ? number_format($employee['FIX'], 0) : $employee['FIX']) . '</td>';
                    // echo '<td>' . (is_numeric($employee['NONFIX']) ? number_format($employee['NONFIX'], 0) : $employee['NONFIX']) . '</td>';
                    echo '<td>' . number_format($employee['FIX'], 0) . '</td>';
                    echo '<td>' . number_format($employee['NONFIX'], 0) . '</td>';
                    echo '<td>' . number_format($avgOtPerDay, 2) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

<script>
    $(document).ready(function() {
    $('.data-table').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: "datatable-nosort",
            orderable: false,
        }],
        "lengthMenu": [
            [10, 20, 35, -1],
            [10, 20, 35, "ทั้งหมด"]
        ],
        "language": {
            "info": "หน้า _START_ - _END_ จากทั้งหมด _TOTAL_ รายการ",
            "lengthMenu": "แสดง _MENU_ รายการ",
            "search": "<span style='color: #7a7a7a'><i class='fa-solid fa-magnifying-glass'></i> ค้นหา:</span>",
            "paginate": {
                "next": '▶',
                "previous": '◀'
            },
            "infoEmpty": "ไม่มีรายการที่แสดง",
            "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
            searchPlaceholder: "ค้นหา",
        }
    });
});


</script>
</html>