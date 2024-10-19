<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require("../config/doctorDBConnection.php");
require('../config/logger.php');

$loadMyAppointmentsUI = false;
$logger = createLogger('doctor.log');

try {
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }

    if (!isset($_SESSION['doctorid'])) {
        throw new Exception('Doctor session not found.', 403);
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.');
    }
    $docid = $_SESSION['doctorid'];

    // Secure query to fetch appointment records with patient names
    $stmt = $con->prepare("
        SELECT ap.*, CONCAT(p.firstname, ' ', p.lastname) AS patientname 
        FROM appointment ap 
        JOIN patient p ON ap.patientid = p.patientid 
        WHERE ap.doctorid = ? 
        ORDER BY ap.appointmentdate DESC
    ");

    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $con->error, 500);
    }

    // Bind the doctor ID parameter
    $stmt->bind_param('s', $docid);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error, 500);
    }
    $result = $stmt->get_result();
    $appointments = [];
    while ($record = $result->fetch_assoc()) {
        $record['patientname'] = htmlspecialchars($record['patientname'], ENT_QUOTES, 'UTF-8');
        $appointments[] = $record;
    }

    // Clean up resources
    $stmt->close();
    $con->close();

    $loadMyAppointmentsUI = true;
} catch (Exception $e) {
    // Log the error if logging is set up
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
if ($loadMyAppointmentsUI) : ?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Appointments</title>
        <link rel="icon" href="/images_new/favicon.png">
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="PAppointment.css">
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
            <p>My Appointments</p>
        </div>
        <div class="bottom">
            <select id="searchCriteria" class="form-select">
                <option value="date">Date</option>
                <option value="doctorName">Patient Name</option>
                <option value="status">Status</option>
            </select>
            <div class="search">
                <input type="text" class="form-control" id="searchDate" placeholder="eg :YYYY/MM/DD">
            </div>
            <table class="table table-responsive table-dark table-striped table-hover rounded ">
                <thead class="text-center">
                    <tr>
                        <th>AppointmentID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Queue No</th>
                        <th>Patient Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center" style="overflow-y:auto; height:400px; position:absolute " id="appointmentTableBody">
                </tbody>
            </table>
        </div>

    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <?php include("../config/includes.php"); ?>
    <script>
        $('.logout').click(function() {
                $.ajax({
                    type: 'POST',
                    url: '../user/logout.php',
                    data: {
                        csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
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


        document.addEventListener("DOMContentLoaded", function() {
            fillTable();
            var searchInput = document.getElementById("searchDate");

            searchInput.addEventListener("input", function() {
                const searchCriteria = document.getElementById("searchCriteria");
                var searchValue = sanitize(searchInput.value.trim().toLowerCase());
                const selectedCriteria = searchCriteria.value.toLowerCase();
                var tableRows = document.querySelectorAll(".table tbody tr");
                tableRows.forEach(function(row) {
                    var cellContent;
                    if (selectedCriteria === "date") {
                        cellContent = row.cells[2].textContent.toLowerCase();
                    } else if (selectedCriteria === "doctorname") {
                        cellContent = row.cells[4].textContent.toLowerCase();
                    } else if (selectedCriteria === "status") {
                        cellContent = row.cells[5].textContent.toLowerCase();
                    }
                    if (cellContent.includes(searchValue)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });

            var searchCriteria = document.getElementById("searchCriteria");

            searchCriteria.addEventListener("change", function() {
                var selectedValue = searchCriteria.value;
                var searchDateInput = document.getElementById("searchDate");

                if (selectedValue === "doctorName") {
                    searchDateInput.placeholder = "e.g. PatientName";
                } else if (selectedValue === "date") {
                    searchDateInput.placeholder = "e.g. YYYY/MM/DD";
                } else if (selectedValue === "status") {
                    searchDateInput.placeholder = "e.g. Complete/In Progress";
                }
            });

        });

        function findAppointmentById(appointments, appointmentId) {
            for (var i = 0; i < appointments.length; i++) {
                if (appointments[i].appointmentID === appointmentId) {
                    return appointments[i];
                }
            }
            return null;
        }

        function fillTable() {
            var appointments = <?php echo json_encode($appointments); ?>;
            var tableBody = $("#appointmentTableBody");
            tableBody.empty();

            appointments.forEach(function(appointment) {

                var rowHtml = "<tr>" +
                    "<td>" + sanitize(appointment.appointmentid) + "</td>" +
                    "<td>" + sanitize(appointment.appointmentslot) + "</td>" +
                    "<td>" + sanitize(appointment.appointmentdate) + "</td>" +
                    "<td>" + sanitize(appointment.queueno) + "</td>" +
                    "<td>" + sanitize(appointment.patientname) + "</td>" +
                    "<td>" + sanitize(appointment.status) + "</td>";
                if (appointment.status === "In Progress") {
                    rowHtml += "<td><button class='btn btn-outline-info btn-cancel' style='width:100px; margin-top: 0px;'>Cancel</button></td>";
                } else {
                    rowHtml += "<td><button class='btn btn-outline-info btn-cancel' style='width:100px; margin-top: 0px;' disabled>Cancel</button></td>";
                }
                rowHtml += "</tr>";

                tableBody.append(rowHtml);
            });
            var viewButtons = document.querySelectorAll(".btn-cancel");
            viewButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    var row = this.closest("tr");
                    var appointmentID = sanitize(row.cells[0].textContent);
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This appointment will be canceled!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, cancel it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var tableBody = document.querySelector(".table tbody");
                            $.ajax({
                                type: "POST",
                                url: "delete_appointment.php",
                                data: {
                                    appointmentID: appointmentID
                                },
                                headers: {
                                    'X-CSRF-Token': "<?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32));echo $_SESSION['csrf_token']; ?>",
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                success: function(success) {
                                    var rows = tableBody.querySelectorAll("tr");
                                    rows.forEach(function(row) {
                                        if (row.cells[0].textContent === appointmentID) {
                                            row.remove();
                                        }
                                    });
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'An error occurred while canceling the appointment.',
                                        confirmButtonText: 'Okay'
                                    });
                                }
                            });
                        }
                    });
                });
            });
        }

        function sanitize(input) {
            const string = input.toString();
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#x27;',
                "/": '&#x2F;',
            };
            const reg = /[&<>"'/]/ig;
            return string.replace(reg, (match) => (map[match]));
        }
    </script>

    </html>
<?php endif; ?>