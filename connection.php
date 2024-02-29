<?php
$serverName = "LAPTOP-BD48TPSN\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "OTReport",
    "Uid" => "",
    "PWD" => "",
    "CharacterSet" => "UTF-8",
 
);


$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    //echo "<script language='javascript'>alert('Connection Successful.');</script>";
} else {
    echo "Connection could not be established.";
    die(print_r(sqlsrv_errors(), true));
}
?>