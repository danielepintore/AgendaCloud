<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged'] && $_SESSION['isAdmin']) {
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
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <link href='../css/admin/employees.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin/employees.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, EMPLOYEES); ?>
    <div class="container">
        <div class="col-12 mt-2 mb-2">
            <div class="card w-auto">
                <div class="card-header">
                    Lista dipendenti:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="employeesList">
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="row">
                                <div class="col-8 mb-auto mt-auto">
                                    <h5 class="mb-1">Francesco Diego Malica</h5>
                                </div>
                                <div class="col-4 mb-auto mt-auto">
                                    <a class="mini-buttons neutral" value="' + element.appointmentId + '"><i
                                                class="fa-solid fa-trash"></i></a>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
