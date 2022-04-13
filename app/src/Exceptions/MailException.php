<?php
class MailException extends Exception {
    public static function failedToSend() {
        return new static('The email cannot be delivered');
    }
}