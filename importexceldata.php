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


}



function processOTRecordFile($data, $conn)
{
    // วนลูปผ่านข้อมูลที่ได้รับจากไฟล์ Excel
    foreach ($data as $index => $row) {

        if ($index === 0 || $row[0] !== 'OT') {
            continue;
        }

        $employee_id = $row[1];

        $query = "SELECT * FROM employee WHERE employee_id = ?";
        $params = [$employee_id];
        $stmt = sqlsrv_query($conn, $query, $params);


        if ($stmt && sqlsrv_has_rows($stmt)) {
            // Insert data into ot_record_upload table
            $insertUploadQuery = "INSERT INTO ot_record_upload (employee_id, date, time_start, time_end, attendance_hours, request_time, request_msg, request_detail, reviewer_id, reviewer_time, approver_id, approve_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $uploadParams = [
                $employee_id, // employee_id from the row
                parseDate($row[2], false), // date
                parseDate($row[3]), // time_start
                parseDate($row[4]), // time_end
                $row[5], // attendance_hours
                parseDate($row[6]), // request_time
                $row[7], // request_msg
                $row[8], // request_detail
                $row[9], // reviewer_id
                parseDate($row[10]), // reviewer_time
                $row[11], // approver_id
                parseDate($row[12]) // approve_time
            ];
            $insertUploadStmt = sqlsrv_query($conn, $insertUploadQuery, $uploadParams);

            if (!$insertUploadStmt) {
                die(print_r(sqlsrv_errors(), true));
            }
        } else {
            continue;
        }
    }

    // ย้ายข้อมูลจาก ot_record_upload ไปยัง ot_record ตามเงื่อนไข PLGROUP
    $moveQuery = "INSERT INTO ot_record (employee_id, date, time_start, time_end, attendance_hours, request_time, request_msg, request_detail, reviewer_id, reviewer_time, approver_id, approve_time)
                  SELECT ur.employee_id, ur.date, ur.time_start, ur.time_end, ur.attendance_hours, ur.request_time, ur.request_msg, ur.request_detail, ur.reviewer_id, ur.reviewer_time, ur.approver_id, ur.approve_time
                  FROM ot_record_upload ur
                  INNER JOIN employee e ON ur.employee_id = e.employee_id
                  WHERE e.PLGROUP = 'ป'";
    $moveStmt = sqlsrv_query($conn, $moveQuery);

    if (!$moveStmt) {
        die(print_r(sqlsrv_errors(), true));
    }

    $deleteQuery = "DELETE FROM ot_record_upload";
    $deleteStmt = sqlsrv_query($conn, $deleteQuery);

    // // ตรวจสอบข้อผิดพลาดในการลบข้อมูล
    if (!$deleteStmt) {
         die(print_r(sqlsrv_errors(), true));
     }
}



function processOTPlanFile($data, $conn) {
    foreach ($data as $index => $row) {
        if ($index === 0) continue;

        $costcenter_code = $row[1]; 

        $checkQuery = "SELECT cost_center_id FROM costcenter WHERE cost_center_code = ?";
        $checkStmt = sqlsrv_query($conn, $checkQuery, [$costcenter_code]);

        if ($checkStmt && $fetchedRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
            $costcenter_id = $fetchedRow['cost_center_id']; 

            if (isset($row[4],$row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12], $row[13], $row[14], $row[15], $row[16], $row[17])) {
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


                $insertQuery = "INSERT INTO ot_plan (costcenter_id, month, year, fix1, fix2, fix3, nonfix, people, working_day, dayoff, sum_fix, total_hours, plan_fix_percent, plan_nonfix_percent, plan_total_hours_percent, budget, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insertParams = [$costcenter_id, $month, $year, $fix1, $fix2, $fix3, $nonfix, $people_plan, $working_day, $dayoff, $plan_fix_all, $plan_total_hours, $plan_fix_percent, $plan_nonfix_percent, $plan_total_hours_percent, $budget, $salary];
                $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

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
        if ($index === 0) continue; 


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
            
            continue;
        }
    }
}

