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
    <title><?php print("Servizi - ".$config->company->name." - AgendaCloud");?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/admin/services.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="../js/admin/services.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, SERVICES); ?>
    <div class="container">
        <button type="submit" id="addServiceBtn" class="btn btn-success d-block me-0 ms-auto mt-2"><i class="fa-solid fa-plus"></i> Aggiungi un servizio</button>
        <div class="col-12 mt-2 mb-2">
            <div class="card w-auto">
                <div class="card-header">
                    Lista servizi:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="servicesList">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un nuovo servizio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="service-name">
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" placeholder="Durata" class="form-control" id="service-duration">
                            <span class="input-group-text">minuti</span>
                        </div>
                        <div class="mb-2">
                            <label for="service-startTime" class="form-label">Orario apertura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="08:00" class="form-control" id="service-startTime">
                            </div>
                            <label for="service-endTime" class="form-label">Orario chiusura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="12:00" class="form-control" id="service-endTime">
                            </div>
                            <div class="input-group mb-2">
                                <input type="number" value="15" placeholder="Costo" class="form-control" id="service-cost">
                                <span class="input-group-text">€</span>
                            </div>
                            <label for="service-waitTime" class="form-label">Tempo di attesa tra appuntamenti:</label>
                            <div class="input-group mb-2">
                                <input type="number" value="0" class="form-control" id="service-waitTime">
                                <span class="input-group-text">minuti</span>
                            </div>
                            <label for="service-bookableUntilTime" class="form-label">Non permettere di prenotare se mancano meno di </label>
                            <div class="input-group mb-2">
                                <input type="number" value="0" class="form-control" id="service-bookableUntilTime">
                                <span class="input-group-text">minuti</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="service-description" class="col-form-label">Descrizione servizio:</label>
                            <textarea class="form-control" id="service-description"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="service-active" checked>
                            <label class="form-check-label" for="service-active">
                                Servizio attivo istantaneamente
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmAddServiceBtn" data-bs-dismiss="modal">Aggiungi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica un servizio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="service-name-edit">
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" placeholder="Durata" class="form-control" id="service-duration-edit">
                            <span class="input-group-text">minuti</span>
                        </div>
                        <div class="mb-2">
                            <label for="service-startTime-edit" class="form-label">Orario apertura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="" class="form-control" id="service-startTime-edit">
                            </div>
                            <label for="service-endTime-edit" class="form-label">Orario chiusura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="" class="form-control" id="service-endTime-edit">
                            </div>
                            <div class="input-group mb-2">
                                <input type="number" value="" placeholder="Costo" class="form-control" id="service-cost-edit">
                                <span class="input-group-text">€</span>
                            </div>
                            <label for="service-waitTime-edit" class="form-label">Tempo di attesa tra appuntamenti:</label>
                            <div class="input-group mb-2">
                                <input type="number" value="" class="form-control" id="service-waitTime-edit">
                                <span class="input-group-text">minuti</span>
                            </div>
                            <label for="service-bookableUntilTime-edit" class="form-label">Non permettere di prenotare se mancano meno di </label>
                            <div class="input-group mb-2">
                                <input type="number" value="" class="form-control" id="service-bookableUntilTime-edit">
                                <span class="input-group-text">minuti</span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="service-description-edit" class="col-form-label">Descrizione servizio:</label>
                            <textarea class="form-control" id="service-description-edit"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="service-active-edit" checked>
                            <label class="form-check-label" for="service-active-edit">
                                Servizio attivo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="editServiceBtn" data-bs-dismiss="modal">Modifica</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showEmployeesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lista dipendenti:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="employeesInfo"></p>
                    <table class="table" id="employeesTable">
                        <thead>
                        <th scope="col">Nome</th>
                        <th scope="col">Cognome</th>
                        </thead>
                        <tbody id="employeesTableContent">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="addEmployeesBtn" data-bs-dismiss="modal"><i class="fa-solid fa-pen"></i> Modifica i dipendenti</button>
                    <button type="button" class="btn btn-secondary" id="confirmAddServiceBtn" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editEmployeesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi dei dipendenti:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="employeesToAddInfo"></p>
                    <div class="input-group mb-2">
                        <input type="text" placeholder="Nome" class="form-control" id="employeeNameSearch">
                    </div>
                    <table class="table d-none text-center" id="editEmployeesTable">
                        <thead>
                        <th scope="col">Nome</th>
                        <th scope="col">Cognome</th>
                        <th scope="col">Azione</th>
                        </thead>
                        <tbody id="editEmployeesTableContent">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="confirmAddEmployeeBtn" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vuoi eliminare questo servizio?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteServiceBtn" data-bs-dismiss="modal">Elimina</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
