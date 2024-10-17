<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include_once('patientAccessControl.php');
include ("../config/logger.php");
require("../config/patientDBConnection.php");
$logger = createLogger('Paitent_appointment.log');
header('Content-Type: application/json');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new  Exception("Invalid request method", 405);
    }
    if (!isset($_POST['doctorId'], $_POST['appointmentDate'], $_POST['appointmentSlot'], 
              $_POST['queueNo'], $_POST['appointmentId'], $_POST['paymentMethod'])) {
        throw new Exception("Missing required fields", 400);
    }
    
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    
    $doctorId = htmlspecialchars($con->real_escape_string($_POST['doctorId']), ENT_QUOTES, 'UTF-8');
    $appointmentDate = htmlspecialchars($con->real_escape_string($_POST['appointmentDate']), ENT_QUOTES, 'UTF-8');
    $appointmentSlot = htmlspecialchars($con->real_escape_string($_POST['appointmentSlot']), ENT_QUOTES, 'UTF-8');
    $queueNo = (int)$_POST['queueNo']; 
    $patientId = $_SESSION['patientid'];
    $appointmentId = htmlspecialchars($con->real_escape_string($_POST['appointmentId']), ENT_QUOTES, 'UTF-8');
    $paymentMethod = htmlspecialchars($con->real_escape_string($_POST['paymentMethod']), ENT_QUOTES, 'UTF-8');

    $stmt = $con->prepare("INSERT INTO `pdms`.`appointment`
        (`appointmentid`, `paymentmethodid`, `patientid`, `doctorid`, `status`,
        `appointmentcharges`, `createddate`, `appointmentdate`,
        `appointmentslot`, `queueno`) 
        VALUES (?, ?, ?, ?, 'In Progress', '3500', NOW(), ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $con->error, 500);
    }

    $stmt->bind_param("ssssssi", $appointmentId, $paymentMethod, $patientId, $doctorId, 
                      $appointmentDate, $appointmentSlot, $queueNo);

    if ($stmt->execute()) {
        $logger->info("Successfully created appointment ID: $appointmentId for patient ID: $patientId");
        echo json_encode(array('success' => true));
    } else {
        throw new Exception("Failed to create appointment: " . $stmt->error, 500);
    }
} catch (Exception $e) {
    if($logger)
        $logger->error("Error: " . $e->getMessage(), ['code' => $e->getCode()]);
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(array('success' => false, 'error' => 'An error occurred while processing your request.'));
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $con->close();
}
