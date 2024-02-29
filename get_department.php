<?php
session_start();
require_once('./connection.php');

header('Content-Type: application/json');

// ตรวจสอบว่ามีการส่ง division_id มาหรือไม่
if(isset($_GET['divisionId']) && !empty($_GET['divisionId'])) {
    $divisionId = $_GET['divisionId'];
    // กรองข้อมูล department ตาม division_id ที่รับมา
    $sql = "SELECT department_id, name FROM Department WHERE division_id = ?";
    $params = array($divisionId);
} else {
    // ถ้าไม่มี division_id มา ก็เลือกข้อมูล department ทั้งหมด
    $sql = "SELECT department_id, name FROM Department";
    $params = array();
}

$stmt = sqlsrv_query($conn, $sql, $params);

$departments = array();
if($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $departments[] = $row;
    }
    echo json_encode($departments);
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>