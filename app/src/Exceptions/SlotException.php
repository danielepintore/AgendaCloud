<?php

/**
 * This classes contains all the exceptions related to the slots
 */
class SlotException extends Exception {

    public static function inesistentSlot() {
        return new static("The slot inserted is already booked");
    }
    public static function unableToGetSlots() {
        return new static("The slot inserted is already booked");
    }
}