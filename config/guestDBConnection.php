<?php
if (!defined('SYSTEM_INIT')) {
    http_response_code(403);
    echo '  <h3>Error 403: Forbidden</h3>
            <p>You do not have permission to access this page.</p>';
    exit('Forbidden');
}
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
    $username = $_ENV['DB_USER_GUEST'];
    $password = $_ENV['DB_PASSWORD_GUEST'];
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
