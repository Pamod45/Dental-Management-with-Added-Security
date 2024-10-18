<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include_once('patientAccessControl.php');
include ("../config/logger.php");
require("../config/patientDBConnection.php");
$logger = createLogger('patient.log');
header('Content-Type: application/json');

try{
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Only POST requests are allowed.", 403);
    }
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    if (isset($_POST['appointmentID']) && !empty($_POST['appointmentID'])) {
        $appointmentID = $con->real_escape_string($_POST['appointmentID']);
        
        $stmt = $con->prepare("DELETE FROM appointment WHERE appointmentid = ? AND patientid = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: " . $con->error, 500);
        }
        $stmt->bind_param("ss", $appointmentID,$_SESSION['patientid']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute delete query: " . $stmt->error, 500);
        }
        if ($stmt->affected_rows > 0) {
            $logger->info("Successfully deleted appointment ID: " . $appointmentID . " for patient ID: " .$_SESSION['patientid'] );
            http_response_code(200);
            echo json_encode(["success" => true]);
        } else {
            $logger->warning("No appointment found with ID: " . $appointmentID . " for patient ID: " . $_SESSION['patientid']);
            echo json_encode(["success" => false, "message" => "No appointment found with the provided ID."]);
        }
    } else {
        throw new Exception("No appointment found with the provided ID: " .$_POST['appointmentID'], 500);
    }
}catch(Exception $e){
    if($logger)
        $logger->error("Error in " . (__FILE__) . ": " . $e->getMessage(), [
            'code' => $e->getCode()
        ]);
    header('Content-Type: application/json');
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(["error" => $e->getMessage()]);
}





