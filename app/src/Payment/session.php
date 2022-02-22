<?php

class Session {
    private $secretApiKey;

    /**
     * @param $secretApiKey
     */
    public function __construct($secretApiKey) {
        $this->secretApiKey = $secretApiKey;
    }

    /**
     * @param $sessionIds
     * @return void
     * @throws PaymentException
     */
    public function invalidateSessions($sessionIds) {
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        try {
            $stripe = new \Stripe\StripeClient(
                $this->secretApiKey
            );
        } catch (Exception $e) {
            throw PaymentException::failedToCreateStripeClient();
        }
        foreach ($sessionIds as $sessionId) {
            try {
                $stripe->checkout->sessions->expire(
                    $sessionId["SessionId"]
                );
            } catch (Exception $e) {
                throw PaymentException::failedToInvalidateSession();
            }
        }
    }

    /**
     * @param $sessionId
     * @return mixed
     * @throws PaymentException
     */
    public function getCustomerData($sessionId) {
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        try {
            $stripe = new \Stripe\StripeClient(
                $this->secretApiKey
            );
        } catch (Exception $e) {
            throw PaymentException::failedToCreateStripeClient();
        }
        try {
            $session = $stripe->checkout->sessions->retrieve(
                $sessionId
            );
        } catch (Exception $e){
            throw PaymentException::failedToRetrieveSession();
        }
        try {
            $customer = $stripe->customers->retrieve(
                $session->customer
            );
            return $customer;
        } catch (Exception $e){
            throw PaymentException::failedToRetrieveCustomerData();
        }
    }
}