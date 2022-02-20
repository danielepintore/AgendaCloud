<?php
class session {
    public static function invalidateSession($sessionId){
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        require_once realpath(dirname(__FILE__, 3)) . '/config/config.php';
        $stripe = new \Stripe\StripeClient(
            $config['stripe']['secret_api_key']
        );
        $stripe->checkout->sessions->expire(
            $sessionId
        );
    }
}