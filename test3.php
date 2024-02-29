<?php
session_start();
require_once('./connection.php');

$currentYear = date('Y');
$currentDate = date('Y-m-d');
$startYear = $currentYear . '-01-01';
$filterData = $_SESSION['filter'] ?? null;

$baseConditions = "ot_record.date BETWEEN '{$startYear}' AND '{$currentDate}'";

// Define subqueries for each condition
// $subqueries = [
//     'default' => "
//     SELECT weekly_over_times.division_name, COUNT(*) AS OverTime36Count
//     FROM (
//         SELECT 
//             dv.name AS division_name, 
//             DATEPART(ISO_WEEK, ot_record.date) as week_num,
//             SUM(ot_record.attendance_hours) as weekly_hours
//         FROM 
//             ot_record
//             INNER JOIN employee e ON ot_record.employee_id = e.employee_id
//             INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
//             INNER JOIN section s ON cc.section_id = s.section_id
//             INNER JOIN department d ON s.department_id = d.department_id
//             INNER JOIN division dv ON d.division_id = dv.division_id
//             WHERE
//                 {$baseConditions}   
//             GROUP BY 
//                 dv.name, 
//                 DATEPART(ISO_WEEK, ot_record.date)
//             HAVING 
//                 SUM(ot_record.attendance_hours) > 36
//     ) AS weekly_over_times
//     GROUP BY weekly_over_times.division_name
//     ",
//     'section' => "
//     SELECT weekly_over_times.cost_center_code, COUNT(*) AS OverTime36Count
//     FROM (
//         SELECT 
//             cc.cost_center_code AS cost_center_code, 
//             DATEPART(ISO_WEEK, ot_record.date) as week_num,
//             SUM(ot_record.attendance_hours) as weekly_hours
//         FROM 
//             ot_record
//             INNER JOIN employee e ON ot_record.employee_id = e.employee_id
//             INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
//             WHERE
//                 cc.section_id = '{$filterData['sectionId']}' AND
//                 {$baseConditions}
//             GROUP BY 
//                 cc.cost_center_code, 
//                 DATEPART(ISO_WEEK, ot_record.date)
//             HAVING 
//                 SUM(ot_record.attendance_hours) > 36
//     ) AS weekly_over_times
//     GROUP BY weekly_over_times.cost_center_code
// ",
//     'department' => "
//     SELECT weekly_over_times.Section, COUNT(*) AS OverTime36Count
//     FROM (
//         SELECT 
//             s.name AS Section, 
//             DATEPART(ISO_WEEK, ot_record.date) as week_num,
//             SUM(ot_record.attendance_hours) as weekly_hours
//         FROM 
//             ot_record
//             INNER JOIN employee e ON ot_record.employee_id = e.employee_id
//             INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
//             INNER JOIN section s ON cc.section_id = s.section_id
//             INNER JOIN department d ON s.department_id = d.department_id
//             INNER JOIN division dv ON d.division_id = dv.division_id
//             WHERE
//                 s.name = '{$filterData['departmentId']}' AND
//                 {$baseConditions}
//             GROUP BY 
//                 s.name, 
//                 DATEPART(ISO_WEEK, ot_record.date)
//             HAVING 
//                 SUM(ot_record.attendance_hours) > 36
//     ) AS weekly_over_times
//     GROUP BY weekly_over_times.Section
//     ",
//     'division' => "
//     SELECT weekly_over_times.Department, COUNT(*) AS OverTime36Count
//     FROM (
//         SELECT 
//             d.name AS Department, 
//             DATEPART(ISO_WEEK, ot_record.date) as week_num,
//             SUM(ot_record.attendance_hours) as weekly_hours
//         FROM 
//             ot_record
//             INNER JOIN employee e ON ot_record.employee_id = e.employee_id
//             INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
//             INNER JOIN section s ON cc.section_id = s.section_id
//             INNER JOIN department d ON s.department_id = d.department_id
//             INNER JOIN division dv ON d.division_id = dv.division_id
//             WHERE
//                 d.name = '{$filterData['divisionId']}' AND
//                 {$baseConditions}
//             GROUP BY 
//                 d.name, 
//                 DATEPART(ISO_WEEK, ot_record.date)
//             HAVING 
//                 SUM(ot_record.attendance_hours) > 36
//     ) AS weekly_over_times
//     GROUP BY weekly_over_times.Department
//     "
// ];

// Decide which subquery to use based on filters
if ($filterData) {
    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $baseConditions = "ot_record.date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }

    if (!empty($filterData['sectionId'])) {
        $selectedSubquery = "
        SELECT weekly_over_times.cost_center_code AS Unit, COUNT(*) AS OverTime36Count
        FROM (
            SELECT 
                cc.cost_center_code AS cost_center_code, 
                DATEPART(ISO_WEEK, ot_record.date) as week_num,
                SUM(ot_record.attendance_hours) as weekly_hours
            FROM 
                ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                WHERE
                    cc.section_id = '{$filterData['sectionId']}' AND
                    {$baseConditions}
                GROUP BY 
                    cc.cost_center_code, 
                    DATEPART(ISO_WEEK, ot_record.date)
                HAVING 
                    SUM(ot_record.attendance_hours) > 36
        ) AS weekly_over_times
        GROUP BY weekly_over_times.cost_center_code";

    } elseif (!empty($filterData['departmentId'])) {
        $selectedSubquery = "
        SELECT weekly_over_times.Section AS Unit, COUNT(*) AS OverTime36Count
        FROM (
            SELECT 
                s.name AS Section, 
                DATEPART(ISO_WEEK, ot_record.date) as week_num,
                SUM(ot_record.attendance_hours) as weekly_hours
            FROM 
                ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN section s ON cc.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE
                    s.name = '{$filterData['departmentId']}' AND
                    {$baseConditions}
                GROUP BY 
                    s.name, 
                    DATEPART(ISO_WEEK, ot_record.date)
                HAVING 
                    SUM(ot_record.attendance_hours) > 36
        ) AS weekly_over_times
        GROUP BY weekly_over_times.Section
        ";
        // Modify the subquery for department condition here
    } elseif (!empty($filterData['divisionId'])) {
        $selectedSubquery = "
        SELECT weekly_over_times.Department AS Unit, COUNT(*) AS OverTime36Count
    FROM (
        SELECT 
            d.name AS Department, 
            DATEPART(ISO_WEEK, ot_record.date) as week_num,
            SUM(ot_record.attendance_hours) as weekly_hours
        FROM 
            ot_record
            INNER JOIN employee e ON ot_record.employee_id = e.employee_id
            INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
            INNER JOIN section s ON cc.section_id = s.section_id
            INNER JOIN department d ON s.department_id = d.department_id
            INNER JOIN division dv ON d.division_id = dv.division_id
            WHERE
                dv.division_id = '{$filterData['divisionId']}' AND
                {$baseConditions}
            GROUP BY 
                d.name, 
                DATEPART(ISO_WEEK, ot_record.date)
            HAVING 
                SUM(ot_record.attendance_hours) > 36
    ) AS weekly_over_times
    GROUP BY weekly_over_times.Department";
        // Modify the subquery for division condition here
    } else {
        $selectedSubquery = "
        SELECT weekly_over_times.division_name AS Unit, COUNT(*) AS OverTime36Count
        FROM (
            SELECT 
                dv.name AS division_name, 
                DATEPART(ISO_WEEK, ot_record.date) as week_num,
                SUM(ot_record.attendance_hours) as weekly_hours
            FROM 
                ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
                INNER JOIN section s ON cc.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE
                    {$baseConditions}   
                GROUP BY 
                    dv.name, 
                    DATEPART(ISO_WEEK, ot_record.date)
                HAVING 
                    SUM(ot_record.attendance_hours) > 36
        ) AS weekly_over_times
        GROUP BY weekly_over_times.division_name
        ";
    }
} else {
    $selectedSubquery = "
    SELECT weekly_over_times.division_name AS Unit , COUNT(*) AS OverTime36Count
    FROM (
        SELECT 
            dv.name AS division_name, 
            DATEPART(ISO_WEEK, ot_record.date) as week_num,
            SUM(ot_record.attendance_hours) as weekly_hours
        FROM 
            ot_record
            INNER JOIN employee e ON ot_record.employee_id = e.employee_id
            INNER JOIN costcenter cc ON e.CostcenterID = cc.cost_center_id
            INNER JOIN section s ON cc.section_id = s.section_id
            INNER JOIN department d ON s.department_id = d.department_id
            INNER JOIN division dv ON d.division_id = dv.division_id
            WHERE
                {$baseConditions}   
            GROUP BY 
                dv.name, 
                DATEPART(ISO_WEEK, ot_record.date)
            HAVING 
                SUM(ot_record.attendance_hours) > 36
    ) AS weekly_over_times
    GROUP BY weekly_over_times.division_name
    ";
}

// Execute the selected subquery
$stmt = sqlsrv_query($conn, $selectedSubquery);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Output the results in an HTML table
echo "<table border='1'>";
echo "<tr><th>Organization Unit</th><th>OT Over 36 Hours Count</th></tr>";
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr><td>" . htmlspecialchars($row['Unit']) . "</td>";
    echo "<td>" . htmlspecialchars($row['OverTime36Count']) . "</td></tr>";
}
echo "</table>";
