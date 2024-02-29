<?php
session_start();
require_once('./connection.php');

$currentYear = date('Y');
$currentDate = date('Y-m-d');
$startYear = $currentYear . '-01-01';
$filterData = $_SESSION['filter'] ?? null;

// Set the conditions for the actual data
$sqlConditions_actual = "ot_record.date BETWEEN '{$startYear}' AND '{$currentDate}'";

if ($filterData) {
    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_actual = "ot_record.date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }
}

// Construct the subquery
$subQuery = "
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
        $sqlConditions_actual
    GROUP BY 
        dv.name, 
        DATEPART(ISO_WEEK, ot_record.date)
    HAVING 
        SUM(ot_record.attendance_hours) > 36
";

// Construct the main query
$sql = "
    SELECT 
        division_name, 
        COUNT(*) AS OverTime36Count
    FROM 
        ($subQuery) AS weekly_over_times
    GROUP BY 
        division_name
";

// Execute the query
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Output the results in an HTML table
echo "<table border='1'>";
echo "<tr>
        <th>Division Name</th>
        <th>OT Over 36 Hours Count</th>
      </tr>";

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['division_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['OverTime36Count']) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
