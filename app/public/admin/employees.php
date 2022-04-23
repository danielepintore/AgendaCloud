<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()){
        header("HTTP/1.1 303 See Other");
        header("Location: /admin/logout.php");
    }
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
    <link rel="stylesheet" type="text/css" href="../css/loading.min.css"/>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
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
                    <form id="addEmployeeForm">
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="name" name="name">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Cognome" class="form-control" id="surname" name="surname">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role" name="role">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username" name="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Password" class="form-control" id="password" name="password">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="admin">
                            <label class="form-check-label">
                                Amministratore
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="isActive" checked>
                            <label class="form-check-label">
                                Attivo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmAddEmployeeBtn"><span id="loadingCircleAddEmployee" class="ld ld-ring ld-cycle loading-circe d-none"></span> Aggiungi</button>
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
                    <form id="editEmployeeForm">
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="name-edit" name="name">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Cognome" class="form-control" id="surname-edit" name="surname">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role-edit" name="role">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username-edit" name="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Nuova password" class="form-control" id="password-edit" name="password">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="admin-edit">
                            <label class="form-check-label" for="service-active">
                                Amministratore
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="isActive-edit" checked>
                            <label class="form-check-label">
                                Attivo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmEditEmployeeBtn"><span id="loadingCircleEditEmployee" class="ld ld-ring ld-cycle loading-circe d-none"></span> Modifica</button>
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

    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="successModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="errorModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
