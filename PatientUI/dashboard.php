<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

require '../config/fatalErrorWarningHandler.php';
require 'patientAccessControl.php';
require "../config/patientDBConnection.php";
require '../config/logger.php' ;

$loadDashBoard = false;
$logger = createLogger('patient.log');
try {
    // $result = authorizePatientAccessFromCookie();
    // $jsonResult = json_encode($result);
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.',403);
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $username = $_SESSION['userid'];

    $query5 = $con->prepare("SELECT * FROM patient WHERE userid = ?");
    if (!$query5) {
        throw new Exception('Failed to prepare statement for fetching patient details.');
    }

    $query5->bind_param("s", $username);
    if (!$query5->execute()) {
        throw new Exception('Execution failed for patient details query.');
    }

    $result5 = $query5->get_result();
    if ($result5->num_rows === 0) {
        throw new Exception('No patient details found for user ID: ' . $username);
    }

    $row5 = $result5->fetch_assoc();

    $_SESSION['patientid'] = $row5['patientid'];
    $_SESSION['firstname'] = $row5['firstname'];
    $_SESSION['lastname'] = $row5['lastname'];
    $_SESSION['email'] = $row5['email'];
    $_SESSION['dob'] = $row5['dob'];
    $_SESSION['address'] = $row5['address'];
    $_SESSION['contactno'] = $row5['contactno'];

    $appointmentCount = 0;

    $patientId = $_SESSION['patientid'];

    $query = $con->prepare("SELECT COUNT(*) AS AppointmentCount 
                            FROM appointment 
                            WHERE patientid = ? 
                            AND status = 'In Progress' 
                            AND appointmentdate >= CURRENT_DATE()");
    if (!$query) {
        throw new Exception('Failed to prepare statement for counting appointments.');
    }
    $query->bind_param("s", $patientId);
    if (!$query->execute()) {
        throw new Exception('Execution failed for appointment count query.');
    }
    $result = $query->get_result();
    $row1 = $result->fetch_assoc();

    $appointmentCount = $row1['AppointmentCount'];
    if ($appointmentCount > 0 || $appointmentCount == NULL) {
        $query2 = $con->prepare("SELECT a.*, d.lastname 
                            FROM appointment AS a 
                            JOIN doctor AS d ON a.doctorid = d.doctorid 
                            WHERE a.patientid = ? 
                            AND a.appointmentdate >= CURRENT_DATE() 
                            AND a.status = 'In Progress' 
                            ORDER BY a.appointmentdate ASC 
                            LIMIT 3");

        if (!$query2) {
            throw new Exception('Failed to prepare statement for fetching appointments.');
        }
        $query2->bind_param("s", $patientId);
        if (!$query2->execute()) {
            throw new Exception('Execution failed for fetching appointments query.');
        }
        $result2 = $query2->get_result();

        while ($row = $result2->fetch_assoc()) {
            $latestAppointments[] = $row;
        }
    }
    $loadDashBoard = true;
} catch (Throwable $e) {
    if ($logger)
        $logger->critical('Error occurred: ' . $e->getMessage());
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo '
    <h1>Something went wrong</h1>
    <p>' . htmlspecialchars($e->getMessage()) . '</p>
    ';
}
if ($loadDashBoard): ?>
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
        <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
        <title>Patient DashBoard</title>
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

        <div class="grid-container">
            <div class="grid-item item1">
                <p class="greeting">Hello <?php echo htmlspecialchars($_SESSION['firstname']) . ' ' . htmlspecialchars($_SESSION['lastname']); ?> <br> Current Appointments</p>
                <div class="appointmentNumber">
                    <span class="textAppointmentNumber"><?php echo htmlspecialchars($appointmentCount) ?></span>
                </div>
                <div class="appointment appointment1">
                    <?php if (!empty($latestAppointments[0])) : ?>
                        <p><?php echo htmlspecialchars($latestAppointments[0]['appointmentdate']) ?></p>
                        <p><?php echo 'Dr. ' . htmlspecialchars($latestAppointments[0]['lastname']) ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[0]['appointmentslot']) ?></p>
                    <?php else : ?>
                        <p>No appointment</p>
                    <?php endif; ?>
                </div>
                <div class="appointment appointment2">
                    <?php if (!empty($latestAppointments[1])) : ?>
                        <p><?php echo htmlspecialchars($latestAppointments[1]['appointmentdate']) ?></p>
                        <p><?php echo 'Dr. ' . htmlspecialchars($latestAppointments[1]['lastname']) ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[1]['appointmentslot']) ?></p>
                    <?php else : ?>
                        <p>No appointment</p>
                    <?php endif; ?>
                </div>
                <div class="appointment">
                    <?php if (!empty($latestAppointments[2])) : ?>
                        <p><?php echo htmlspecialchars($latestAppointments[2]['appointmentdate']) ?></p>
                        <p><?php echo 'Dr. ' . htmlspecialchars($latestAppointments[2]['lastname']) ?></p>
                        <p><?php echo htmlspecialchars($latestAppointments[2]['appointmentslot']) ?></p>
                    <?php else : ?>
                        <p>No appointment</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid-item item2">
                <div class="date" id="systemDate">SUN 22 MAY 2024</div>
                <div class="time" id="dynamicTime">05 : 08 : 23 </div>
            </div>
            <div class="grid-item item3">
                <a href="MedicalRecords.php">Medical Records</a>
            </div>
            <div class="grid-item item4">
                <a href="Pappointments.php">My Appointments</a>
            </div>
            <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        </div>
        <?php include("../config/includes.php"); ?>

        <script>
            $('.logout').click(function() {
                $.ajax({
                    type: 'POST',
                    url: '../user/logout.php',
                    data: {
                        csrf_token: '<?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32));echo $_SESSION['csrf_token']; ?>'
                    },
                    success: function(response) {
                        window.location.href = '../user/login.php';
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to logout. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                        });
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