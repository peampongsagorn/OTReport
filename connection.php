<?php
$serverName = "52.139.193.40, 3511"; // แก้ตรงนี้
$connectionOptions = array(
    "Database" => "OTReport", 
    "Uid" => "follow", 
    "PWD" => "Follow@2022",
    "CharacterSet" => "UTF-8"
);

// Establishes the connection เชื่อมต่อ SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    // echo "<script language='javascript'>alert('Connection Successfull.')
} else {
    echo "Connection could not be established.";
    die(print_r(sqlsrv_errors(), true));
}
