<?php

/*
 * This class manages Stripe sessions
 */

class Session {
    private string $secretApiKey;

    /**
     * @param $secretApiKey
     */
    public function __construct($secretApiKey) {
        if (empty($secretApiKey)){
            throw PaymentException::failedToCreateStripeClient();
        }
        $this->secretApiKey = $secretApiKey;
    }

    /**
     * @param $sessionIds
     * @throws PaymentException
     * @return void
     * Invalidate an array of sessionIds
     */
    public function invalidateSessions($sessionIds): void {
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
                // TODO edit this but for now continue
                // ignore and continue
                //throw PaymentException::failedToInvalidateSession();
            }
        }
    }

    /**
     * @param $sessionId
     * @return mixed
     * @throws PaymentException
     * Retrieve customer data from a session id
     */
    public function getCustomerData($sessionId): mixed {
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
            return $stripe->customers->retrieve(
                $session->customer
            );
        } catch (Exception $e){
            throw PaymentException::failedToRetrieveCustomerData();
        }
    }

    /**
     * @param $sessionId
     * @return string
     * @throws PaymentException
     * Retrieve paymentIntent from a sessionId
     */
    public function getPaymentIntent($sessionId): mixed {
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
            return $session->payment_intent;
        } catch (Exception $e){
            throw PaymentException::failedToRetrievePaymentIntent();
        }
    }

    /**
     * @param $sessionId
     * @return bool
     * @throws PaymentException
     * Emit a refund for the payment
     */
    public function emitRefund($sessionId): mixed {
        require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
        $paymentIntent = $this->getPaymentIntent($sessionId);
        try {
            $stripe = new \Stripe\StripeClient(
                $this->secretApiKey
            );
        } catch (Exception $e) {
            throw PaymentException::failedToCreateStripeClient();
        }
        try {
            $stripe->refunds->create([
                'payment_intent' => $paymentIntent,
            ]);
            return true;
        } catch (Exception $e){
            throw PaymentException::failedToEmitRefund();
        }
    }
}