<?php
//session_start();
require_once('./connection.php');

$currentYear = date('Y');
$filterData = $_SESSION['filter'] ?? null;

$currentYear = date('Y'); // ปีปัจจุบัน
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$currentDate = date('Y-m-d'); // วันที่ปัจจุบัน

$sqlConditions_actual = "date BETWEEN '{$startYear}' AND '{$currentDate}'";
$sqlConditions_plan = "year = '{$currentYear}'"; // เงื่อนไขเริ่มต้นคือข้อมูลของปีปัจจุบัน
$isDepartmentSpecific = !empty($filterData['departmentId']);
$sqlSelect = "dv.name AS NAME, SUM(otr.attendance_hours) / NULLIF(COUNT(DISTINCT(otr.employee_id)),0) AS AVERAGE_OT";
$sqlGroupBy = "dv.name";

$sqlSelect1 = "dv.name AS NAME,SUM(otp.working_day) / COUNT(DISTINCT(otp.costcenter_id)) AS Working_day";
$sqlGroupBy1 = "dv.name";


//query เฉลี่ยต่อคนแต่ยังไม่หารวัน
if ($filterData) {

    if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
        $sqlConditions_actual = "date BETWEEN '{$filterData['startMonthDate']}' AND '{$filterData['endMonthDateCurrent']}'";
    }

    if (!empty($filterData['sectionId'])) {
        $sqlSelect = "cc.cost_center_code AS NAME, SUM(otr.attendance_hours) / NULLIF(COUNT(DISTINCT(otr.employee_id)),0) AS AVERAGE_OT";
        $sqlConditions_actual .= " AND cc.section_id = '{$filterData['sectionId']}'";
        $sqlGroupBy = "cc.cost_center_code";
    } elseif (!empty($filterData['departmentId'])) {
        $sqlSelect = "s.name AS NAME, SUM(otr.attendance_hours) / NULLIF(COUNT(DISTINCT(otr.employee_id)),0) AS AVERAGE_OT";
        $sqlConditions_actual .= " AND s.department_id = '{$filterData['departmentId']}'";
        $sqlGroupBy = "s.name";
    } elseif (!empty($filterData['divisionId'])) {
        $sqlSelect = "d.name AS NAME, SUM(otr.attendance_hours) / NULLIF(COUNT(DISTINCT(otr.employee_id)),0) AS AVERAGE_OT";
        $sqlConditions_actual .= " AND d.division_id = '{$filterData['divisionId']}'";
        $sqlGroupBy = "d.name";
    }
}
$sql = "SELECT 
            $sqlSelect 
        FROM 
            ot_record as otr
        INNER JOIN 
            employee as emp ON otr.employee_id = emp.employee_id
        INNER JOIN 
            costcenter as cc ON emp.CostcenterID = cc.cost_center_id
        INNER JOIN
             section as s ON cc.section_id = s.section_id
        INNER JOIN 
            department as d ON s.department_id = d.department_id
        INNER JOIN
            division as dv on d.division_id = dv.division_id
        WHERE
            {$sqlConditions_actual}
        GROUP BY
            $sqlGroupBy";

$stmt = sqlsrv_query($conn, $sql);
$chartData = [];

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $chartData[] = [
        'name' => $row['NAME'],
        'average_ot' => $row['AVERAGE_OT']
    ];
}

//  
//query วันทำงาน

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

$totalOTPercentForActual = $row['WorkingDay'] ?? 0;
//echo $totalOTPercentForActual

// ตรวจสอบก่อนว่า $totalOTPercentForActual มีค่ามากกว่า 0 เพื่อหลีกเลี่ยงการหารด้วยศูนย์
if ($totalOTPercentForActual > 0) {
    foreach ($chartData as $key => $value) {
        // หารค่าเฉลี่ย OT ด้วยจำนวนวันทำงานที่ได้จาก ot_plan และปรับปรุงค่าใน array
        $chartData[$key]['average_ot'] = $value['average_ot'] / $totalOTPercentForActual;
    }
} else {
    // ถ้าไม่มีวันทำงาน, อาจจะต้องการจัดการกับสถานการณ์นี้ เช่น การตั้งค่าเป็น 0 หรือค่าเริ่มต้นอื่น
    foreach ($chartData as $key => $value) {
        $chartData[$key]['average_ot'] = 0; // หรือค่าที่เหมาะสมอื่นๆ
    }
}









$chartDataJson = json_encode($chartData);
?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Average OT Per Person Actual</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.7.0"></script>    
    <style>
        .chart-container-bar {
            position: relative;
            margin: auto;
            height: 60vh; /* คุณสามารถปรับขนาดตามที่ต้องการ */
            width: 80vw; /* คุณสามารถปรับขนาดตามที่ต้องการ */
        }
    </style>
</head>
<body>

<div class="chart-container-bar">
    <canvas id="barChart"></canvas>
</div>

<script>
    var chartData = <?php echo $chartDataJson; ?>; // ตัวอย่างข้อมูลจากเซิร์ฟเวอร์

    var ctx = document.getElementById('barChart').getContext('2d');
    var barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(function(item) { return item.name; }), // ปรับให้เหมาะสมกับชื่อแต่ละแผนกหรือส่วนงาน
            datasets: [{
                label: 'Average OT Per Person',
                data: chartData.map(function(item) { return item.average_ot; }), // ปรับให้เหมาะสมกับข้อมูล
                backgroundColor: 'rgba(0, 123, 255, 0.5)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>

</body>
</html>

