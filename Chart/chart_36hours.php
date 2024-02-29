<?php
session_start();
require_once('./connection.php');

// เริ่มต้น ตั้งค่าสำหรับเงื่อนไขค้นหา
$currentYear = date('Y');
$currentDate = date('Y-m-d');
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$filterData = $_SESSION['filter'] ?? null;

// ตั้งค่าเงื่อนไขค้นหาเริ่มต้น
$sqlConditions_actual = "date BETWEEN '{$startYear}' AND '{$currentDate}'";

$sqlSelect = "weekly_over_times.division_name, COUNT(*) AS OverTime36Count";
$sqlFrom = "(
                SELECT 
                    dv.name AS division_name, 
                    DATEPART(ISO_WEEK, ot_record.date) as week_num,
                    SUM(ot_record.attendance_hours) as weekly_hours
                FROM 
                    ot_record
                    INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                    INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                    INNER JOIN section s ON  cc.section_id = s.section_id
                    INNER JOIN department d ON s.department_id = d.department_id
                    INNER JOIN division dv ON d.division_id = dv.division_id
                    WHERE
                        $sqlConditions_actual
                GROUP BY 
                    dv.name, 
                    DATEPART(ISO_WEEK, ot_record.date)
                HAVING 
                    SUM(ot_record.attendance_hours) > 36
            ) AS weekly_over_times";
$sqlGroupBy = "weekly_over_times.division_name";


//query เฉลี่ยต่อคนแต่ยังไม่หารวัน
if ($filterData) {

    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_actual = "date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }

    if (!empty($filterData['sectionId'])) {
        $sqlConditions_actual .= " AND cc.section_id = '{$filterData['sectionId']}'";
        $sqlSelect = "weekly_over_times.cost_center_code, COUNT(*) AS OverTime36CountT";
        $sqlFrom = "(
            SELECT 
                cc.cost_center_code AS cost_center_code, 
                DATEPART(ISO_WEEK, ot_record.date) as week_num,
                SUM(ot_record.attendance_hours) as weekly_hours
            FROM 
                ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN section s ON  cc.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE
                    $sqlConditions_actual
            GROUP BY 
                cc.cost_center_code, 
                DATEPART(ISO_WEEK, ot_record.date)
            HAVING 
                SUM(ot_record.attendance_hours) > 36
        ) AS weekly_over_times";
        $sqlGroupBy = "weekly_over_times.cost_center_code";



    } elseif (!empty($filterData['departmentId'])) {
        $sqlSelect = "weekly_over_times.section_name, COUNT(*) AS OverTime36Count";
        $sqlConditions_actual .= " AND s.department_id = '{$filterData['departmentId']}'";
        $sqlFrom = "(
            SELECT 
                s.name AS section_name, 
                DATEPART(ISO_WEEK, ot_record.date) as week_num,
                SUM(ot_record.attendance_hours) as weekly_hours
            FROM 
                ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN section s ON  cc.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE
                    $sqlConditions_actual
            GROUP BY 
                s.name, 
                DATEPART(ISO_WEEK, ot_record.date)
            HAVING 
                SUM(ot_record.attendance_hours) > 36
        ) AS weekly_over_times";
        $sqlGroupBy = "weekly_over_times.section_name";



    } elseif (!empty($filterData['divisionId'])) {
        $sqlSelect = "weekly_over_times.department_name, COUNT(*) AS OverTime36Count";
        $sqlConditions_actual .= " AND d.division_id = '{$filterData['divisionId']}'";
        $sqlFrom = "(
            SELECT 
                d.name AS department_name, 
                DATEPART(ISO_WEEK, ot_record.date) as week_num,
                SUM(ot_record.attendance_hours) as weekly_hours
            FROM 
                ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN section s ON  cc.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE
                    $sqlConditions_actual
            GROUP BY 
                d.name, 
                DATEPART(ISO_WEEK, ot_record.date)
            HAVING 
                SUM(ot_record.attendance_hours) > 36
        ) AS weekly_over_times";
        $sqlGroupBy = "weekly_over_times.department_name";
    }
}

// คำสั่ง SQL สำหรับการดึงข้อมูล OT
$sql = "SELECT 
            $sqlSelect
        FROM
            $sqlFrom
        GROUP BY 
            $sqlGroupBy";
            


// ดำเนินการ query และเก็บผลลัพธ์
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// เริ่มต้นตาราง HTML
echo "<table border='1'>";
echo "<tr>
        <th>Division/Department/Section</th>
        <th>จำนวนครั้งที่ทำ OT เกิน 36 ชม/สัปดาห์</th>
      </tr>";

// วนลูปผ่านผลลัพธ์ที่ได้จากการ query และแสดงในตาราง
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['division_name'] ?? $row['cost_center_code'] ?? $row['section_name'] ?? $row['department_name']) . "</td>"; // ใช้ ?? เพื่อตรวจสอบค่าที่ไม่ได้กำหนด
    echo "<td>" . htmlspecialchars($row['OverTime36Count']) . "</td>";
    echo "</tr>";
}

echo "</table>";


?>


<!-- <html>

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
        }
    </style>

</head>

<body>
    <table class="data-table2 table striped hover nowrap" style="width: 100%; border-collapse: collapse; border: 2px solid #3E4080; box-shadow: 2px 4px 5px #3E4080; height: 100%">
        <thead style="background-color: #1C1D3A; color: white;">
            <tr>
                <th>Department</th>

                <th>จำนวนครั้งที่ทำ OT เกิน 36 ชม/สัปดาห์</th>

            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($top10Employees as $employee) {
                $avgOtPerDay = $totalWorkingDays > 0 ? $employee['TOTAL_HOURS'] / $totalWorkingDays : 0;
                echo '<tr style="background-color: #D4E8E5; color: #757575;">';
                
                echo '<td>' . htmlspecialchars($employee['DEPARTMENT']) . '</td>';    
  
                echo '<td>' . htmlspecialchars($employee['OT_EXCEEDS']) . '</td>';    

                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</body>


</html> -->