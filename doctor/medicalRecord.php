<?php
// header_remove("X-Powered-By");
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require("../config/doctorDBConnection.php");
require('../config/logger.php');

$loadMedicalRecord = false;
$logger = createLogger('doctor.log');

try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.',403);
    }
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        throw new Exception('Invalid request method');
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    if(!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
        http_response_code(403); 
        throw new Exception('CSRF token not found');
    }
    if(!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403); 
        throw new Exception('Invalid CSRF token');
    }
    $trustedOrigin = 'http://localhost:3000';
    $trustedReferrer = 'http://localhost:3000/doctor/patientMedicalRecords.php';

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    if ($origin !== $trustedOrigin || !str_starts_with($referrer, $trustedReferrer)) {
        http_response_code(403);
        throw new Exception('Invalid origin or referrer');
    }
    if(!isset($_POST['medicalrecordid'])){
        throw new Exception('Mandatory field is not found', 404);
    }
    $recordid=htmlspecialchars($_POST['medicalrecordid']);
    $getMRecquery = "SELECT medicalrecordid, patientid,
                    (SELECT CONCAT(firstname, ' ', lastname) 
                     FROM patient WHERE patientid = m.patientid) AS patientname,
                    TIMESTAMPDIFF(YEAR, (SELECT dob FROM patient WHERE patientid = m.patientid), CURDATE()) 
                     AS patientage,
                    (SELECT CONCAT(firstname, ' ', lastname) 
                     FROM doctor WHERE doctorid = m.doctorid) AS doctorname,
                    specialnotes, presentingcomplaints, treatments, date
                    FROM medicalrecord m
                    WHERE medicalrecordid = ?";

    $stmt = $con->prepare($getMRecquery);

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $con->error, 500);
    }
    $stmt->bind_param('s', $recordid);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error, 500);
    }
    $result = $stmt->get_result();
    $MedicalRecord = $result->fetch_assoc();

    if (!$MedicalRecord) {
        throw new Exception('Medical record not found', 404);
    }
    $treatmentsArray = array_map('htmlspecialchars', explode(",", $MedicalRecord['treatments']));
    $complaintsArray = array_map('htmlspecialchars', explode(",", $MedicalRecord['presentingcomplaints']));
    $specialNotesArray = array_map('htmlspecialchars', explode(",", $MedicalRecord['specialnotes']));
    $loadMedicalRecord = true;
    $stmt->close();
} catch (Exception $e) {
    if ($logger)
        $logger->error($e->getMessage());
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo '
    <h1>Something went wrong</h1>
    <p>' . htmlspecialchars($e->getMessage()) . '</p>
    ';
    if($e->getCode() === 403){
        echo '<a href="/user/login.php">Go to Login Page</a>';
    }    
    exit;
}

if($loadMedicalRecord):?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records</title>
    <link rel="icon" href="/images_new/favicon.png">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="pmedicalrecord.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
    <script src="/bootstrap-5.3.2/dist/js/bootstrap.bundle.js"></script>
    <link rel="stylesheet" href="/bootstrap-5.3.2/dist/css/bootstrap.min.css" type="text/css">
</head>

<body>
    <nav>
        <ul>
            <li>
                <a href="#">
                    <i class="fa-solid fa-bars sideBarIcon"></i>
                    <span class="nav-item">PSW Dental</span>
                </a>
            </li>
            <li>
                <a href="dashboard.php" class="nav-list-item">
                    <i class="fas fa-home sideBarIcon"></i>
                    <span class="nav-item">Home</span>
                </a>
            </li>
            <li>
                <a href="patientMedicalRecords.php" class="nav-list-item">
                    <i class="fa-solid fa-notes-medical sideBarIcon"></i>
                    <span class="nav-item">Patient Medical Record</span>
                </a>
            </li>
            <li>
                <a href="addMedicalRecord.php" class="nav-list-item">
                    <i class="fa-solid fa-folder-plus sideBarIcon"></i>
                    <span class="nav-item">Add New Record</span>
                </a>
            </li>
            <li>
                <a href="Pappointments.php" class="nav-list-item">
                    <i class="fa-regular fa-calendar-check sideBarIcon"></i>
                    <span class="nav-item">MY Appointments</span>
                </a>
            </li>
            <li>
                <a href="doctorSchedule.php" class="nav-list-item">
                    <i class="fa-solid fa-calendar-days sideBarIcon"></i>
                    <span class="nav-item">My Schedule</span>
                </a>
            </li>
            <li>
                <a href="#" class="logout nav-list-item">
                    <i class="fas fa-sign-out-alt sideBarIcon"></i>
                    <span class="nav-item">Log out</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="top">
        <p class="">Medcial Record</p>
        <a href="patientMedicalRecords.php">
            <button class="btn btn-primary addexpense" id="viewExpenses">View Records</button>
        </a>

    </div>
    <div class="bottom">
        <div class="container">
            <div class="item1">
                <div class="inneritem">
                    <label for="rid">Record ID</label>
                    <input type="text" class="form-control" id="rid" readonly>
                </div>
                <div class="inneritem">
                    <label for="pid">Patient ID</label>
                    <input type="text" class="form-control" id="pid" readonly>
                </div>
                <div class="inneritem">
                    <label for="pname">Patient Name</label>
                    <input type="text" class="form-control" id="pname" readonly>
                </div>
            </div>
            <div class="item2">
                <div class="inneritem">
                    <label for="page">Patient Age</label>
                    <input type="text" class="form-control" id="page" readonly>
                </div>
                <div class="inneritem">
                    <label for="dname">Doctor Name</label>
                    <input type="text" class="form-control" id="dname" readonly>
                </div>
                <div class="inneritem">
                    <label for="date">Date</label>
                    <input type="text" class="form-control" id="date" readonly>
                </div>
            </div>
            <div class="item3">
                <div class="inneritem">
                    <label for="complaints">Presenting Complaints</label>
                    <textarea rows="5" class="form-control" id="complaints" readonly></textarea>
                </div>
                <div class="inneritem">
                    <label for="treatements">Treatments</label>
                    <textarea rows="5" class="form-control" id="treatments" readonly></textarea>
                </div>
            </div>
            <div class="item4">
                <div class="inneritem">
                    <label>Prescription Image</label>
                    <img src="/images/prescription.jpg" alt="">
                </div>
            </div>
            <div class="item5">
                <div class="inneritem">
                    <label for="specialnotes">Special Notes</label>
                    <textarea rows="5" class="form-control" id="specialnotes" readonly></textarea>
                </div>
            </div>


        </div>
    </div>


</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script src='../assets/js/sanitize.js'></script>
<script>
    var recordDetails = {
        rid: "<?php echo htmlspecialchars($MedicalRecord['medicalrecordid']) ?>",
        pid: "<?php echo htmlspecialchars($MedicalRecord['patientid']) ?>",
        pname: "<?php echo htmlspecialchars($MedicalRecord['patientname']) ?>",
        date: "<?php echo htmlspecialchars($MedicalRecord['date']) ?>",
        dname: "Dr. <?php echo htmlspecialchars($MedicalRecord['doctorname']) ?>",
        page: "<?php echo htmlspecialchars($MedicalRecord['patientage']) ?>",
        complaints: <?php echo json_encode($complaintsArray) ?>,
        treatments: <?php echo json_encode($treatmentsArray) ?>,
        specialnotes: <?php echo json_encode($specialNotesArray) ?>
    }

    $('.logout').click(function() {
        // Send an AJAX request to logout
        $.ajax({
            type: 'POST', // or 'GET' depending on your server-side implementation
            url: '../user/logout.php', // URL to your logout endpoint
            success: function(response) {
                window.location.href = '../user/login.php';
            },
            error: function(xhr, status, error) {
                console.error('Error occurred while logging out:', error);
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        var rid = document.getElementById("rid");
        var pid = document.getElementById("pid");
        var pname = document.getElementById("pname");
        var page = document.getElementById("page");
        var date = document.getElementById("date");
        var dname = document.getElementById("dname");
        var complaints = document.getElementById("complaints");
        var treatments = document.getElementById("treatments");
        var specialnotes = document.getElementById("specialnotes");

        rid.value = recordDetails.rid;
        pid.value = recordDetails.pid;
        pname.value = recordDetails.pname;
        page.value = recordDetails.page;
        date.value = recordDetails.date;
        dname.value = recordDetails.dname;
        var allcomplaints = "";
        recordDetails.complaints.forEach(element => {
            allcomplaints += element + '\n';
        });
        complaints.value = allcomplaints;

        var alltreatments = "";
        recordDetails.treatments.forEach(element => {
            alltreatments += element + '\n';
        });
        treatments.value = alltreatments;

        var allspecialnotes = "";
        recordDetails.specialnotes.forEach(element => {
            allspecialnotes += element + '\n';
        });
        specialnotes.value = allspecialnotes;


    });
</script>

</html>

<?php endif; ?>