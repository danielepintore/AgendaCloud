<?php

/**
 * This class is a helper class used to manage debug functions
 */

class Debug {

    /**
     * @param Exception $e
     * @return void
     * Prints the exception on the page
     */
    public static function printException(Exception $e): void {
        print(self::getDebugMessage($e));
    }

    /**
     * @param Exception $e
     * @return string
     * Returns the debug message
     */
    public static function getDebugMessage(Exception $e): string {
        return ($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
    }
}