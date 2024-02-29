<?php
session_start();

require_once('./connection.php');

header('Content-Type: application/json');

// ตรวจสอบว่ามีการส่ง departmentId มาหรือไม่
if(isset($_GET['departmentId']) && !empty($_GET['departmentId'])) {
    $departmentId = $_GET['departmentId'];
    // กรองข้อมูล section ตาม department_id ที่รับมา
    $sql = "SELECT section_id, name FROM section WHERE department_id = ?";
    $params = array($departmentId);
} else {
    // ถ้าไม่มีการส่ง departmentId มา ก็เลือกข้อมูล section ทั้งหมด
    $sql = "SELECT section_id, name FROM section";
    $params = array();
}

$stmt = sqlsrv_query($conn, $sql, $params);

$sections = array();
if($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $sections[] = $row;
    }
    echo json_encode($sections);
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>