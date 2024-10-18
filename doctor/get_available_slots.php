<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require('../config/logger.php');
require("../config/doctorDBConnection.php");

$logger = createLogger('doctor.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizeDoctorAccess2();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        throw new Exception('Invalid request method');
    }
    if(!isset($_SESSION['csrf_token']) || !isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        http_response_code(403); 
        throw new Exception('CSRF token not found');
    }
    if(!hash_equals($_SESSION['csrf_token'], $_SERVER['HTTP_X_CSRF_TOKEN'])) {
        http_response_code(403); 
        throw new Exception('Invalid CSRF token');
    }
    $trustedOrigin = 'http://localhost:3000';
    $trustedReferrer = 'http://localhost:3000/doctor/doctorSchedule.php';

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    if ($origin !== $trustedOrigin || !str_starts_with($referrer, $trustedReferrer)) {
        http_response_code(403);
        throw new Exception('Invalid origin or referrer');
    }

    // Validate inputs
    $doctorid = trim($_POST['doctorid']);
    $doctorid = htmlspecialchars($doctorid, ENT_QUOTES, 'UTF-8');
    $date = trim($_POST['date']);
    $date = htmlspecialchars($date, ENT_QUOTES, 'UTF-8');

    // Check if inputs are valid (e.g., doctorid and date format)
    if (empty($doctorid) || empty($date)) {
        http_response_code(400); // Bad Request
        throw new Exception('Invalid input');
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $con->prepare("SELECT starttime FROM pdms.schedule WHERE doctorid = ? AND date = ?");
    $stmt->bind_param("ss", $doctorid, $date); // Adjust the types as necessary

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    $availableSlots = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $availableSlots[] = htmlspecialchars($row['starttime']);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($availableSlots);
} catch (Exception $e) {
    if ($logger)
        $logger->error($e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    http_response_code($e->getCode() ? $e->getCode() : 500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>