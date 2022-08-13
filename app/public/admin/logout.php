<?php
// Unset all sessions variable
unset($_SESSION['logged']);
unset($_SESSION['userId']);
unset($_SESSION['username']);
unset($_SESSION['isAdmin']);
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
// Redirect to login page
header("HTTP/1.1 303 See Other");
header("Location: /admin/index.php");
die(0);
?>
