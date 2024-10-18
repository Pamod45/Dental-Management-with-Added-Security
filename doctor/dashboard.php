<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require("../config/doctorDBConnection.php");
require('../config/logger.php');

$loadDashBoard = false;
$logger = createLogger('doctor.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    if (!isset($_SESSION['userid'])) {
        throw new Exception('User session not found.', 403);
    }

    $username = $_SESSION['userid'];

    // Secure query to fetch doctor details
    $stmt = $con->prepare("SELECT * FROM doctor WHERE userid = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $con->error, 500);
    }

    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error, 500);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        throw new Exception('Doctor not found.', 404);
    }

    // Store doctor information in session securely
    $_SESSION['doctorid'] = $row['doctorid'];
    $_SESSION['firstname'] = htmlspecialchars($row['firstname']);
    $_SESSION['lastname'] = htmlspecialchars($row['lastname']);
    $_SESSION['email'] = htmlspecialchars($row['email']);
    $_SESSION['address'] = htmlspecialchars($row['address']);
    $_SESSION['contactno'] = htmlspecialchars($row['contactno']);
    $_SESSION['category'] = htmlspecialchars($row['category']);

    $docid = $row['doctorid'];

    // Secure query to fetch appointment count
    $stmt2 = $con->prepare("
        SELECT COUNT(*) as appointmentcount
        FROM appointment
        WHERE doctorid = ?
        AND appointmentdate >= CURRENT_DATE()
        AND status = 'In Progress'
    ");
    if (!$stmt2) {
        throw new Exception('Failed to prepare statement: ' . $con->error, 500);
    }

    $stmt2->bind_param('s', $docid);
    if (!$stmt2->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt2->error, 500);
    }

    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $appointmentCount = $row2['appointmentcount'] ?? 0;

    // Secure query to fetch latest appointments
    $stmt3 = $con->prepare("
        SELECT ap.*, p.firstname, p.lastname
        FROM appointment ap
        JOIN patient p ON ap.patientid = p.patientid
        WHERE ap.doctorid = ?
        AND ap.appointmentdate >= CURRENT_DATE()
        AND ap.status = 'In Progress'
        ORDER BY ap.appointmentdate ASC
        LIMIT 3
    ");
    if (!$stmt3) {
        throw new Exception('Failed to prepare statement: ' . $con->error, 500);
    }

    $stmt3->bind_param('s', $docid);
    if (!$stmt3->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt3->error, 500);
    }

    $result3 = $stmt3->get_result();
    $latestAppointments = [];
    while ($row3 = $result3->fetch_assoc()) {
        // Sanitizing output
        $latestAppointments[] = $row3;
    }
    $loadDashBoard = true;
} catch (Exception $e) {
    if ($logger)
        $logger->error($e->getMessage());
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo '
    <h1>Something went wrong</h1>
    <p>' . htmlspecialchars($e->getMessage()) . '</p>
    <a href="/user/login.php">Go to Login Page</a>
    ';
    exit;
}
$systemDate = date('D d M Y');
$dynamicTime = date('H : i : s');
if($loadDashBoard):?>


    <!DOCTYPE html>
    <html lang="en">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="dashboard.css">
        <link rel="stylesheet" href="sidebar.css" />
        <link rel="icon" href="/images_new/favicon.png">
        <script src="/bootstrap-5.3.2/dist/js/bootstrap.bundle.js"></script>
        <link rel="stylesheet" href="/bootstrap-5.3.2/dist/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
        <title>Doctor Dashboard</title>
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
    
        <div class="grid-container">
            <div class="grid-item item1">
                <p class="greeting">Hello Dr. <?php echo htmlspecialchars($row['firstname']) . ' ' . htmlspecialchars($row['lastname']); ?> <br> Upcoming Appointments</p>
                <div class="appointmentNumber">
                    <span class="textAppointmentNumber"><?php echo htmlspecialchars($row2['appointmentcount']); ?></span>
                </div>
                <div class="appointment appointment1">
                    <?php if (!empty($latestAppointments[0])) : ?>
                        <p><?php echo htmlspecialchars($latestAppointments[0]['appointmentdate']); ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[0]['firstname']) . ' ' . htmlspecialchars($latestAppointments[0]['lastname']); ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[0]['appointmentslot']); ?></p>
                    <?php else : ?>
                        <p>No appointment</p>
                    <?php endif; ?>
                </div>
                <div class="appointment appointment2">
                    <?php if (!empty($latestAppointments[1])) : ?>
                        <p><?php echo htmlspecialchars($latestAppointments[1]['appointmentdate']); ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[1]['firstname']) . ' ' . htmlspecialchars($latestAppointments[1]['lastname']); ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[1]['appointmentslot']); ?></p>
                    <?php else : ?>
                        <p>No appointment</p>
                    <?php endif; ?>
                </div>
                <div class="appointment">
                    <?php if (!empty($latestAppointments[2])) : ?>
                        <p><?php echo htmlspecialchars($latestAppointments[2]['appointmentdate']); ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[2]['firstname']) . ' ' . htmlspecialchars($latestAppointments[2]['lastname']); ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[2]['appointmentslot']); ?></p>
                    <?php else : ?>
                        <p>No appointment</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid-item item2">
                <div class="date" id="systemDate"><?php echo htmlspecialchars($systemDate); ?></div>
                <div class="time" id="dynamicTime"><?php echo htmlspecialchars($dynamicTime); ?></div>
            </div>
            <div class="grid-item item3">
                <a href="doctorSchedule.php">My Schedule</a>
            </div>
            <div class="grid-item item4">
                <a href="patientMedicalRecords.php">Patient Medical Records</a>
            </div>
        </div>
    
        <?php include("../config/includes.php"); ?>
        <script>
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
            window.onload = function() {
                var currentDate = new Date();
                var day = currentDate.getDate();
                var month = currentDate.getMonth() + 1;
                var year = currentDate.getFullYear();
                var daysOfWeek = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
                var months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                var dayOfWeek = daysOfWeek[currentDate.getDay()];
    
                var formattedDate = dayOfWeek + " " + (day < 10 ? '0' + day : day) + " " + months[month] + " " + year;
    
                document.getElementById('systemDate').innerHTML = formattedDate;
                document.getElementById("systemDate").style.wordSpacing = "15px";
            };
    
            function updateTime() {
                var currentTime = new Date();
                var hours = currentTime.getHours();
                var minutes = currentTime.getMinutes();
                var seconds = currentTime.getSeconds();
    
                hours = (hours < 10 ? "0" : "") + hours;
                minutes = (minutes < 10 ? "0" : "") + minutes;
                seconds = (seconds < 10 ? "0" : "") + seconds;
    
                var formattedTime = hours + " : " + minutes + " : " + seconds;
    
                document.getElementById('dynamicTime').innerHTML = formattedTime;
            }
    
            function toggleNavbar() {
                var navbar = document.getElementById("navbar");
                var menu = document.getElementById("menu");
                var homeText = document.getElementById("homeText");
                var appointmentText = document.getElementById("appointmentText");
    
                if (navbar.classList.contains("menu-closed")) {
                    navbar.classList.remove("menu-closed");
                    navbar.classList.add("menu-open");
                    menu.style.display = "block";
                    homeText.style.display = "inline";
                    appointmentText.style.display = "inline";
                } else {
                    navbar.classList.remove("menu-open");
                    navbar.classList.add("menu-closed");
                    menu.style.display = "none";
                    homeText.style.display = "none";
                    appointmentText.style.display = "none";
                }
            }
            setInterval(updateTime, 1000);
        </script>
    </body>
    
    </html>
    <?php endif; ?>