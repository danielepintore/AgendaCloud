<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();
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
    <title><?php print("Dipendenti - ".$config->company->name." - AgendaCloud");?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
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
        <button type="submit" id="addEmployeeBtn" class="btn btn-success d-block me-0 ms-auto mt-2"><i class="fa-solid fa-plus"></i> Aggiungi un dipendente</button>
        <div class="col-12 mt-2 mb-2">
            <div class="card w-auto">
                <div class="card-header">
                    Lista dipendenti:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="employeeList">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un nuovo dipendente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="name">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Cognome" class="form-control" id="surname">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Password" class="form-control" id="password">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="admin">
                            <label class="form-check-label" for="service-active">
                                Amministratore
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmAddEmployeeBtn" data-bs-dismiss="modal">Aggiungi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica un dipendente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="name-edit">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Cognome" class="form-control" id="surname-edit">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role-edit">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username-edit">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Password" class="form-control" id="password-edit">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="admin-edit">
                            <label class="form-check-label" for="service-active">
                                Amministratore
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmEditEmployeeBtn" data-bs-dismiss="modal">Modifica</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vuoi eliminare questo dipendente?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    Il dipendente selezionato sarà eliminato in modo irreversibile.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteEmployeeBtn" data-bs-dismiss="modal">Elimina</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
