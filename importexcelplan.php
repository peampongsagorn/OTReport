<?php
require_once('./connection.php');
require_once('./vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['save_plan_data'])) { // Check if the form has been submitted
    $fileName = $_FILES['planFile']['name']; // Get the file name
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION); // Get the file extension

    $allowed_ext = ['xls', 'csv', 'xlsx']; // Allowed file extensions

    if (in_array($file_ext, $allowed_ext)) {
        $inputFileNamePath = $_FILES['planFile']['tmp_name']; // Get the temp file path
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath); // Load the spreadsheet
        $data = $spreadsheet->getActiveSheet()->toArray(); // Convert the active sheet to an array

        // Loop through the data (excluding the header row)
        foreach ($data as $index => $row) {
            if ($index === 0) continue; // Skip the header row

            // Map the row to corresponding variables
            $costcenter_id = $row[0];
            $month = $row[1];
            $year = $row[2];
            $fix1 = $row[3];
            $fix2 = $row[4];
            $fix3 = $row[5];
            $fix_all = $row[6]; // Assuming this is the sum of fix1, fix2, fix3
            $nonfix = $row[7];
            $total_hours = $row[8];
            $people_plan = $row[9];
            $working_day = $row[10];
            $dayoff = $row[11];

            // Construct the SQL Insert query
            $insertQuery = "INSERT INTO ot_plan (costcenter_id, month, year, fix1, fix2, fix3, nonfix, people, working_day, dayoff) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertParams = [$costcenter_id, $month, $year, $fix1, $fix2, $fix3, $nonfix, $people_plan, $working_day, $dayoff];
            $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams); // Execute the query

            // Check for errors in the insert query
            if (!$insertStmt) {
                die(print_r(sqlsrv_errors(), true)); // If there is an error, print it and exit
            }
        }

        echo "<script>alert('OT plan data uploaded successfully!')</script>";
    } else {
        echo "<script>alert('Invalid File Extension.')</script>";
    }
}
?>