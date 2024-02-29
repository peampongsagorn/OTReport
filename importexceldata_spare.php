<?php
require_once('./connection.php');
require_once('./vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

function parseDate($dateString, $includeTime = true)
{
    $format = $includeTime ? 'Y-m-d H:i:s' : 'Y-m-d';
    $date = DateTime::createFromFormat($format, $dateString);
    if ($date) {
        return $date->format($format);
    }
    return null; // Return null if the format does not match
}

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['save_excel_data'])) {
    // Check for OT Record file upload
    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == 0) {
        $inputFileNamePath = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        // Process the OT Record file
        processOTRecordFile($data, $conn);
    }

    // Check for OT Plan file upload
    if (isset($_FILES['planFile']) && $_FILES['planFile']['error'] == 0) {
        $inputFileNamePath = $_FILES['planFile']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        // Process the OT Plan file
        processOTPlanFile($data, $conn);
    }

    if (isset($_FILES['employeeFile']) && $_FILES['employeeFile']['error'] == 0) {
        $inputFileNamePath = $_FILES['employeeFile']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileNamePath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        // Process the Employee file
        processEmployeeFile($data, $conn);
    }
    // echo "<script>alert('Data uploaded successfully!')</script>";
    echo "<script>alert('Data uploaded successfully!'); window.location.href = 'dashboard.php';</script>";

    // Uncomment the line below to redirect after processing
    //echo "<meta http-equiv='refresh' content='1;URL=listemployee.php'/>";
}

function processOTRecordFile($data, $conn)
{
    foreach ($data as $index => $row) {
        // Skip the header row
        if ($index === 0 || $row[0] !== 'OT') {
            continue;
        }

        $employee_id = $row[1];

        // Check if employee_id exists in the employee table
        $query = "SELECT * FROM employee WHERE employee_id = ?";
        $params = [$employee_id];
        $stmt = sqlsrv_query($conn, $query, $params);

        // If the employee exists, insert the data into ot_record table
        if ($stmt && sqlsrv_has_rows($stmt)) {


            $date = parseDate($row[2], false); // Date only
            $time_start = parseDate($row[3]); // Date and time
            $time_end = parseDate($row[4]); // Date and time

            $attendance_hours = $row[5];
            $request_time = parseDate($row[6]); // Date and time
            $request_msg = $row[7];
            $request_detail = $row[8];
            $reviewer_id = $row[9];
            $review_time = parseDate($row[10]); // Date and time
            $approver_id = $row[11];
            $approve_time = parseDate($row[12]); // Date and time


            $insertQuery = "INSERT INTO ot_record (employee_id, date, time_start, time_end, attendance_hours, request_time, request_msg, request_detail, reviewer_id, reviewer_time, approver_id, approve_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $insertParams = [$employee_id, $date, $time_start, $time_end, $attendance_hours, $request_time, $request_msg, $request_detail, $reviewer_id, $review_time, $approver_id, $approve_time];
            $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

            // Check for errors in the insert query
            if (!$insertStmt) {
                die(print_r(sqlsrv_errors(), true));
            }
        } else {
            // Employee ID does not exist in employee table, skip this record
            continue;
        }
    }
    // ... any additional code needed for post-processing or error handling
}

function processOTPlanFile($data, $conn) {
    foreach ($data as $index => $row) {
        if ($index === 0) continue; // ข้ามแถวแรกซึ่งเป็นหัวตาราง

        $costcenter_code = $row[1]; // ได้รับรหัส costcenter จากไฟล์ Excel

        // ค้นหาในฐานข้อมูลเพื่อหา cost_center_id ที่ตรงกับรหัส
        $checkQuery = "SELECT cost_center_id FROM costcenter WHERE cost_center_code = ?";
        $checkStmt = sqlsrv_query($conn, $checkQuery, [$costcenter_code]);

        // ตรวจสอบว่ามีข้อมูลในฐานข้อมูลหรือไม่
        if ($checkStmt && $fetchedRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
            $costcenter_id = $fetchedRow['cost_center_id']; // ใช้ cost_center_id ที่ได้จากการ query

            // ตรวจสอบว่าข้อมูลในแถวนั้นครบถ้วนหรือไม่
            if (isset($row[4],$row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12], $row[13], $row[14], $row[15], $row[16], $row[17])) {
                // ดำเนินการบันทึกข้อมูล
                $month = $row[5];
                $year = $row[4];
                $fix1 = $row[6];
                $fix2 = $row[7];
                $fix3 = $row[8];
                $plan_fix_all = $row[9];
                $plan_fix_percent = $row[10];
                $nonfix = $row[11];
                $plan_nonfix_percent = $row[12];
                $plan_total_hours = $row[13];
                $plan_total_hours_percent = $row[14];
                $people_plan = $row[15];
                $working_day = $row[16];
                $dayoff = $row[17];
                $budget = $row[18];
                $salary = $row[19];

                // สร้างคำสั่ง SQL สำหรับการบันทึกข้อมูล
                $insertQuery = "INSERT INTO ot_plan (costcenter_id, month, year, fix1, fix2, fix3, nonfix, people, working_day, dayoff, sum_fix, total_hours, plan_fix_percent, plan_nonfix_percent, plan_total_hours_percent, budget, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertParams = [$costcenter_id, $month, $year, $fix1, $fix2, $fix3, $nonfix, $people_plan, $working_day, $dayoff, $plan_fix_all, $plan_total_hours, $plan_fix_percent, $plan_nonfix_percent, $plan_total_hours_percent, $budget, $salary];
                $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

                // ตรวจสอบข้อผิดพลาดในคำสั่ง insert
                if (!$insertStmt) {
                    echo "Error while inserting data: " . print_r(sqlsrv_errors(), true);
                }
            } else {
                echo "ข้อมูลในแถวที่ $index ไม่ครบถ้วน กำลังข้ามแถวนี้\n";
            }
        } else {
            continue;
        }
    }
}


function processEmployeeFile($data, $conn) {
    foreach ($data as $index => $row) {
        if ($index === 0) continue; // Skip header row

        // Assume columns are as follows:
        // A: Employee ID, B: Name, C: Department, D: Position, E: Email, H: Costcenter code
        $employee_id = $row[0];
        $name_title_t = $row[1];
        $firstname_t = $row[2];
        $lastname_t = $row[3];
        $name_title_e = $row[4];
        $firstname_e = $row[5];
        $lastname_e = $row[6];
        $costcenter_code = $row[7];
        $section = $row[8];
        $department = $row[9];
        $position = $row[10];
        $email = $row[11];
        $mobile = $row[12];
        $isshift = $row[13];
        $emplevel = $row[14];
        $companyno = $row[15];
        $boss = $row[16];
        $phonework = $row[17];
        $phonehome = $row[18];
        $hotline = $row[19];
        $houseno = $row[20];
        $plgroup = $row[21];
        $function = $row[22];
        $idcard = $row[23];
        $nickname = $row[24];
        $subsection = $row[25];
        $division = $row[26];

        if ($plgroup !== 'ป') {
            echo "PLGROUP is not 'ป'. Skipping row $index.\n";
            continue;
        }


        // Check if costcenter exists
        $costcenterQuery = "SELECT cost_center_id FROM costcenter WHERE cost_center_code = ?";
        $costcenterParams = [$costcenter_code];
        $costcenterStmt = sqlsrv_query($conn, $costcenterQuery, $costcenterParams);

        if ($costcenterStmt && $costcenterRow = sqlsrv_fetch_array($costcenterStmt, SQLSRV_FETCH_ASSOC)) {
            $costcenter_id = $costcenterRow['cost_center_id'];

            // Check if employee exists
            $employeeQuery = "SELECT employee_id FROM employee WHERE employee_id = ?";
            $employeeParams = [$employee_id];
            $employeeStmt = sqlsrv_query($conn, $employeeQuery, $employeeParams);

            if ($employeeStmt && sqlsrv_fetch_array($employeeStmt, SQLSRV_FETCH_ASSOC)) {
                // Update existing employee
                $updateQuery = "UPDATE employee SET NAMETITLE_T = ?,
                                    FIRSTNAME_T = ?, LASTNAME_T = ?, NAMETITLE_E = ?, FIRSTNAME_E = ?, LASTNAME_E = ?, SECTION = ? ,DEPARTMENT = ?, POSITION = ?, 
                                    EMAIL = ?, MOBILE = ?, ISSHIFT = ?, EMPLEVEL = ?, COMPANYNO = ?, BOSS = ?, PHONEWORK = ?, PHONEHOME = ?, HOTLINE = ?, 
                                    HOUSENO = ?, PLGROUP = ?, FUNCTION_EMP = ?, IDCARD = ?, NICKNAME_T =?, SUBSECTION = ?, DIVISION =?, CostcenterID = ? WHERE employee_id = ?";
                $updateParams = [$name_title_t, $firstname_t, $lastname_t, $name_title_e, $firstname_e, $lastname_e, $section, $department, $position, $email,
                                $mobile, $isshift, $emplevel, $companyno, $boss, $phonework, $phonehome, $hotline, $houseno, $plgroup, $function, $idcard,
                                $nickname, $subsection, $division, $costcenter_id ,$employee_id];
                $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);
                if (!$updateStmt) {
                    die("Error while updating data: " . print_r(sqlsrv_errors(), true));
                }
            } else {
                // Insert new employee
                $insertQuery = "INSERT INTO employee (employee_id, NAMETITLE_T, FIRSTNAME_T, LASTNAME_T, NAMETITLE_E, FIRSTNAME_E, LASTNAME_E, SECTION, DEPARTMENT,
                                            POSITION, EMAIL, MOBILE, ISSHIFT, EMPLEVEL, COMPANYNO, BOSS, PHONEWORK, PHONEHOME, HOTLINE, HOUSENO, 
                                            PLGROUP, FUNCTION_EMP, IDCARD, NICKNAME_T, SUBSECTION, DIVISION, CostcenterID) VALUES (?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertParams = [$employee_id, $name_title_t, $firstname_t, $lastname_t, $name_title_e, $firstname_e, $lastname_e, $section, $department, $position, $email,
                                    $mobile, $isshift, $emplevel, $companyno, $boss, $phonework, $phonehome, $hotline, $houseno, $plgroup, $function, $idcard,
                                    $nickname, $subsection, $division, $costcenter_id];
                $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);
                if (!$insertStmt) {
                    die("Error while inserting data: " . print_r(sqlsrv_errors(), true));
                }
            }
        } else {
            // costcenter_code does not exist, skip this row
            echo "Costcenter code: $costcenter_code not found. Skipping row $index.\n";
            continue;
        }
    }
}

