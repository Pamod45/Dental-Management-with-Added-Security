<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/medical_records.log');
include_once '../patientUI/patientAccessControl.php';
authorizePatientAccess();
require("../config/dbconnection.php");
require("../config/logger.php");
$logger = createLogger('medical_records.log');

try {
    if (isset($_POST['medicalrecordid'])) {
        $medicalrecordid = filter_var($_POST['medicalrecordid'], FILTER_SANITIZE_STRING);
        $query = "SELECT m.*,
            CONCAT(p.firstname, ' ', p.lastname) AS patientname,
            CONCAT(d.firstname, ' ', d.lastname) AS doctorname,
            YEAR(CURDATE()) - YEAR(p.dob) - 
            (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(p.dob, '%m%d')) AS age 
            FROM medicalrecord m
            JOIN patient p ON m.patientid = p.patientid
            JOIN doctor d ON d.doctorid = m.doctorid
            WHERE medicalrecordid = ?";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            http_response_code(500);
            throw new Exception("Failed to prepare statement.");
        }
        $stmt->bind_param("s", $medicalrecordid);
        if (!$stmt->execute()) {
            http_response_code(500);
            throw new Exception("Query execution failed: ");
        }
        $result = $stmt->get_result();
        $records = array();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $record = array(
                    "patientname" => htmlspecialchars($row['patientname'], ENT_QUOTES, 'UTF-8'),
                    "doctorname" => htmlspecialchars($row['doctorname'], ENT_QUOTES, 'UTF-8'),
                    "specialnotes" => htmlspecialchars($row['specialnotes'], ENT_QUOTES, 'UTF-8'),
                    "presentingcomplaints" => htmlspecialchars($row['presentingcomplaints'], ENT_QUOTES, 'UTF-8'),
                    "date" => htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'),
                    "treatments" => htmlspecialchars($row['treatments'], ENT_QUOTES, 'UTF-8'),
                    "age" => htmlspecialchars($row['age'], ENT_QUOTES, 'UTF-8')
                );
                $records[] = $record;
            }
            http_response_code(200);
        }else{
            http_response_code(404);
            throw new Exception("No records found.");
        }
        $stmt->close();
        $recordsJSON = json_encode($records);
        header('Content-Type: application/json');
        echo $recordsJSON;
    } else {
        http_response_code(400); 
        throw new Exception("Missing medical record ID.");
    }
} catch (Exception $e) {
    $logger->error($e->getMessage());
    if(!http_response_code()){
        http_response_code(500); 
    }
    echo json_encode(array("error" => "An error occurred. Please try again later."));
} finally {
    $con->close();
}
?>
