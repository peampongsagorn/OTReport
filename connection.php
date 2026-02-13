<?php
$serverName = "ip, port"; // แก้ตรงนี้
$connectionOptions = array(
    "Database" => "", 
    "Uid" => "", 
    "PWD" => "",
    "CharacterSet" => ""
);

// Establishes the connection เชื่อมต่อ SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    // echo "<script language='javascript'>alert('Connection Successfull.')
} else {
    echo "Connection could not be established.";
    die(print_r(sqlsrv_errors(), true));
}
