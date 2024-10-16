<?php
include_once 'patientAccessControl.php';
authorizePatientAccess();
try{
    require('../config/logger.php');
    $logger = createLogger('Paitent_appointment.log');
    if(!$logger){
        throw new Exception('Failed to create logger instance.',500);
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Only POST requests are allowed.", 403);
    }
    require("../config/dbconnection.php");
    if (isset($_POST['appointmentID']) && !empty($_POST['appointmentID'])) {
        $appointmentID = $con->real_escape_string($_POST['appointmentID']);
        $stmt = $con->prepare("DELETE FROM appointment WHERE appointmentid = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: " . $con->error, 500);
        }
        $stmt->bind_param("s", $appointmentID);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute delete query: " . $stmt->error, 500);
        }
        if ($stmt->affected_rows > 0) {
            $logger->info("Successfully deleted appointment ID: " . $appointmentID . " for patient ID: " . $patientid);
            echo json_encode(["success" => true]);
        } else {
            $logger->warning("No appointment found with ID: " . $appointmentID . " for patient ID: " . $patientid);
            echo json_encode(["success" => false, "message" => "No appointment found with the provided ID."]);
        }
    } else {
        throw new Exception("No appointment found with the provided ID: " .$_POST['appointmentID'], 500);
    }
}catch(Exception $e){
    $logger->error("Error in " . (__FILE__) . ": " . $e->getMessage(), [
        'code' => $e->getCode()
    ]);
    header('Content-Type: application/json');
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(["error" => $e->getMessage()]);
}
finally{
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($con) && $con instanceof mysqli) {
        $con->close();
    }  
    if (isset($logger)) {
        unset($logger);
    }
}





