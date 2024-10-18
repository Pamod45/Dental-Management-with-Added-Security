<?php
require '../vendor/autoload.php'; 
use Dotenv\Dotenv;
/**
 * Function to create.
 * Returns the connection object if successful, or false if the connection fails.
 *
 * @return mysqli|false
 */
function getDatabaseConnection()
{
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $server = $_ENV['DB_HOST'];
    $username = $_ENV['DB_USER_DOCTOR'];
    $password = $_ENV['DB_PASSWORD_DOCTOR'];
    $db = $_ENV['DB_NAME'];

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
