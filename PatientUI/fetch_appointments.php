<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('patientAccessControl.php');
include('authorizePatientAccess.php');
require("../config/patientDBConnection.php");
require('../config/logger.php');

$logger = createLogger('patient.log');
try {
    if(!$logger){
        throw new Exception('Failed to create logger instance.',500);
    }
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Only GET requests are allowed.", 403);
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    
    $patientId = $_SESSION['patientid'];

    $stmt = $con->prepare("
        SELECT a.*, d.lastname, pm.name as paymentname
        FROM appointment AS a
        JOIN doctor AS d ON a.doctorid = d.doctorid
        JOIN paymentmethod AS pm ON a.paymentmethodid = pm.paymentmethodid
        WHERE a.patientid = ?
        ORDER BY a.appointmentdate DESC
    ");
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: ", 500);
    }
    $stmt->bind_param("s", $patientId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: ", 500);
    }
    $result = $stmt->get_result();

    $appointments = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $appointment = array(
                "appointmentID" => htmlspecialchars($row['appointmentid']),
                "date" => htmlspecialchars($row['appointmentdate']),
                "time" => htmlspecialchars($row['appointmentslot']),
                "queueNo" => htmlspecialchars($row['queueno']),
                "doctorName" => htmlspecialchars($row['lastname']),
                "status" => htmlspecialchars($row['status']),
                "charge" => htmlspecialchars($row['appointmentcharges']),
                "paymentMethod" => htmlspecialchars($row['paymentname'])
            );
            $appointments[] = $appointment;
        }
        $logger->info("Successfully fetched " . count($appointments) . " appointments for patient ID: " . $patientId);
    }else{
        $logger->warning("No appointments found for patient ID: " . $patientId);
    }
    $appointmentsJSON = json_encode($appointments);
    header('Content-Type: application/json');
    echo $appointmentsJSON;
} catch (Exception $e) {
    if($logger)
        $logger->error("Error in " . (__FILE__) . ": " . $e->getMessage(), [
            'code' => $e->getCode()
        ]);
    header('Content-Type: application/json');
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(["error" => $e->getMessage()]);
} 

