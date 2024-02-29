<?php
//session_start();
require_once('./connection.php');
$currentYear = date('Y');
$filterData = $_SESSION['filter'] ?? null;
$sqlConditions_plan = "year = '{$currentYear}'"; // เงื่อนไขเริ่มต้นคือข้อมูลของปีปัจจุบัน
$sqlConditions_actual_working_day = "year = '{$currentYear}'";

$currentYear = date('Y'); // ปีปัจจุบัน
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$currentDate = date('Y-m-d'); // วันที่ปัจจุบัน

$sqlConditions_actual = "date BETWEEN '{$startYear}' AND '{$currentDate}'";
$sqlselect= "SUM(otp.total_hours) AS totalHours, SUM(otp.cal_percent_OT_plan_normaltime) AS totalOTPercent" ;

//query %Plan OT compare Normal Time
if ($filterData) {
    if (!empty($filterData['startYear']) && !empty($filterData['endYearDecember'])) {
        $sqlConditions_plan = "year BETWEEN '{$filterData['startYear']}' AND '{$filterData['endYearDecember']}'";
    }

    if (!empty($filterData['startMonth']) && !empty($filterData['endMonthDecember'])) {
        $sqlConditions_plan .= " AND month BETWEEN '{$filterData['startMonth']}' AND '{$filterData['endMonthDecember']}'";
    }
    if (!empty($filterData['type'])) {
        if ($filterData['type'] == 'OT FIX') {
            $sqlselect = "  SUM(otp.sum_fix)  AS totalHours, SUM(otp.cal_percent_OT_plan_normaltime) AS totalOTPercent";
        } elseif ($filterData['type'] == 'OT NON FIX') {
            // หาก type เป็น OT NON FIX, เพิ่มเงื่อนไขสำหรับ nonfix
            $sqlselect = "  SUM(otp.nonfix)  AS totalHours, SUM(otp.cal_percent_OT_plan_normaltime) AS totalOTPercent ";
        }
    }

    if (!empty($filterData['sectionId'])) {
        $sqlConditions_plan .= " AND cc.section_id = '{$filterData['sectionId']}'";
    } elseif (!empty($filterData['departmentId'])) {
        $sqlConditions_plan .= " AND s.department_id = '{$filterData['departmentId']}'";
    } elseif (!empty($filterData['divisionId'])) {
        $sqlConditions_plan .= " AND d.division_id = '{$filterData['divisionId']}'";
    }
}

$sql = "SELECT 
            $sqlselect
        FROM 
            ot_plan otp
        INNER JOIN 
            costcenter cc ON otp.costcenter_id = cc.cost_center_id
        INNER JOIN 
            section s ON cc.section_id = s.section_id
        INNER JOIN
            department d ON s.department_id = d.department_id
        INNER JOIN 
            division dv ON d.division_id = dv.division_id
    
        WHERE 
            $sqlConditions_plan";


$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($data['totalOTPercent'] != 0) {
    $percentage_plan = ($data['totalHours'] / $data['totalOTPercent']) * 100;
} else {
    $percentage_plan = 0;
}

//query ชั่วโมงการทำงานจริง
if ($filterData) {

    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_actual = "date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }
    if (!empty($filterData['type'])) {
        $sqlConditions_actual .= " AND otr.TYPE_OT = '{$filterData['type']}'";
    }
    if (!empty($filterData['sectionId'])) {
        $sqlConditions_actual .= " AND cc.section_id = '{$filterData['sectionId']}'";
    } elseif (!empty($filterData['departmentId'])) {
        $sqlConditions_actual .= " AND s.department_id = '{$filterData['departmentId']}'";
    } elseif (!empty($filterData['divisionId'])) {
        $sqlConditions_actual .= " AND d.division_id = '{$filterData['divisionId']}'";
    }
}

$sql = "SELECT 
            SUM(otr.attendance_hours) AS totalAttendanceHours
        FROM 
            ot_record otr
        INNER JOIN 
            employee e ON otr.employee_id = e.employee_id
        INNER JOIN 
            costcenter cc ON e.CostcenterID = cc.cost_center_id
        INNER JOIN 
            section s ON cc.section_id = s.section_id
        INNER JOIN
            department d ON s.department_id = d.department_id
        INNER JOIN 
            division dv ON d.division_id = dv.division_id

        WHERE 
            {$sqlConditions_actual}
        ";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$totalAttendanceHours = $row['totalAttendanceHours'] ?? 0;

//query cal_percent_OT_plan_normaltime FOR ACTUAL
if ($filterData) {
    if (!empty($filterData['startYear']) && !empty($filterData['endYearDecember'])) {
        $sqlConditions_plan = "year BETWEEN '{$filterData['startYear']}' AND '{$filterData['endYearDecember']}'";
    }

    if (!empty($filterData['startMonth']) && !empty($filterData['endMonthDecember'])) {
        $sqlConditions_plan .= " AND month BETWEEN '{$filterData['startMonth']}' AND '{$filterData['endMonthDecember']}'";
    }

    if (!empty($filterData['sectionId'])) { 
        $sqlConditions_plan .= " AND cc.section_id = '{$filterData['sectionId']}'";
    } elseif (!empty($filterData['departmentId'])) {
        $sqlConditions_plan .= " AND s.department_id = '{$filterData['departmentId']}'";
    } elseif (!empty($filterData['divisionId'])) {
        $sqlConditions_plan .= " AND d.division_id = '{$filterData['divisionId']}'";
    }
}
$sql = "SELECT 
            SUM(otp.cal_percent_OT_plan_normaltime) AS totalOTPercentForActual 
        FROM 
            ot_plan otp
        INNER JOIN 
            costcenter cc ON otp.costcenter_id = cc.cost_center_id
        INNER JOIN 
            section s ON cc.section_id = s.section_id
        INNER JOIN
            department d ON s.department_id = d.department_id
        INNER JOIN 
            division dv ON d.division_id = dv.division_id

        WHERE 
            {$sqlConditions_plan}
        ";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$totalOTPercentForActual = $row['totalOTPercentForActual'] ?? 0;



if ($totalOTPercentForActual != 0) {
    $percentage_actual = ($totalAttendanceHours / $totalOTPercentForActual) * 100;
} else {
    $percentage_actual = 0;
}

$diff_percentage_plan_actual = $percentage_plan - $percentage_actual;

if ($percentage_plan > $percentage_actual) {
    $colorClass = '#09B39D';
} else {
    $colorClass = '#F06549';
}

?>


    