<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();
session_start();
if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['logged']) && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $db = new Database();
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()) {
        header("HTTP/1.1 303 See Other");
        header("Location: /admin/logout.php");
        die(0);
    }
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
    die(0);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php print("Dipendenti - " . $config->company->name . " - AgendaCloud"); ?></title>
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
    <link href='../css/dataTables.bootstrap5.min.css' rel='stylesheet'>
    <link href='../css/dataTablesStyle.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script src="../js/jquery.dataTables.min.js"></script>
    <script src="../js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/buttonLoader.js"></script>
    <script src="../js/admin/employees.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, EMPLOYEES); ?>
    <div class="container">
        <button type="submit" id="addEmployeeBtn" class="btn btn-success d-block me-0 ms-auto mt-2"><i
                    class="fa-solid fa-plus"></i> Aggiungi un dipendente
        </button>
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
                            <input type="text" placeholder="Username" class="form-control" id="username"
                                   name="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Password" class="form-control" id="password"
                                   name="password">
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
                    <button type="button" class="btn btn-success" id="confirmAddEmployeeBtn"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span>
                        Aggiungi
                    </button>
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
                            <input type="text" placeholder="Cognome" class="form-control" id="surname-edit"
                                   name="surname">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role-edit" name="role">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username-edit"
                                   name="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Nuova password" class="form-control" id="password-edit"
                                   name="password">
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
                    <button type="button" class="btn btn-success" id="confirmEditEmployeeBtn"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span>
                        Modifica
                    </button>
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
                    <button type="button" class="btn btn-danger" id="confirmDeleteEmployeeBtn" data-bs-dismiss="modal">
                        Elimina
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="workTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imposta gli orari di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body" style="">
                    <div id="employeeWorkTimesTab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#defaultWorkTimes"
                                        type="button" role="tab" aria-controls="Orari standard" aria-selected="true">
                                    Orari standard
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#customWorkTimes" type="button" role="tab"
                                        aria-controls="Orari speciali" aria-selected="false">Orari speciali
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="defaultWorkTimes" role="tabpanel"
                                 aria-labelledby="defaultWorkTimes">
                                <div id="defaultWorkTimesTable" class="mb-2"></div>
                                <button id="showModalEditWorkingTimeBtn" type="button" class="btn btn-success w-100"><i
                                            class="fa-solid fa-pen"></i> Modifica orari
                                </button>
                            </div>
                            <div class="tab-pane fade" id="customWorkTimes" role="tabpanel"
                                 aria-labelledby="customWorkTimes">
                                <div id="customWorkTimesTable" class="mb-2 mt-2"></div>
                                <button id="showModalCustomWorkingTimeBtn" type="button"
                                        class="btn btn-success w-100 mt-2"><i
                                            class="fa-solid fa-plus"></i> Aggiungi orari
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica gli orari di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <h6>Scegli un giorno:</h6>
                    <div class="day-container">
                        <div class="day-selector first-day" value="1">Lun</div>
                        <div class="day-selector" value="2">Mar</div>
                        <div class="day-selector" value="3">Mer</div>
                        <div class="day-selector" value="4">Gio</div>
                        <div class="day-selector" value="5">Ven</div>
                        <div class="day-selector" value="6">Sab</div>
                        <div class="day-selector last-day" value="7">Dom</div>
                    </div>
                    <form id="updateWorkTimeForm">
                        <h6>Orario inizio lavoro:</h6>
                        <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime"
                               name="startTime">
                        <h6>Orario fine lavoro:</h6>
                        <input type="time" value="17:00" class="form-control mb-2" id="workTime-endTime" name="endTime">
                        <h6>Orario inizio pausa:</h6>
                        <input type="time" value="13:00" class="form-control mb-2" id="workTime-startBreak"
                               name="startBreak">
                        <h6>Orario fine pausa:</h6>
                        <input type="time" value="15:00" class="form-control mb-2" id="workTime-endBreak"
                               name="endBreak">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="free-day-checkbox"
                                   name="freeDayCheckbox">
                            <label class="form-check-label">
                                Giorno libero
                            </label>
                        </div>
                    </form>
                    <div class="alert alert-danger d-flex align-items-center mt-2 mb-0 d-none" id="workTimeAlert"
                         role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div>
                            Devi selezionare dei giorni dalla barra qui sopra!
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-success" id="editWorkingTimeButton"><i
                                class="fa-solid fa-pen"></i><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Modifica
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un orario di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomWorkTimeForm">
                        <h6>Data inizio:</h6>
                        <input type="date" class="form-control mb-2" id="workTime-startCustomDay" name="startCustomDay">
                        <h6>Data fine:</h6>
                        <input type="date" class="form-control mb-2" id="workTime-endCustomDay" name="endCustomDay">
                        <h6>Orario inizio lavoro:</h6>
                        <input type="time" value="08:00" class="form-control mb-2" id="workTime-customStartTime"
                               name="customStartTime">
                        <h6>Orario fine lavoro:</h6>
                        <input type="time" value="17:00" class="form-control mb-2" id="workTime-customEndTime"
                               name="customEndTime">
                        <h6>Orario inizio pausa:</h6>
                        <input type="time" value="13:00" class="form-control mb-2" id="workTime-customStartBreak"
                               name="customStartBreak">
                        <h6>Orario fine pausa:</h6>
                        <input type="time" value="15:00" class="form-control mb-2" id="workTime-customEndBreak"
                               name="customEndBreak">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="free-day-custom-checkbox"
                                   name="freeDayCustomCheckbox">
                            <label class="form-check-label">
                                Giorno libero
                            </label>
                        </div>
                    </form>
                    <div class="alert alert-danger d-flex align-items-center mt-2 mb-0 d-none" id="customWorkTimeAlert"
                         role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div>
                            Devi selezionare un giorno che non sia già passato!
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="addCustomWorkingTimeButton"><i
                                class="fa-solid fa-plus"></i><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Aggiungi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="conflictWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="conflictWorkTimesModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="conflictWorkTimesModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-success" id="confirmOvverideEmployeeWorkTimesBtn">
                        <span class="ld ld-ring ld-cycle loading-circle d-none"></span> Si
                    </button>
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
