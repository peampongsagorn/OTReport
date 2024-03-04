<?php include('./connection.php');
session_start();
?>
<?php include('./include/head.php') ?>

<body>
    <?php include('./include/navbar.php') ?>

    <div class="main-container">
        <div class="pd-ltr-20">

            <div class="card-box lower-box pd-20 mb-30" style="background-color: #eeeeee;  min-height: 1100px; border-radius: 15px; box-shadow: 10px 10px 8px #3E4080">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="container-fluid" style="background-color: white; padding: 15px; margin-bottom: 20px; border: 2px solid #3E4080; border-radius: 15px; box-shadow: 5px 5px 5px #3E4080;">
                            <div class="row" style="justify-content: center;">
                                <div class="col-md-3 mb-2 d-flex">
                                    <?php include('./Chart/chart_cal_percent.php') ?>
                                    <div class="card custom-card text-white" style="background-color: #41446B; flex-grow: 1; border-radius: 15px; box-shadow: 2px 4px 5px #3E4080">
                                        <div class="card-header text-center" style="background-color: #313456; border-top-right-radius: 15px; border-top-left-radius: 15px;">
                                            Plan % Normal Time
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="height: 70px;">
                                            <h5 class="card-title">
                                                <?php
                                                echo "<span style='font-size: 40px; color: white;'>" . number_format($percentage_plan, 2) . "%</span><br>";
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2 d-flex">
                                    <div class="card custom-card text-white" style="background-color: #41446B; flex-grow: 1; border-radius: 15px; box-shadow: 2px 4px 5px #3E4080">
                                        <div class="card-header text-center" style="background-color: #313456; border-top-right-radius: 15px; border-top-left-radius: 15px;">
                                            Actual % Normal Time
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="height: 70px;">
                                            <h5 class="card-title">
                                                <?php
                                                echo "<span style='font-size: 40px; color: white;'>" . number_format($percentage_actual, 2) . "%</span><br>";
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2 d-flex">
                                    <div class="card custom-card text-white" style="background-color: #41446B ; flex-grow: 1; border-radius: 15px; box-shadow: 2px 4px 5px <?php echo $colorClass ?>">
                                        <div class="card-header text-center" style="background-color: #313456; border-top-right-radius: 15px; border-top-left-radius: 15px;">
                                            Percentage
                                            Difference
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="height: 70px;">
                                            <h5 class="card-title">
                                                <?php
                                                echo "<span style='font-size: 40px; color: white;'>" . number_format($diff_percentage_plan_actual, 2) . "%</span><br>";
                                                ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <?php include('./Chart/chart_plan_actual.php') ?>
                    </div>
                    <div class="col-md-4">
                        <?php include('./Chart/chart_ot_type.php') ?>
                    </div>
                </div>

                <div class="row justify-content-center" style="margin-bottom:5px">
                    <div class="col-md-8">
                        <?php include('./Chart/chart_ot_per_person_per_day.php') ?>
                    </div>
                    <div class="col-md-4">
                        <?php include('./Chart/chart_36hours.php') ?>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <?php include('./Chart/chart_top10.php') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>



</html>