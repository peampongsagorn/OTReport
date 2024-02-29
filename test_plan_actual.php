<?php
session_start();
require_once('./connection.php');

$currentYear = date('Y');
$filterData = $_SESSION['filter'] ?? null;
$currentYear = date('Y'); // ปีปัจจุบัน
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$currentDate = date('Y-m-d'); // วันที่ปัจจุบัน

$sqlConditions_actual = "date BETWEEN '{$startYear}' AND '{$currentDate}'";
$sqlConditions_plan = "year = '{$currentYear}'"; // เงื่อนไขเริ่มต้นคือข้อมูลของปีปัจจุบัน

if ($filterData) {

    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_actual = "date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }
    if (!empty($filterData['startYear']) && !empty($filterData['endYearDecember'])) {
        $sqlConditions_plan = "year BETWEEN '{$filterData['startYear']}' AND '{$filterData['endYearDecember']}'";
    }
    if (!empty($filterData['startMonth']) && !empty($filterData['endMonthDecember'])) {
        $sqlConditions_plan .= " AND month BETWEEN '{$filterData['startMonth']}' AND '{$filterData['endMonthDecember']}'";
    }
    if (!empty($filterData['type'])) {
        $sqlConditions_actual .= " AND ot_record.TYPE_OT = '{$filterData['type']}'";
    }
    if (isset($filterData['type'])) {
        if ($filterData['type'] == 'OT FIX') {
            $totalHoursColumn = 'SUM(ot_plan.sum_fix)';
        } elseif ($filterData['type'] == 'OT NON FIX') {
            $totalHoursColumn = 'SUM(ot_plan.nonfix) ';
        } else {
            $totalHoursColumn = 'SUM(ot_plan.total_hours) ';
        }
    } else {
        $totalHoursColumn = 'SUM(ot_plan.total_hours) ';
    }
    // $filterDataType = $filterData['type'] ?? 'default';
    // $totalHoursColumn = match($filterDataType) {
    //     'OT FIX' => 'SUM(ot_plan.sum_fix)',
    //     'OT NON FIX' => 'SUM(ot_plan.nonfix)',
    //     default => 'SUM(ot_plan.total_hours)',
    // };
    if (!empty($filterData['sectionId'])) {
        $sqlConditions_actual .= " AND c.section_id = '{$filterData['sectionId']}'";
        $sqlConditions_plan .= " AND c.section_id = '{$filterData['sectionId']}'";
        $query = "SELECT 
                COALESCE(attendance.name, total_hours.name) AS name,
                COALESCE(SUM(attendance.attendance_hours), 0) AS attendance_hours,
                COALESCE(SUM(total_hours.total_hours), 0) AS total_hours
                FROM 
                (SELECT 
                    c.cost_center_code,
                    SUM(ot_record.attendance_hours) as attendance_hours
                FROM 
                    ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter c ON e.CostcenterID = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_actual}
                GROUP BY c.cost_center_code
                ) attendance
                FULL JOIN 
                (SELECT 
                    c.cost_center_code,
                    {$totalHoursColumn} as total_hours
                FROM 
                    ot_plan
                INNER JOIN costcenter c ON ot_plan.costcenter_id = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_plan}
                GROUP BY c.cost_center_code
                ) total_hours ON attendance.name = total_hours.name
                GROUP BY COALESCE(attendance.name, total_hours.name)";
    } elseif (!empty($filterData['departmentId'])) {
        $sqlConditions_actual .= " AND s.department_id = '{$filterData['departmentId']}'";
        $sqlConditions_plan .= " AND s.department_id = '{$filterData['departmentId']}'";
        $query = "SELECT 
                COALESCE(attendance.name, total_hours.name) AS name,
                COALESCE(SUM(attendance.attendance_hours), 0) AS attendance_hours,
                COALESCE(SUM(total_hours.total_hours), 0) AS total_hours
                FROM 
                (SELECT 
                    s.name,
                    SUM(ot_record.attendance_hours) as attendance_hours
                FROM 
                    ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter c ON e.CostcenterID = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_actual}
                GROUP BY s.name
                ) attendance
                FULL JOIN 
                (SELECT 
                    s.name,
                    {$totalHoursColumn} as total_hours
                FROM 
                    ot_plan
                INNER JOIN costcenter c ON ot_plan.costcenter_id = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_plan}
                GROUP BY s.name
                ) total_hours ON attendance.name = total_hours.name
                GROUP BY COALESCE(attendance.name, total_hours.name)";
    } elseif (!empty($filterData['divisionId'])) {
        $sqlConditions_actual .= " AND d.division_id = '{$filterData['divisionId']}'";
        $sqlConditions_plan .= " AND d.division_id = '{$filterData['divisionId']}'";
        $query = "SELECT 
                COALESCE(attendance.name, total_hours.name) AS name,
                COALESCE(SUM(attendance.attendance_hours), 0) AS attendance_hours,
                COALESCE(SUM(total_hours.total_hours), 0) AS total_hours
                FROM 
                (SELECT 
                    d.name,
                    {$totalHoursColumn} as attendance_hours
                FROM 
                    ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter c ON e.CostcenterID = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_actual}
                GROUP BY d.name
                ) attendance
                FULL JOIN 
                (SELECT 
                    d.name,
                    SUM(ot_plan.total_hours) as total_hours
                FROM 
                    ot_plan
                INNER JOIN costcenter c ON ot_plan.costcenter_id = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_plan}
                GROUP BY d.name
                ) total_hours ON attendance.name = total_hours.name
                GROUP BY COALESCE(attendance.name, total_hours.name)";
    } else {
        $query = "SELECT 
                COALESCE(attendance.name, total_hours.name) AS name,
                COALESCE(SUM(attendance.attendance_hours), 0) AS attendance_hours,
                COALESCE(SUM(total_hours.total_hours), 0) AS total_hours
                FROM 
                (SELECT 
                    dv.name,
                    SUM(ot_record.attendance_hours) as attendance_hours
                FROM 
                    ot_record
                INNER JOIN employee e ON ot_record.employee_id = e.employee_id
                INNER JOIN costcenter c ON e.CostcenterID = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_actual}
                GROUP BY dv.name
                ) attendance
                FULL JOIN 
                (SELECT 
                    dv.name,
                    {$totalHoursColumn}
                FROM 
                    ot_plan
                INNER JOIN costcenter c ON ot_plan.costcenter_id = c.cost_center_id
                INNER JOIN [section] s ON c.section_id = s.section_id
                INNER JOIN department d ON s.department_id = d.department_id
                INNER JOIN division dv ON d.division_id = dv.division_id
                WHERE {$sqlConditions_plan}
                GROUP BY dv.name
                ) total_hours ON attendance.name = total_hours.name
                GROUP BY COALESCE(attendance.name, total_hours.name)";
    }
}

$result = sqlsrv_query($conn, $query);

// Check if there are any results
$chartData = array();
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $chartData[] = $row;
    }
}



?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>OT Planning Trends</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
</head>

<body>
    <canvas id="otChart"></canvas>
    <script>
        var ctx = document.getElementById('otChart').getContext('2d');
        var chartData = <?php echo json_encode($chartData); ?>;

        var labels = chartData.map(function(item) {
            return item.name;
        });
        var attendanceData = chartData.map(function(item) {
            return item.attendance_hours;
        });
        var totalHoursData = chartData.map(function(item) {
            return item.total_hours;
        });

        var otChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Attendance Hours',
                    data: attendanceData,
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    yAxisID: 'y-axis-1',
                }, {
                    label: 'Plan Hours',
                    data: totalHoursData,
                    type: 'line',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false,
                    yAxisID: 'y-axis-1',
                    steppedLine: 'middle',
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        id: 'y-axis-1',
                        type: 'linear',
                        position: 'left',
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            callback: function(value, index, values) {
                                // เปลี่ยนจุดเป็น Unicode character ที่ใหญ่กว่า
                                return attendanceData[index] > totalHoursData[index] ? value + ' 🔴' : value;
                            },
                            fontColor: function(context) {
                                var index = context.index;
                                return attendanceData[index] > totalHoursData[index] ? 'red' : '#000';
                            }
                        }
                    }]
                },
                legend: {
                    labels: {
                        usePointStyle: true
                    }
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                plugins: {
                    datalabels: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>

</html>