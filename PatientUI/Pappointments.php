<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('patientAccessControl.php');
include('authorizePatientAccess.php');
require('../config/logger.php');

$loadAppointmentsUI = false;
$logger = createLogger('patient_dashboard.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }
    $loadAppointmentsUI = true;
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
//calling APIs from the page
//../user/login.php
//delete_appointment.php
//fetch_appointments.php
if ($loadAppointmentsUI): ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Appointments</title>
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="PAppointment.css">
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
            <p>My Appointments</p>
            <a href="PNewAppointment.php"><button class="btn btn-primary">Make Appointment</button></a>
        </div>
        <div class="bottom">
            <select id="searchCriteria" class="form-select">
                <option value="date">Date</option>
                <option value="doctorName">Doctor Name</option>
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
                        <th>Doctor Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="text-center" style="overflow-y:auto; height:400px; position:absolute " id="appointmentTableBody">

                </tbody>
            </table>
        </div>
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="background-color: #D9D9D9; width: 600px;">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="editModalLabel">Appointment Details</h5>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <div class="form-group">
                                <label for="appointmentID" id="idlabel">Appointment ID:</label>
                                <input type="text" class="form-control" id="appointmentID" readonly>
                            </div>
                            <div class="form-group">
                                <label for="date">Date:</label>
                                <input type="text" class="form-control" id="date" readonly>
                            </div>
                            <div class="form-group">
                                <label for="time">Time:</label>
                                <input type="text" class="form-control" id="time" readonly>
                            </div>
                            <div class="form-group">
                                <label for="doctorName">Doctor Name:</label>
                                <input type="text" class="form-control" id="doctorName" readonly>
                            </div>
                            <div class="form-group">
                                <label for="Amount">Payment Amount:</label>
                                <input type="text" class="form-control" id="Amount" readonly>
                            </div>
                            <div class="form-group">
                                <label for="PMethod">Payment Method:</label>
                                <input type="text" class="form-control" id="PMethod" readonly>
                            </div>
                            <div class="form-group">
                                <label for="Qno">Queue No:</label>
                                <input type="text" class="form-control" id="Qno" readonly>
                            </div>
                        </form>
                    </div>
                    <div class="footer">
                        <button type="button" class="btn btn-primary" id="cancelAppointment">Cancel Appointment</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </body>
    <?php include("../config/includes.php"); ?>
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

            fillTable();

            var cancelButton = document.getElementById("cancelAppointment");
            var closeButton = document.getElementById("close");

            closeButton.addEventListener("click", function() {
                $('#editModal').modal('hide');
            });
            cancelButton.addEventListener("click", function() {
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
                        var modalBody = document.querySelector(".modal-body");
                        var appointmentID = modalBody.querySelector("#appointmentID").value;
                        var tableBody = document.querySelector(".table tbody");
                        $.ajax({
                            type: "POST",
                            url: "delete_appointment.php",
                            data: {
                                appointmentID: appointmentID
                            },
                            dataType: 'json',
                            beforeSend: function(xhr) {
                                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Indicate that this is an AJAX request
                                xhr.setRequestHeader("Accept", "application/json");
                            },
                            success: function(success) {
                                var rows = tableBody.querySelectorAll("tr");
                                rows.forEach(function(row) {
                                    if (row.cells[0].textContent === appointmentID) {
                                        row.remove();
                                    }
                                });
                                $('#editModal').modal('hide');
                            },
                            error: function(xhr, status, error) {
                                console.error('Error occurred while deleting appointment:', error);
                            }
                        });
                        $('#editModal').modal('hide');
                    }
                });

            });

            var searchInput = document.getElementById("searchDate");

            searchInput.addEventListener("input", function() {
                const searchCriteria = document.getElementById("searchCriteria");
                var searchValue = searchInput.value.trim().toLowerCase();
                const selectedCriteria = searchCriteria.value.toLowerCase();
                var tableRows = document.querySelectorAll(".table tbody tr");

                tableRows.forEach(function(row) {
                    var cellContent;
                    if (selectedCriteria === "date") {
                        cellContent = row.cells[1].textContent.toLowerCase();
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
                    searchDateInput.placeholder = "e.g. Dr.";
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
            $.ajax({
                type: "GET",
                url: "fetch_appointments.php",
                dataType: 'json',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest"); // Indicate that this is an AJAX request
                    xhr.setRequestHeader("Accept", "application/json");
                },
                success: function(appointmentsJSON) {
                    var appointments = appointmentsJSON;
                    var tableBody = $("#appointmentTableBody");
                    tableBody.empty();
                    appointments.forEach(function(appointment) {
                        var rowHtml = "<tr>" +
                            "<td>" + appointment.appointmentID + "</td>" +
                            "<td>" + appointment.date + "</td>" +
                            "<td>" + appointment.time + "</td>" +
                            "<td>" + appointment.queueNo + "</td>" +
                            "<td>" + appointment.doctorName + "</td>" +
                            "<td>" + appointment.status + "</td>" +
                            "<td><button class='btn btn-outline-info btn-view' style='width:100px; margin-top: 0px;'>View</button></td>" +
                            "</tr>";
                        tableBody.append(rowHtml);
                    });
                    var viewButtons = document.querySelectorAll(".btn-view");
                    viewButtons.forEach(function(button) {
                        button.addEventListener("click", function() {
                            var row = this.closest("tr");
                            var appointmentID = row.cells[0].textContent;
                            currentAppointment = findAppointmentById(appointments, appointmentID);

                            var date = currentAppointment.date;
                            var time = currentAppointment.time;
                            var queueNo = currentAppointment.queueNo;
                            var doctorName = currentAppointment.doctorName;
                            var status = currentAppointment.status;
                            var charge = currentAppointment.charge;
                            var paymentName = currentAppointment.paymentMethod;

                            document.getElementById("appointmentID").value = appointmentID;
                            document.getElementById("date").value = date;
                            document.getElementById("time").value = time;
                            document.getElementById("Qno").value = queueNo;
                            document.getElementById("doctorName").value = doctorName;
                            document.getElementById("Amount").value = charge;
                            document.getElementById("PMethod").value = paymentName;
                            var cancelButton = document.getElementById('cancelAppointment');
                            if (status === "Completed") {
                                cancelButton.disabled = true;
                            } else {
                                cancelButton.disabled = false;
                            }
                            $('#editModal').modal('show');
                        });
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching appointments:", error);
                }
            });
        }
    </script>

    </html>
<?php endif; ?>