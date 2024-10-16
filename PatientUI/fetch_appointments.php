<?php
include_once 'patientAccessControl.php';
authorizePatientAccess();

try {
    require('../config/logger.php');
    $logger = createLogger('Paitent_appointment.log');
    if(!$logger){
        throw new Exception('Failed to create logger instance.',500);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Only GET requests are allowed.", 403);
    }
    require("../config/dbconnection.php");

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
    $logger->error("Error in " . (__FILE__) . ": " . $e->getMessage(), [
        'code' => $e->getCode()
    ]);
    header('Content-Type: application/json');
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
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

