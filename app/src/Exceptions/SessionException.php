<?php

class SessionException extends Exception {
    public static function moreAppointmentWithSameSessionId() {
        return new static("There are more appointment with the same session id");
    }
}