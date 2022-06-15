<?php

/**
 * This classes contains all the exceptions related to the database
 */
class ClientException extends Exception {

    public static function invalidClientData() {
        return new static("The client that provided doesn't match our criteria");
    }
}