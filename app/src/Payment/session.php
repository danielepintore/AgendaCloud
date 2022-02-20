<?php
class Session {
    private $secretApiKey;

    /**
     * @param $secretApiKey
     */
    public function __construct($secretApiKey) {
        $this->secretApiKey = $secretApiKey;
    }

    public function invalidateSession($sessionId){
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        $stripe = new \Stripe\StripeClient(
            $this->secretApiKey
        ); // todo add a try catch block here
        try {
            $stripe->checkout->sessions->expire(
                $sessionId
            );
        } catch (Exception $e) {
            //TODO log stuff here
        }
    }

    public function invalidateSessions($sessionIds){
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        $stripe = new \Stripe\StripeClient(
            $this->secretApiKey
        );// todo add a try catch block here
            foreach ($sessionIds as $sessionId){
                try {
                    $stripe->checkout->sessions->expire(
                        $sessionId["SessionId"]
                    );
                } catch (Exception $e){
                    //TODO log stuff here
                }
            }
    }

    public function getCustomerData($sessionId){
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        $stripe = new \Stripe\StripeClient(
            $this->secretApiKey
        );// todo add a try catch block here

        $session = $stripe->checkout->sessions->retrieve(
            $sessionId
        );
        $customer = $stripe->customers->retrieve(
            $session->customer
        );
        return $customer;
    }
}