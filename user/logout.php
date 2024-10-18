<?php
session_start();
require('../config/logger.php');


try {
    $logger = createLogger('logout.log');
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION = array();
            session_unset();
            session_destroy();

            $logger->info('User logged out successfully.');
            echo json_encode(array("success" => true));
        } else {
            $logger->warning('Invalid CSRF token during logout attempt from IP: ' . $_SERVER['REMOTE_ADDR']);
            http_response_code(403); 
            echo json_encode(array("success" => false, "error" => "Invalid CSRF token."));
        }
    } else {
        http_response_code(405); 
        echo json_encode(array("success" => false, "error" => "Invalid request method."));
    }
} catch (Exception $e) {
    $logger->error('Error during logout: ' . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(array("success" => false, "error" => "An unexpected error occurred."));
}
?>

