<?php
require_once('./connection.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$currentYear = date('Y'); // ปีปัจจุบัน
$startYear = $currentYear . '-01-01'; // วันที่ 1 มกราคมของปีปัจจุบัน
$currentDate = date('Y-m-d'); // วันที่ปัจจุบัน
$filterData = $_SESSION['filter'] ?? null;
$displayData = '';

if (!empty($filterData['startMonthDate']) && !empty($filterData['endMonthDateCurrent'])) {
    $displayStartDate = $filterData['startMonthDate'];
    $displayEndDate = $filterData['endMonthDateCurrent'];
} else {
    $displayStartDate = $startYear;
    $displayEndDate = $currentDate;
}
function getEntityNameById($conn, $table, $id, $idField, $nameField)
{
    if (empty($id)) {
        return null;
    }

    $sql = "SELECT {$nameField} FROM {$table} WHERE {$idField} = ?";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        return $row[$nameField];
    }

    return null;
}

// ตรวจสอบข้อมูลใน $_SESSION['filter'] และ query ข้อมูล
if (isset($_SESSION['filter'])) {
    $filters = $_SESSION['filter'];
    $entities = [

        'divisionId' => ['table' => 'division', 'idField' => 'division_id', 'nameField' => 'name'],
        'departmentId' => ['table' => 'department', 'idField' => 'department_id', 'nameField' => 'name'],
        'sectionId' => ['table' => 'section', 'idField' => 'section_id', 'nameField' => 'name'],
    ];
}
$displayNames = [
    'divisionId' => 'Division',
    'departmentId' => 'Department',
    'sectionId' => 'Section',
];
?>




<div class="col-auto">
    <div class="text-white bg-dark p-2" style="color: white;background-color: #1C1D3A; text-align: center; border: 2px solid #3E4080;
         border-radius: 15px; box-shadow: 10px 10px 10px #3E4080; white-space: nowrap;">
        ข้อมูลที่ค้นหา:
        <span style="margin-right: 10px;">ช่วงวันที่: <?php echo "{$displayStartDate} ถึง {$displayEndDate}"; ?></span>
        <?php
        if (isset($_SESSION['filter'])) {
            $filters = $_SESSION['filter'];
            foreach ($entities as $key => $entity) {
                if (!empty($filters[$key])) {
                    $name = getEntityNameById($conn, $entity['table'], $filters[$key], $entity['idField'], $entity['nameField']);
                    if ($name) {
                        echo "<span style='margin-right: 10px;'><strong>{$displayNames[$key]}:</strong> {$name}</span>";
                    } else {
                        echo "<span style='margin-right: 10px;'><strong>{$displayNames[$key]}:</strong> ไม่พบข้อมูล</span>";
                    }
                }
            }
            // เพิ่มเงื่อนไขสำหรับแสดงประเภท OT
            if (!empty($filters['type'])) {
                echo "<span>ประเภท OT: {$filters['type']}</span>";
            }
        }
        ?>
    </div>
</div>
