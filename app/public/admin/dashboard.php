<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new Admin($_SESSION['logged'], $_SESSION['username'], $_SESSION['password']);
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
}
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-md navbar-light bg-white mb-2">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">AgendaCloud</a>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#rightMenu" aria-controls="rightMenu" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-0">
                    <li class="nav-item me-2">
                        <a class="nav-link active" aria-current="page" href="#">Calendario</a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#">Servizi</a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#">Dipendenti</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="collapse navbar-collapse" id="rightMenu">
        <ul class="mobile-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Calendario</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="#">Servizi</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="#">Dipendenti</a>
            </li>
        </ul>
    </div>
    <div class="card w-100">
        <div class="card-header">
            Orari disponibili:
        </div>
        <div class="card-body">
            <h5 class="card-title">Scegli un orario da qua sotto:</h5>
            <select class="form-select" aria-label="Default select example">
            </select>
        </div>
    </div>
</div>
</body>
</html>
