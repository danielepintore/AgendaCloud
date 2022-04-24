<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();
session_start();
$credentialError = "noerr";
// check if data is set up
if (isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['pwd']) && !empty($_POST['pwd']) &&
    isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
    try {
        //check if recaptcha is valid
        if (!Captcha::isSuccess($_POST['h-captcha-response'])) {
            $credentialError = "wrongCaptcha";
        } else {
            // get db connection
            $db = new Database();
            // make the query to check if the user exists
            $sql = "SELECT id, Username, Password, UserType FROM Dipendente WHERE (Username = ? AND isActive = TRUE)";
            $status = $db->query($sql, "s", $_POST['username']);
            if ($status) {
                // check the number of results
                $result = $db->getResult()[0];
                if ($db->getAffectedRows() > 0) {
                    // user exists
                    if (password_verify($_POST['pwd'], $result['Password'])) {
                        // correct credentials
                        session_start();
                        $_SESSION['logged'] = 1;
                        $_SESSION['userId'] = $result['id'];
                        $_SESSION['username'] = $result['Username'];
                        if ($result['UserType'] == ADMIN_USER) {
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
                    // no user is found
                    $credentialError = "userNotFound";
                }
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
    } catch (DatabaseException $e) {
        header("HTTP/1.1 303 See Other");
        header("Location: /error.php");
    }
} elseif (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['logged']) && $_SESSION['logged']) {
    $db = new Database();
    $user = new User($db);
    // check if user still exist in the database and is in active status
    try {
        if (!$user->exist() || !$user->isActive()) {
            header("HTTP/1.1 303 See Other");
            header("Location: /admin/logout.php");
        }
    } catch (DatabaseException|Exception $e) {
        if (DEBUG) {
            print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
        } else {
            header("HTTP/1.1 303 See Other");
            header("Location: /admin/logout.php");
        }
    }
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
    case 'wrongCaptcha':
        $displayError = true;
        $errorMessage = "Il captcha non è valido";
        break;
    default:
        break;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php print("Login - " . $config->company->name . " - AgendaCloud"); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/login.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/admin/index.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
<div class="container-fluid d-flex align-items-center justify-content-center main-container">
    <div class="card col col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-6">
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
                                   placeholder="Inserisci il tuo username" data-error="#errorUsername">
                        </div>
                    </div>
                    <span id="errorUsername" class="mb-2"></span>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-2">
                        <div class="input-group flex-nowrap">
                            <span class="input-group-text"><i class="fa-solid fa-key" id="pwd-img"></i></span>
                            <input type="password" class="form-control" id="passwordLogin" name="pwd"
                                   placeholder="Inserisci la tua password" data-error="#errorPwd">
                        </div>
                    </div>
                    <span id="errorPwd" class="mb-2"></span>
                </div>
                <div class="row">
                    <div class="col-12">
                        <input type="button" id="login-btn" class="btn btn-outline-success" value="Login"/>
                    </div>
                </div>
                <div class="h-captcha"
                     data-sitekey="<?php print($config->captcha->pub_key) ?>"
                     data-callback="submitForm"
                     data-size="invisible">
                </div>
            </form>
            <p class="captcha-terms mt-2 mb-0">
                Questo sito è protetto da hCaptcha e si applicano la sua
                <a href="https://hcaptcha.com/privacy">Privacy Policy</a> e i suoi
                <a href="https://hcaptcha.com/terms">Termini di servizio</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>