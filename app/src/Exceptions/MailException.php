<?php

/**
 * This classes contains all the exceptions related to the mail client
 */
class MailException extends Exception {

    public static function failedToSend() {
        return new static('The email cannot be delivered');
    }
}