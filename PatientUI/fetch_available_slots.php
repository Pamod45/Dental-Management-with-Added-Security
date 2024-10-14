<?php
include_once 'auth.php';
require("../config/dbconnection.php");
// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Errors/error.php?code=403&message=No permission allowed");
    exit();
}

$date = $_POST['date'];
$doctorid = $_POST['doctorid'];


$query = "SELECT * FROM schedule WHERE date = '$date' AND doctorid = '$doctorid'";


$result = $con->query($query);


$slots = array();

if ($result->num_rows > 0) {
    // Fetch each row and add it to the records array
    while ($row = $result->fetch_assoc()) {
        $slot = array(
            "availabilityid" => $row['availabilityid'],
            "date" => $row['date'],
            "starttime" => $row['starttime'],
            "duration" => $row['duration'],
            "doctorid" => $row['doctorid']
        );
        $slots[] = $slot;
    }
}

// Close the database connection
$con->close();

// Encode the records array into JSON format
$recordsJSON = json_encode($slots);

// Set the appropriate headers to indicate JSON content
header('Content-Type: application/json');

// Output the JSON data
echo $recordsJSON;
