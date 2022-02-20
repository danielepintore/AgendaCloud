<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
// this file will make invalid the sessions using their session id
if (isset($_GET["sessionId"])){
    session::invalidateSession($_GET["sessionId"]);
}