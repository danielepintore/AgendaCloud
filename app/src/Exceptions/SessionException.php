<?php

/**
 * This classes contains all the exceptions related to Stripe sessions
 */
class SessionException extends Exception {

    public static function moreAppointmentWithSameSessionId() {
        return new static("There are more appointment with the same session id");
    }
}