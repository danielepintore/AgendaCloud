<?php

/**
 * This class contains the code to work with JSON file in php
 */
class JSONUtility {

    /**
     * @param string $json
     * @return bool
     * This function check if the JSON passed is a valid JSON, if it is returns true, otherwise false
     */
    public static function validateJSON(string $json): bool {
        try {
            json_decode($json, null, flags: JSON_THROW_ON_ERROR);
            return true;
        } catch (Exception $e) {
            return false;
        }

    }
}