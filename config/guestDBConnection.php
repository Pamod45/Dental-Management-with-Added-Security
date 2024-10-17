<?php

/**
 * Function to create.
 * Returns the connection object if successful, or false if the connection fails.
 *
 * @return mysqli|false
 */
function getDatabaseConnection()
{
    $server = "localhost";
    $username = "guest_user";
    $password = "jdkkdjkfj5454SIER,";
    $db = "pdms";

    try {
        $con = new mysqli($server, $username, $password, $db);
        if ($con->connect_error) {
            return false;
        }
        return $con;
    } catch (Exception $e) {
        return false;
    }
}
