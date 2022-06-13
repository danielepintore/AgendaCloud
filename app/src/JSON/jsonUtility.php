<?php

class JSONUtility {
    public static function validateJSON(string $json): bool {
        try {
            $test = json_decode($json, null, flags: JSON_THROW_ON_ERROR);
            return true;
        } catch (Exception $e) {
            return false;
        }

    }
}