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

$loadNewAppointmentUI = false;
$logger = createLogger('patient.log');
//required apis 
//fetch_available_slots.php 
//create_appointment.php
//fetch_queue_number.php

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
    $nextAppointmentID = '';
    $stmt = $con->prepare("SELECT MAX(appointmentid) AS max_id FROM appointment");
    if (!$stmt) {
        throw new Exception("Failed to prepare SQL statement: " . $con->error, 500);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['max_id']) {
            $lastAppointmentID = $row['max_id'];
            $numericPart = (int) substr($lastAppointmentID, 2);
            $nextNumericPart = $numericPart + 1;
            $nextAppointmentID = 'AP' . sprintf("%03d", $nextNumericPart);
        } else {
            $nextAppointmentID = 'AP001';
        }
    }
    $doctorStmt = $con->prepare("SELECT * FROM doctor");
    if (!$doctorStmt) {
        throw new Exception("Failed to prepare SQL statement for doctors: " . $con->error, 500);
    }
    $doctorStmt->execute();
    $doctorResult = $doctorStmt->get_result();
    $options = "";
    if ($doctorResult && $doctorResult->num_rows > 0) {
        while ($doctorRow = $doctorResult->fetch_assoc()) {
            $doctorFullName = htmlspecialchars($doctorRow['firstname'] . " " . $doctorRow['lastname'], ENT_QUOTES, 'UTF-8');
            $doctorID = htmlspecialchars($doctorRow['doctorid'], ENT_QUOTES, 'UTF-8');
            $options .= "<option value='{$doctorID}'>Dr. {$doctorFullName}</option>";
        }
    }
    $paymentMethodStmt = $con->prepare("SELECT * FROM paymentmethod");
    if (!$paymentMethodStmt) {
        throw new Exception("Failed to prepare SQL statement for payment methods: " . $con->error, 500);
    }

    $paymentMethodStmt->execute();
    $paymentMethodResult = $paymentMethodStmt->get_result();
    $paymentMethodOptions = "";
    if ($paymentMethodResult && $paymentMethodResult->num_rows > 0) {
        while ($paymentMethodRow = $paymentMethodResult->fetch_assoc()) {
            $paymentMethodName = htmlspecialchars($paymentMethodRow['name'], ENT_QUOTES, 'UTF-8');
            $paymentMethodID = htmlspecialchars($paymentMethodRow['paymentmethodid'], ENT_QUOTES, 'UTF-8');
            $paymentMethodOptions .= "<option value='{$paymentMethodID}'>{$paymentMethodName}</option>";
        }
    }
    $stmt->close();
    $doctorStmt->close();
    $paymentMethodStmt->close();
    $loadNewAppointmentUI = true;
} catch (Exception $e) {
    if ($logger)
        $logger->error("Error in " . (__FILE__) . ": " . $e->getMessage(), [
            'code' => $e->getCode()
        ]);
    http_response_code($e->getCode() ? $e->getCode() : 500);
    echo '
    <h1>Something went wrong</h1>
    <p>' . htmlspecialchars($e->getMessage()) . '</p>
    ';
}

if ($loadNewAppointmentUI): ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Appointments</title>
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="PNewAppointment.css">
        <link rel="icon" href="/images_new/favicon.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
        <script src="/bootstrap-5.3.2/dist/js/bootstrap.bundle.js"></script>
        <link rel="stylesheet" href="/bootstrap-5.3.2/dist/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.min.css">

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
            <p>New Appointment</p>
            <a href="Pappointments.php"><button class="btn btn-primary">View Appointments</button></a>
        </div>
        <div class="bottom">
            <div class="mycontainer">
                <form id="appointmentForm">
                    <div class="form-group">
                        <label for="AID" id="idlabel">Appointment ID</label>
                        <input type="text" class="form-control" id="AID" value="<?php echo isset($nextAppointmentID) ? $nextAppointmentID : '--' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="ADname">Doctor Name</label>
                        <select class="form-select" id="ADname">
                            <?php echo isset($options) ? $options : null; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" class="form-control" id="ADate">
                    </div>
                    <div class="form-group time">
                        <label>Time</label>
                        <div class="time-slot disable" id="slot1" data-starttime="08:30AM">08:30-11:00AM</div>
                        <div class="time-slot disable" id="slot2" data-starttime="11:30AM">11:30AM-01:30PM</div>
                        <div class="time-slot disable" id="slot3" data-starttime="02:30PM">02:30PM-05:00PM</div>
                        <div class="time-slot disable" id="slot4" data-starttime="05:30PM">05:30PM-08:00PM</div>
                    </div>
                    <div class="form-group">
                        <label for="Qno">Queue No</label>
                        <input type="text" class="form-control" id="Qno" value="" readonly>
                    </div>
                    <div class="form-group">
                        <label for="ACharges">Appointment Charges</label>
                        <input type="text" class="form-control" id="ACharges" value="Rs :3500" readonly>
                    </div>
                    <div class="form-group">
                        <label for="PMethod">Payment Method</label>
                        <select class="form-select" id="PMethod">
                            <?php echo isset($paymentMethodOptions) ? $paymentMethodOptions : null; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="button" id="makeAppointmentBtn" class="btn btn-primary text-center" value="Make Appointment">
                    </div>
                </form>
            </div>
        </div>

    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.min.js"></script>
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
        document.addEventListener('DOMContentLoaded', function() {

            document.getElementById('ADate').addEventListener('change', function() {

                timeSlots.forEach(function(slot) {
                    // Add the "disable" class
                    slot.classList.add('disable');
                    // Remove the "enable" class
                    slot.classList.remove('enable');

                    slot.classList.remove('selected');
                });
                document.getElementById("Qno").value = "";
                doctors = document.getElementById("ADname");
                // Get the selected date
                var selectedDate = this.value;
                var doctorid = doctors.value;
                $.ajax({
                    url: 'fetch_available_slots.php', // Path to your PHP script
                    method: 'POST',
                    data: {
                        date: selectedDate,
                        doctorid: doctorid
                    },
                    dataType: 'json',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Indicate that this is an AJAX request
                        xhr.setRequestHeader("Accept", "application/json");
                    },
                    success: function(recordsJSON) {

                        // Populate the select element with the available time slots
                        var slots = recordsJSON;

                        slots.forEach(function(slot) {
                            // console.log(slot.starttime + " " + slot.duration);
                            slot = getSlot(slot.starttime);

                            // Add the "disable" class
                            slot.classList.add('enable');
                            // Remove the "enable" class
                            slot.classList.remove('disable');

                            slot.classList.remove('selected');
                        });
                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        console.error(error);
                    }
                });
            });

            var doctorSelect = document.getElementById("ADname");
            const timeSlots = document.querySelectorAll('.time-slot');
            doctorSelect.addEventListener("change", function() {
                document.getElementById("Qno").value = "";
                document.getElementById('ADate').value = "";
                timeSlots.forEach(function(slot) {
                    slot.classList.remove('enable');

                    slot.classList.remove('selected');
                    // Add the "disable" class
                    slot.classList.add('disable');
                    // Remove the "enable" class

                });
            });




            timeSlots.forEach(slot => {
                slot.addEventListener('click', function() {
                    // Check if the clicked slot is disabled
                    if (this.classList.contains('disable')) {
                        return; // Ignore click event if the slot is disabled
                    }

                    // Remove 'selected' class from previously selected slot
                    const selectedSlot = document.querySelector('.time-slot.selected');
                    if (selectedSlot) {
                        selectedSlot.classList.remove('selected');
                    }

                    // Add 'selected' class to the clicked slot
                    this.classList.add('selected');

                    const doctorId = document.getElementById("ADname").value;
                    const startTime = this.getAttribute('data-starttime');
                    const date = document.getElementById('ADate').value;
                    $.ajax({
                        url: 'fetch_queue_number.php', // Path to your PHP script
                        method: 'POST',
                        data: {
                            doctorId: doctorId,
                            startTime: startTime,
                            selectedDate: date
                        },
                        dataType: 'json',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Indicate that this is an AJAX request
                            xhr.setRequestHeader("Accept", "application/json");
                        },
                        success: function(response) {
                            const data = response;
                            // Increment the count to get the queue number
                            const queueNo = parseInt(data.AppointmentCount) + 1;

                            console.log(data);
                            document.getElementById("Qno").value = queueNo;
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                });
            });
            document.getElementById('makeAppointmentBtn').addEventListener('click', function() {
                // Check if a time slot is selected
                const selectedSlot = document.querySelector('.time-slot.selected');
                if (!selectedSlot) {
                    // If no time slot is selected, show an error message
                    Swal.fire({
                        title: 'Error',
                        text: 'Please select a time slot',
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                    return;
                }

                // Extract appointment data
                const appointmentData = {
                    appointmentId: document.getElementById("AID").value,
                    doctorId: document.getElementById("ADname").value,
                    appointmentCharges: document.getElementById("ACharges").value,
                    appointmentDate: document.getElementById("ADate").value,
                    appointmentSlot: selectedSlot.getAttribute('data-starttime'),
                    queueNo: document.getElementById("Qno").value,
                    paymentMethod: document.getElementById("PMethod").value,
                    status: 'In Progress'
                };
                $.ajax({
                    url: 'create_appointment.php', // Path to your PHP script
                    method: 'POST',
                    data: appointmentData,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Indicate that this is an AJAX request
                        xhr.setRequestHeader("Accept", "application/json");
                    },
                    success: function(response) {
                        let currentAppointmentID = document.getElementById("AID").value;
                        let numericPart = parseInt(currentAppointmentID.substring(2));
                        let nextNumericPart = numericPart + 1;
                        let nextAppointmentID = "AP" + nextNumericPart.toString().padStart(3, '0');
                        document.getElementById("AID").value = nextAppointmentID;
                        document.getElementById("ADname").selectedIndex = 0; // Select the first option
                        document.getElementById("ADate").value = ""; // Clear appointment date
                        document.getElementById("Qno").value = ""; // Clear queue number
                        document.getElementById("PMethod").selectedIndex = 0; // Select the first payment method
                        const timeSlots = document.querySelectorAll('.time-slot');
                        timeSlots.forEach(function(slot) {
                            slot.classList.remove('enable');

                            slot.classList.remove('selected');
                            // Add the "disable" class
                            slot.classList.add('disable');
                            // Remove the "enable" class

                        });
                        // Show success message
                        Swal.fire({
                            title: 'Appointment Confirmed!',
                            text: 'Your appointment has been successfully made.',
                            icon: 'success',
                            confirmButtonText: 'OK',
                        });
                    },
                    error: function(xhr, status, error) {
                        // Show error message
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to create appointment. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                        });
                        console.error(error);
                    }
                });
            });

            // Get the current date
            const currentDate = new Date().toISOString().split('T')[0];

            // Set the minimum date for the datepicker
            document.getElementById('ADate').setAttribute('min', currentDate);

        });

        function getSlot(timestring) {
            // Convert the time string to lowercase for case-insensitive comparison
            timestring = timestring.toLowerCase();

            if (timestring == "08:30am") {
                return slot1;
            } else if (timestring == "11:30am") {
                return slot2;
            } else if (timestring == "02:30pm") {
                return slot3;
            } else if (timestring == "05:30pm") {
                return slot4;
            }
            return null;
        }
    </script>

    </html>
<?php endif; ?>