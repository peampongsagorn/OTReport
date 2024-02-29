<?php
session_start();
require_once('./connection.php');

header('Content-Type: application/json');

    $sql = "SELECT division_id, name FROM division";

    $stmt = sqlsrv_query($conn, $sql);

$divisions = array();
if($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $divisions[] = $row;
    }
    echo json_encode($divisions);
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>