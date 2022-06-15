<?php

/**
 * This class is an helper class used to manage debug functions
 */

class Debug {

    /**
     * @param Exception $e
     * @return void
     * Prints the exception on the page
     */
    public static function printException(Exception $e): void {
        print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
    }
}