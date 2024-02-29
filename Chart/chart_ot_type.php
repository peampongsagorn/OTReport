<?php
//session_start();
require_once('./connection.php');

$currentYear = date('Y');
$filterData = $_SESSION['filter'] ?? null;
$currentYear = date('Y'); // ปีปัจจุบัน
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$currentDate = date('Y-m-d'); // วันที่ปัจจุบัน

$sqlConditions_actual = "date BETWEEN '{$startYear}' AND '{$currentDate}'";


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

$sql = "SELECT TOP 3
            otr.New_Request_msg AS Request_msg,
            SUM(otr.attendance_hours) AS SUM_HOURS
        FROM 
            ot_record as otr
        INNER JOIN 
            employee as e ON otr.employee_id = e.employee_id
        INNER JOIN 
            costcenter as cc ON e.CostcenterID = cc.cost_center_id
        INNER JOIN 
            section as s ON cc.section_id = s.section_id
        INNER JOIN
            department d ON s.department_id = d.department_id
        INNER JOIN 
            division dv ON d.division_id = dv.division_id
        WHERE 
            $sqlConditions_actual
        GROUP BY 
            otr.New_Request_msg
        ORDER BY
            SUM(otr.attendance_hours) DESC";


// Execute the SQL query
$stmt = sqlsrv_query($conn, $sql);

// Check if the query was successful
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Initialize an array to hold the query results
$results = array();

// Fetch the results from the query
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    array_push($results, $row);
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top 3 OT Hours</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>
</head>
<body>

<div class="col-md-auto" style="padding: 0; margin: 5px;">
    <div style="border: 2px solid #3E4080; max-width: 350px; box-shadow: 2px 4px 5px #3E4080;">
        <canvas id="otDonutChart" width="350" height="350"></canvas>
    </div>
</div>


<script>
// สร้างฟังก์ชันสำหรับการจัดรูปแบบค่า
function formatHours(value) {
    // แปลงค่าจากชั่วโมงเป็น 'K' สำหรับพันชั่วโมง
    return value >= 1000 ? (value / 1000).toFixed(1) + 'K Hrs' : value + ' Hrs';
}

document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('otDonutChart').getContext('2d');
    var otDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($results as $row) echo "'".htmlspecialchars($row['Request_msg'], ENT_QUOTES)."',"; ?>],
            datasets: [{
                data: [<?php foreach ($results as $row) echo htmlspecialchars($row['SUM_HOURS'], ENT_QUOTES).","; ?>],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Top 3 OT Hours',
                font: {
                        weight: 'bold',
                        size: 20
                    },
            },
            plugins: {
                datalabels: {
                    color: '#fff',
                    textAlign: 'center',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    formatter: function(value, context) {
                        // ใช้ฟังก์ชัน formatHours เพื่อจัดรูปแบบค่า
                        return formatHours(value);
                    }
                }
            },
        }
    });
});
</script>


</body>
</html>

