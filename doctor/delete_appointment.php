<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require("../config/doctorDBConnection.php");
require('../config/logger.php');

$logger = createLogger('doctor.log');
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception('Invalid request method', 405);
    }
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.', 401);
    }
    if (!$logger) {
        throw new Exception('Failed to create logger instance.', 500);
    }
    if (!isset($_SESSION['csrf_token']) || !isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        http_response_code(403);
        throw new Exception('CSRF token not found', 403);
    }
    if (!hash_equals($_SESSION['csrf_token'], $_SERVER['HTTP_X_CSRF_TOKEN'])) {
        throw new Exception('Invalid CSRF token');
    }
    $trustedOrigin = 'http://localhost:3000';
    $trustedReferrer = 'http://localhost:3000/doctor/Pappointments.php';

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    if (
        parse_url($origin, PHP_URL_HOST) !== parse_url($trustedOrigin, PHP_URL_HOST) ||
        parse_url($referrer, PHP_URL_HOST) !== parse_url($trustedReferrer, PHP_URL_HOST)
    ) {
        throw new Exception('Invalid referer/origin', 403);
    }

    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.', 500);
    }

    $appointmentID = htmlspecialchars(trim($_POST['appointmentID']), ENT_QUOTES, 'UTF-8');
    if (empty($appointmentID)) {
        throw new Exception('Mandotary fields are missing',400);
    }
    $stmt = $con->prepare("DELETE FROM appointment WHERE appointmentid = ?");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $con->error);
    }

    $stmt->bind_param("s", $appointmentID);

    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        http_response_code(201);
        echo json_encode(['success' => 'Appointment deleted successfully']);
    }else {
        throw new Exception('Database deletion error: ' . $stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    if ($logger)
        $logger->error($e->getMessage());
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(['error' => 'An error occurred. Please try again later.']);
}
