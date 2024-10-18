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

$loadMedicalRecordUI = false;
$logger = createLogger('patient.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    $patientID = $_SESSION['patientid'];
    $patientID = htmlspecialchars($con->real_escape_string($patientID), ENT_QUOTES, 'UTF-8');
    $stmt = $con->prepare(
        "SELECT mr.medicalrecordid, mr.date, mr.time, mr.presentingcomplaints, 
                mr.treatments, mr.specialnotes, 
                CONCAT(d.firstname, ' ', d.lastname) AS doctorname
         FROM medicalrecord mr
         JOIN doctor d ON mr.doctorid = d.doctorid
         WHERE mr.patientid = ?
         ORDER BY mr.date DESC"
    );
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: ", 500);
    }
    $stmt->bind_param("s", $patientID);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: ", 500);
    }
    $result = $stmt->get_result();
    $loadMedicalRecordUI = true;
} catch (Exception $e) {
    if($logger)
        $logger->error("Error in " . (__FILE__) . ": " . $e->getMessage(), [
            'code' => $e->getCode()
        ]);
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo '    
    <h1>Something went wrong</h1>
    <p>' . htmlspecialchars($e->getMessage()) . '</p>
    ';
}
if ($loadMedicalRecordUI): ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Appointments</title>
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="MedicalRecords.css">
        <link rel="icon" href="/images_new/favicon.png">
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
                    <a href="Pappointments.php" class="nav-list-item">
                        <i class="fa-regular fa-calendar-check sideBarIcon"></i>
                        <span class="nav-item">My Appointments</span>
                    </a>
                </li>
                <li>
                    <a href="PNewAppointment.php" class="nav-list-item">
                        <i class="fa-regular fa-calendar-plus sideBarIcon"></i>
                        <span class="nav-item">New Appointment</span>
                    </a>
                </li>
                <li>
                    <a href="MedicalRecords.php" class="nav-list-item">
                        <i class="fa-solid fa-notes-medical sideBarIcon"></i>
                        <span class="nav-item">Medical Records</span>
                    </a>
                </li>
                <li>
                    <a href="PUpdateProfile.php" class="nav-list-item">
                        <i class="fa-solid fa-user sideBarIcon"></i>
                        <span class="nav-item">Update Profile</span>
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
            <p>Medical Records</p>
        </div>
        <div class="bottom">
            <select id="searchCriteria" class="form-select">
                <option value="date">Date</option>
                <option value="doctorName">Doctor Name</option>
            </select>
            <div class="search">
                <input type="text" class="form-control" id="searchDate" placeholder="eg :YYYY/MM/DD">
            </div>
            <div class="mycontainer">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='medicalRecord'>";
                        echo "<p class='dateTime'>Date: {$row['date']} Time: {$row['time']}</p>";
                        echo "<p class='mrid'>Record ID: {$row['medicalrecordid']}</p>";
                        echo "<p class='doctorName'>Consulted Doctor: Dr. {$row['doctorname']}</p>";
                        echo "<p>Presenting Complaints: {$row['presentingcomplaints']}</p>";
                        echo "<p>Treatments: {$row['treatments']}</p>";
                        echo "<p>Special Notes: {$row['specialnotes']}</p>";
                        echo "<button class='btn btn-primary btn-sm viewPrescription'>View Prescription</button>";
                        echo "<button class='btn btn-primary btn-sm downloadPDF'>Download As PDF</button>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>

        <div class="overlay" id="overlay">
            <div class="modal-content">
                <span class="close-btn" id="closeBtn">&times;</span>
                <img src="/images/prescription.jpg" height="250px" alt="Prescription Image">
            </div>
        </div>


    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.0.272/jspdf.debug.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js"></script>

    <script>
        $('.logout').click(function() {
            $.ajax({
                type: 'POST',
                url: '../user/logout.php',
                success: function(response) {
                    window.location.href = '../user/login.php';
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred while logging out:', error);
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            function handleDownloadPDF(event) {
                event.preventDefault();
                var medicalRecordID = event.target.closest('.medicalRecord').querySelector('.mrid').textContent.trim();
                var parts = medicalRecordID.split(':');
                var recordID = parts[1].trim();
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '../reports/medicalRecord.php';
                form.target = '_blank';
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'medicalrecordid';
                hiddenInput.value = recordID;
                form.appendChild(hiddenInput);
                document.body.appendChild(form);
                form.submit();
                console.log(recordID);
                document.body.removeChild(form);
            }
            var downloadPDFBtns = document.querySelectorAll(".downloadPDF");
            downloadPDFBtns.forEach(function(button) {
                button.addEventListener("click", handleDownloadPDF);
            });
            var searchCriteria = document.getElementById("searchCriteria");

            searchCriteria.addEventListener("change", function() {
                var selectedValue = searchCriteria.value;
                var searchDateInput = document.getElementById("searchDate");

                if (selectedValue === "doctorName") {
                    searchDateInput.placeholder = "e.g. Dr.";
                } else {
                    searchDateInput.placeholder = "e.g. YYYY/MM/DD";
                }
            });

            var searchInput = document.getElementById("searchDate");
            searchInput.addEventListener("input", function() {
                var searchValue = searchInput.value.trim().toLowerCase();
                var selectedCriteria = searchCriteria.value;
                var medicalRecords = document.querySelectorAll(".medicalRecord");

                medicalRecords.forEach(function(record) {
                    var dateTime = record.querySelector(".dateTime").textContent.toLowerCase();
                    var doctorName = record.querySelector(".doctorName").textContent.toLowerCase();

                    if (selectedCriteria === "date" && dateTime.includes(searchValue)) {
                        record.style.display = "";
                    } else if (selectedCriteria === "doctorName" && doctorName.includes(searchValue)) {
                        record.style.display = "";
                    } else {
                        record.style.display = "none";
                    }
                });
            });
            var viewPrescriptionBtns = document.querySelectorAll(".viewPrescription");
            var overlay = document.getElementById("overlay");
            var closeBtn = document.getElementById("closeBtn");
            viewPrescriptionBtns.forEach(function(button) {
                button.addEventListener("click", function() {
                    overlay.style.display = "block";
                });
            });

            closeBtn.addEventListener("click", function() {
                overlay.style.display = "none";
            });
            window.addEventListener("click", function(event) {
                if (event.target === overlay) {
                    overlay.style.display = "none";
                }
            });
        });
    </script>

    </html>
<?php endif; ?>