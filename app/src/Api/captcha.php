<?php

class Captcha {

    /**
     * @param $responseCode
     * @return bool
     * Given a response code it check if the captcha verification step is passed
     */
    public static function isSuccess($responseCode) {
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        try {
            $config = Config::getConfig();
            $SECRET_KEY = $config->captcha->priv_key;
            $SITE_KEY = $config->captcha->pub_key;
            $reCaptchaResponse = file_get_contents('https://hcaptcha.com/siteverify?secret=' . $SECRET_KEY . '&response=' . $responseCode . '&remoteip=' . $_SERVER['REMOTE_ADDR'] . '&sitekey=' . $SITE_KEY);
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