<?php

class PaymentException extends Exception {
    public static function failedToCreateStripeClient() {
        return new static("Failed the initialization of the stripe client check the api key");
    }
    public static function failedToInvalidateSession() {
        return new static("Failed invalidate the session");
    }
    public static function failedToRetrieveSession() {
        return new static("Failed retrieve the session");
    }
    public static function failedToRetrieveCustomerData() {
        return new static("Failed get customer data");
    }
}