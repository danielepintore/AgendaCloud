<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new User();
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/AdminCalendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/admin/appointment.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script type="text/javascript" src="../js/admin/appointments.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, APPOINTMENT, true, 1); ?>
    <div class="container">
        <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="pendingAppointmentsList">
            <div class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="row">
                    <div class="col">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"> Francesco Diego Malica: Preventivo </h5>
                            <small> 02/12/21 17:30-18:00</small>
                        </div>
                        <div class="d-flex w-100 justify-content-between">
                            <small>Daniele Pintore</small>
                            <small>Contanti</small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a class="mini-buttons positive" value="' + element.appointmentId + '"><i
                                    class="fa-solid fa-circle-check"></i></a>
                        <a class="mini-buttons negative" value="' + element.appointmentId + '"><i
                                    class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="pendingAppointmentsList">
            <div class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="row">
                    <div class="col-10">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="mb-1">Francesco Diego Malica</h5>
                            </div>
                            <div class="col-4" style="text-align: end"><small>02/12/21 17:30-18:00</small></div>
                        </div>
                        <div class="row">
                            <div class="col-8">Daniele Pintore</div>
                            <div class="col-4" style="text-align: end"><small>Contanti</small></div>
                        </div>
                    </div>
                    <div class="col-2">
                            <a class="mini-buttons positive" value="' + element.appointmentId + '"><i
                                        class="fa-solid fa-circle-check"></i></a>
                            <a class="mini-buttons negative" value="' + element.appointmentId + '"><i
                                        class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
