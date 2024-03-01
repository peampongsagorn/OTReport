<?php
session_start();
require_once('../connection.php');

// เริ่มต้น ตั้งค่าสำหรับเงื่อนไขค้นหา
$currentYear = date('Y');
$currentDate = date('Y-m-d');
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$filterData = $_SESSION['filter'] ?? null;

// ตั้งค่าเงื่อนไขค้นหาเริ่มต้น
$sqlConditions_date = "date BETWEEN '{$startYear}' AND '{$currentDate}'";
$sqlConditions = "";
$sql = "SELECT 
                    dv.name,
                    COUNT(e.employee_id) AS EmployeesExceeding36Hours
                FROM (
                    SELECT 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date) AS WeekNumber, 
                        SUM(ot.attendance_hours) AS WeeklyHours
                    FROM 
                        ot_record ot
                    WHERE 
                        {$sqlConditions_date}
                    GROUP BY 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date)
                    HAVING 
                        SUM(ot.attendance_hours) > 36
                    ) AS WeeklyOT
                INNER JOIN 
                    employee e ON WeeklyOT.employee_id = e.employee_id
                INNER JOIN 
                    costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN
                        section s on cc.section_id = s.section_id
                INNER JOIN 
                        department d ON s.department_id = d.department_id
                INNER JOIN
                        division dv on d.division_id = dv.division_id
                GROUP BY 
                    dv.name; ";


//query เฉลี่ยต่อคนแต่ยังไม่หารวัน
if ($filterData) {

    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_date = "date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }

    if (!empty($filterData['sectionId'])) {
        $sqlConditions = "cc.section_id = '{$filterData['sectionId']}'";
        $sql = "SELECT 
                    cc.cost_center_code,
                    COUNT(DISTINCT e.employee_id) AS EmployeesExceeding36Hours
                FROM (
                    SELECT 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date) AS WeekNumber, 
                        SUM(ot.attendance_hours) AS WeeklyHours
                    FROM 
                        ot_record ot
                    WHERE 
                        {$sqlConditions_date}
                    GROUP BY 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date)
                    HAVING 
                        SUM(ot.attendance_hours) > 36
                ) AS WeeklyOT
                INNER JOIN 
                    employee e ON WeeklyOT.employee_id = e.employee_id
                INNER JOIN 
                    costcenter cc ON e.CostcenterID = cc.cost_center_id
                WHERE
                    {$sqlConditions}
                GROUP BY 
                    cc.cost_center_code;";



    } elseif (!empty($filterData['departmentId'])) {
        $sqlConditions= "s.department_id = '{$filterData['departmentId']}'";
        $sql = "SELECT 
                    s.name,
                    COUNT(DISTINCT e.employee_id) AS EmployeesExceeding36Hours
                FROM (
                    SELECT 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date) AS WeekNumber, 
                        SUM(ot.attendance_hours) AS WeeklyHours
                    FROM 
                        ot_record ot
                    WHERE 
                        {$sqlConditions_date}
                    GROUP BY 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date)
                    HAVING 
                        SUM(ot.attendance_hours) > 36
                ) AS WeeklyOT
                INNER JOIN 
                    employee e ON WeeklyOT.employee_id = e.employee_id
                INNER JOIN 
                    costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN
                    section s on cc.section_id = s.section_id
                WHERE 
                    {$sqlConditions}
                GROUP BY 
                    s.name;";

    } elseif (!empty($filterData['divisionId'])) {
        $sqlConditions = "d.division_id = '{$filterData['divisionId']}'";
        $sql = "SELECT 
                    d.name,
                    COUNT(e.employee_id) AS EmployeesExceeding36Hours
                FROM (
                    SELECT 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date) AS WeekNumber, 
                        SUM(ot.attendance_hours) AS WeeklyHours
                    FROM 
                        ot_record ot
                    WHERE 
                        {$sqlConditions_date}
                    GROUP BY 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date)
                    HAVING 
                        SUM(ot.attendance_hours) > 36
                ) AS WeeklyOT
                INNER JOIN 
                    employee e ON WeeklyOT.employee_id = e.employee_id
                INNER JOIN 
                    costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN
                        section s on cc.section_id = s.section_id
                INNER JOIN 
                        department d ON s.department_id = d.department_id
                INNER JOIN
                        division dv on d.division_id = dv.division_id
                WHERE 
                        {$sqlConditions}
                GROUP BY 
                    d.name;
                        ";
    } else {
        $sql = "SELECT 
                    dv.name,
                    COUNT(e.employee_id) AS EmployeesExceeding36Hours
                FROM (
                    SELECT 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date) AS WeekNumber, 
                        SUM(ot.attendance_hours) AS WeeklyHours
                    FROM 
                        ot_record ot
                    WHERE 
                        {$sqlConditions_date}
                    GROUP BY 
                        ot.employee_id, 
                        DATEPART(ISO_WEEK, ot.date)
                    HAVING 
                        SUM(ot.attendance_hours) > 36
                    ) AS WeeklyOT
                INNER JOIN 
                    employee e ON WeeklyOT.employee_id = e.employee_id
                INNER JOIN 
                    costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN
                        section s on cc.section_id = s.section_id
                INNER JOIN 
                        department d ON s.department_id = d.department_id
                INNER JOIN
                        division dv on d.division_id = dv.division_id
                GROUP BY 
                    dv.name; ";
    }
}



            
// ดำเนินการ query และเก็บผลลัพธ์
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$employeeOTData = [];
while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employeeOTData[] = $row; // เก็บข้อมูลลงในอาร์เรย์
}

// เรียงลำดับข้อมูลตามจำนวนครั้งที่ทำ OT เกิน 36 ชั่วโมง
usort($employeeOTData, function($a, $b) {
    return $b['EmployeesExceeding36Hours'] <=> $a['EmployeesExceeding36Hours'];
});

?>



<html>
<head>
    <style>
        .table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            border: 2px solid #3E4080;
            box-shadow: 2px 4px 5px #3E4080;
        }

        thead th {
            font-size: 14px;
            background-color: #1C1D3A;
            color: white;
            padding: 3px;
            text-align: center;
        }

        tbody td {
            font-size: 12px;
            background-color: #D4E8E5;
            color: #757575;
            padding: 3px;
            text-align: center;
        }
    </style>
</head>
<body>
<table class="table">
        <thead>
            <tr>
                <th>Division/Department/Section Name</th>
                <th>Employees Exceeding 36 Hours</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employeeOTData as $employee): ?>
                <tr>
                    <td><?= htmlspecialchars($employee['name']) ?></td>
                    <td><?= $employee['EmployeesExceeding36Hours'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>