<!DOCTYPE html>
<html>

<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>OT Plan Actual Report</title>

    <!-- Site favicon -->
    <!-- <link rel="icon" type="image/ico" href="../favicon.ico"> -->

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">


    <!-- CSS :  -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script src="../../asset/plugins/sweetalert2-11.10.1/jquery-3.7.1.min.js"></script>
    <script src="../../asset/plugins/sweetalert2-11.10.1/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../asset/plugins/sweetalert2-11.10.1/sweetalert2.all.min.js"></script>


    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
    <style>
        .header-container {
            /* background-color: #d9534f; */
            /* background: rgb(73, 179, 225);
            background: linear-gradient(90deg, rgba(73, 179, 225, 1) 0%, rgba(108, 211, 220, 1) 50%, rgba(73, 179, 225, 1) 100%); */

            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
        }
        .header-form {
            /* background-color: #d9534f; */
            display: flex;
            align-items: center;
            justify-content: end;
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
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        /* .upper-box {
        flex-grow: 1; 
        flex-basis: calc(25% - 10px); 
        margin: 5px;
        padding: 10px;
        box-sizing: border-box;
        } */

        .lower-box {
            flex-grow: 2;
            flex-basis: calc(100%);
            margin: 5px;
            padding: 10px;
            box-sizing: border-box;
        }

        @media (min-width: 768px) {

            .upper-box,
            .lower-box {
                flex-basis: calc(50% - 20px);
                margin: 10px;
            }
        }

        .upper-box,
        .lower-box {
            width: 100vw;
            /* 100% of the viewport width */
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
            border: 2px solid #757575; /* สีขอบของช่อง */
            padding: 8px; /* ระยะห่างของเนื้อหาจากขอบ */
        }
    </style>



    </style>
</head>