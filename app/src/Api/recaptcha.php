<?php

class ReCaptcha {
    public static function isSuccess($responseCode) {
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        try {
            $config = Config::getConfig();
            $SECRET_KEY = $config->recaptcha->priv_key;
            $reCaptchaResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $SECRET_KEY . '&response=' . $responseCode . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
            $response = json_decode($reCaptchaResponse);
            if ($response->success) {
                // is a human
                return true;
            } else {
                return false;
            }
        } catch (Exception $e){
            if (DEBUG){
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
            }
            return false;

        }
    }
}