<!DOCTYPE html>
<html>

<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <link rel="icon" type="image/svg+xml" href="./image/iconOT3.png" />
    <title>OT Plan Actual Report</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../asset/plugins/sweetalert2-11.10.1/sweetalert2.all.min.js"></script>

    <style>
        .header-container {
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
        }

        .footer-container {
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5px 0;
            font-size: 17px;
        }

        .footer-container p {
            white-space: normal;
            margin: 3px;
            /* แก้ไขตามที่ต้องการเพื่อเพิ่มระยะห่างระหว่างบรรทัด */
        }

        .header-form .filter {
            order: 2;
            justify-content: end;
            /* กำหนดให้แสดงผลเป็นอันดับที่ 2 */
        }

        .header-form .form-upload {
            order: 3;
            justify-content: end;
            /* กำหนดให้แสดงผลเป็นอันดับที่ 3 */
        }

        .header-form .search-data {
            order: 1;
            align-self: center;
            /* กำหนดให้แสดงผลเป็นอันดับแรก */
        }

        .header-form {
            /* background-color: #d9534f; */
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 10px 0;
        }

        .header-search {
            color: white;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 10px 0;
        }

        .header-container img {
            height: auto;
            width: auto;
            max-height: 100%;
        }

        .header-title {
            font-size: 2rem;
            font-weight: bold;
            margin-left: 10px;
            white-space: nowrap;
        }

        .card-body-month {
            min-height: 65px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .flex {
            display: flex;
        }

        .card-box {
            width: 100%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .pd-ltr-20 {
            justify-content: space-between;
            align-items: start;
            display: flex;
            margin-bottom: 10px;

        }



        .lower-box {
            flex-grow: 2;
            flex-basis: calc(100%);
            margin: 5px;
            padding: 10px;
            box-sizing: border-box;
        }

        .lower-box {
            width: 100vw;
        }

        @media (min-width: 768px) {

            .upper-box,
            .lower-box {
                flex-basis: calc(50% - 20px);
                margin: 10px;
            }
        }



        .custom-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 100%;
            max-width: 18rem;
        }

        .custom-card .card-header,
        .custom-card .card-body {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .custom-card .card-title {
            margin: 0;
            font-size: 1.25rem;
        }

        .custom-card .card-title span {
            font-size: 2.5rem;
        }

        .custom-card .col-md-4 {
            flex: 0 0 auto;
            width: 33.33333%;
        }

        .custom-card .mb-3 {
            margin-bottom: 1rem;
        }

        .card-body-month {
            min-height: 65px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .form-control,
        .form-select {
            height: 35px;
            margin-bottom: 0.5rem;
            padding: 5px 10px;
        }

        .card-header-month {
            padding: 5px 10px;
            font-size: 14px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .form-label {
            flex-basis: 20%;
            text-align: right;
            margin-right: 10px;
        }

        .form-control-container,
        .form-control-container-month {
            flex-basis: 75%;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .custom-select,
        .form-control {
            width: 100%;
        }

        .submit-button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .bi-filter {
            cursor: pointer;
        }



        .data-table2 td {
            border: 2px solid #757575;
            /* สีขอบของช่อง */
            padding: 8px;
            /* ระยะห่างของเนื้อหาจากขอบ */
        }
    </style>




</head>