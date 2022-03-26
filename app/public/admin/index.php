<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
session_start();
$credentialError = "noerr";
// check if data is set up
if (isset($_POST['username']) && isset($_POST['pwd'])) {
    try {
        // get db connection
        $db = Database::getDB();
        // make the query to check if the user exists
        $sql = "SELECT id, Username, Password, UserType FROM Dipendente WHERE username = ?;";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('s', $_POST['username'])) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            if ($stmt->store_result()) {
                // check the number of results
                if ($stmt->num_rows > 0) {
                    // user exists
                    if ($stmt->bind_result($userId, $username, $password, $userType) &&
                        $stmt->fetch()) {
                        if (password_verify($_POST['pwd'], $password)) {
                            // correct credentials
                            session_start();
                            $_SESSION['logged'] = 1;
                            $_SESSION['userId'] = $userId;
                            $_SESSION['username'] = $username;
                            if ($userType == ADMIN_USER) {
                                $_SESSION['isAdmin'] = 1;
                            } else {
                                $_SESSION['isAdmin'] = 0;
                            }
                            // redirect to dashboard
                            header("HTTP/1.1 303 See Other");
                            header("Location: /admin/dashboard.php");
                        } else {
                            // wrong credentials
                            $credentialError = "wrongPwdOrUser";
                        }
                    } else {
                        throw DatabaseException::fetchData();
                    }
                } else {
                    // no user is found
                    $credentialError = "userNotFound";
                }
            } else {
                throw DatabaseException::storeResult();
            }
            // close connection
            $stmt->close();
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    } catch (DatabaseException $e) {
        header("HTTP/1.1 303 See Other");
        header("Location: /error.php");
    }
} elseif (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/dashboard.php");
}
// otherwise, let display the login page
$displayError = false;
$errorMessage = "";
switch ($credentialError) {
    case 'wrongPwdOrUser':
    case 'userNotFound':
        $displayError = true;
        $errorMessage = "Non esiste alcun utente con le credenziali inserite";
        break;
    default:
        break;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/login.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container-fluid d-flex align-items-center justify-content-center main-container">
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <p class="card-text">Ciao, benvenuto su AgendaCloud, per favore effettua il login</p>
                </div>
            </div>
            <form id="form_login" action="/admin/index.php" method="post">
                <?php if ($displayError) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php print($errorMessage); ?>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-2">
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="fa-solid fa-user"
                                                              id="user-img"></i></span>
                            <input type="text" class="form-control" id="usernameLogin" name="username"
                                   placeholder="Inserisci il tuo username">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-2">
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="fa-solid fa-key" id="pwd-img"></i></span>
                            <input type="password" class="form-control" id="passwordLogin" name="pwd"
                                   placeholder="Inserisci la tua password">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" id="login-btn" class="btn btn-outline-success">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>