<?php

/**
 * This classes contains all the exceptions related to the database
 */
class DatabaseException extends Exception {

    public static function connectionFailed() {
        return new static('The connection to the database is failed');
    }

    public static function queryPrepareFailed() {
        return new static("The query isn't correct");
    }

    public static function bindingParamsFailed() {
        return new static("Check your binding params");
    }

    public static function queryExecutionFailed() {
        return new static("The query has failed to run");
    }

    public static function updateOrderStatus() {
        return new static("The status of the order can't be updated");
    }

    public static function storeResult() {
        return new static("The result of the query can't be stored");
    }

    public static function fetchData() {
        return new static("The data can't be fetched by the fetch method or the bind method isn't configured appropriately");
    }

    public static function cantDeleteCurrentUser() {
        return new static("Can't delete the current user because it's the same user that is logged");
    }

    public static function noResultAvailable() {
        return new static("The query has failed to run and no result object is available");
    }

    public static function noResultFetched() {
        return new static("The result object isn't fetched");
    }
}