<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/Paitent_appointment.log');
include('patientAccessControl.php');
authorizePatientAccess();
require("../config/dbconnection.php");
require('../config/logger.php');
$logger = createLogger('patient_dashboard.log');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw  new Exception("Invalid request method", 405);
    }
    if (!isset($_POST['selectedDate']) || !isset($_POST['doctorId']) || !isset($_POST['startTime'])) {
        throw new Exception("Missing required fields", 400);
    }
    $date = htmlspecialchars($con->real_escape_string($_POST['selectedDate']), ENT_QUOTES, 'UTF-8');
    $doctorId = htmlspecialchars($con->real_escape_string($_POST['doctorId']), ENT_QUOTES, 'UTF-8');
    $startTime = htmlspecialchars($con->real_escape_string($_POST['startTime']), ENT_QUOTES, 'UTF-8');

    $stmt = $con->prepare("SELECT COUNT(*) AS AppointmentCount 
                           FROM appointment 
                           WHERE appointmentdate = ? 
                           AND doctorid = ? 
                           AND appointmentslot = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: " . $con->error, 500);
    }

    $stmt->bind_param("sss", $date, $doctorId, $startTime);
    $stmt->execute();
    $result = $stmt->get_result();

    $appointmentCount = 0;

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $appointmentCount = (int)$row['AppointmentCount'];
    }

    $stmt->close();
    $con->close();

    $response = array("AppointmentCount" => $appointmentCount);
    echo json_encode($response);
} catch (Exception $e) {
    $logger->error("Error: " . $e->getMessage(), ['code' => $e->getCode()]);
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(array('success' => false, 'error' => 'An error occurred while processing your request.'));
}
