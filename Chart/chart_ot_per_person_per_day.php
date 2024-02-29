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
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', { 'packages': ['bar'] });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Department/Section');
            data.addColumn('number', 'Average OT Per Person');

            var chartData = JSON.parse('<?php echo $chartDataJson; ?>');
            chartData.forEach(function (row) {
                data.addRow([row.name, parseFloat(row.average_ot)]);
            });

            var options = {
                chart: { title: 'Average OT Per Person Actual' },
                backgroundColor: '#1C1D3A',
                chartArea: {
                    backgroundColor: '#1C1D3A',
                    // กำหนด padding หรือ margin ถ้าจำเป็น
                },
                bars: 'horizontal',
                hAxis: { format: 'decimal' },
                height: 400,
                colors: ['#1b9e77', '#d95f02', '#7570b3'],
                legend: {
                    position: 'top', // ตั้งค่าตำแหน่งของ legend
                    textStyle: {
                        color: 'white', // ตั้งค่าสีของตัวอักษร
                        fontSize: 12 // ตั้งค่าขนาดของตัวอักษร
                    }
                },
                vAxis: {
                    title: 'ชั่วโมง',
                    textStyle: { color: 'white' } // กำหนดสีข้อความของแกน Y
                },
                hAxis: {
                    title: 'เดือน',
                    textStyle: { color: 'white' } // กำหนดสีข้อความของแกน X
                },
                titleTextStyle: {
                    color: 'white', // กำหนดสีหัวข้อของกราฟ
                    fontSize: 16,
                    bold: true
                },
            };
            

            var chart = new google.charts.Bar(document.getElementById('barchart_material'));
            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
</head>

<body>
    <div id="barchart_material" style="border: 2px solid #3E4080; box-shadow: 2px 4px 5px #3E4080; width: 40%; height: 100%;"></div>
</body>

</html>