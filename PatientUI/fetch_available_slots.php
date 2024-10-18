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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new  Exception("Invalid request method", 405);
    }
    if (!isset($_POST['date'], $_POST['doctorid'])) {
        throw new Exception("Missing required fields", 400);
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }

    $date = htmlspecialchars($con->real_escape_string($_POST['date']), ENT_QUOTES, 'UTF-8');
    $doctorId = htmlspecialchars($con->real_escape_string($_POST['doctorid']), ENT_QUOTES, 'UTF-8');

    $stmt = $con->prepare("SELECT * FROM schedule WHERE date = ? AND doctorid = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: ", 500);
    }

    $stmt->bind_param("ss", $date, $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();

    $slots = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $slot = array(
                "availabilityid" => htmlspecialchars($row['availabilityid'], ENT_QUOTES, 'UTF-8'),
                "date" => htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'),
                "starttime" => htmlspecialchars($row['starttime'], ENT_QUOTES, 'UTF-8'),
                "duration" => htmlspecialchars($row['duration'], ENT_QUOTES, 'UTF-8'),
                "doctorid" => htmlspecialchars($row['doctorid'], ENT_QUOTES, 'UTF-8')
            );
            $slots[] = $slot;
        }
    }

    $stmt->close();
    $con->close();
    $recordsJSON = json_encode($slots);
    if ($recordsJSON === false) {
        throw new Exception("JSON encoding error: " . json_last_error_msg(), 500);
    }

    header('Content-Type: application/json');
    echo $recordsJSON;

} catch (Exception $e) {
    if($logger)
        $logger->error("Error: " . $e->getMessage(), ['code' => $e->getCode()]);
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo json_encode(array('success' => false, 'error' => 'An error occurred while processing your request.'));
}