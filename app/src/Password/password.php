<?php

/**
 * This class manages the password security level
 */

class Password {
    /**
     * @param $password
     * @return bool
     * Check if the password is strong and if it's return true otherwise its return false
     */
    public static function isStrong($password): bool {
        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);
        /*
         * Check to increase strength with special characters
        if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            echo 'Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.';
        } else {
            echo 'Strong password.';
        }
        */
        if (!$uppercase | !$lowercase | !$number | strlen($password) < 8) {
            return false;
        } else {
            return true;
        }
    }
}