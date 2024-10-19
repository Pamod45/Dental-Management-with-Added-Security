<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require("../config/doctorDBConnection.php");
require('../config/logger.php');

$loadAddMedicalRecord = false;
$logger = createLogger('doctor.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.', 500);
    }
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.', 401);
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.', 500);
    }

    $username = htmlspecialchars($_SESSION['userid'], ENT_QUOTES, 'UTF-8');

    $getrecid = "SELECT MAX(medicalrecordid) AS current_id FROM medicalrecord";
    $stmt = $con->prepare($getrecid);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement.', 500);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $currentId = $row['current_id'];
    if ($currentId) {
        $nextId = 'MR' . str_pad((int)substr($currentId, 2) + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextId = "MR0001";
    }
    $getPatientData = "SELECT patientid, CONCAT(firstname, ' ', lastname) as patientname,
                       TIMESTAMPDIFF(YEAR, (SELECT dob FROM patient WHERE patientid = p.patientid), CURDATE()) 
                       AS patientage FROM patient p";
    $patientstmt = $con->prepare($getPatientData);
    if (!$patientstmt) {
        throw new Exception('Failed to prepare patient data statement.', 500);
    }

    $patientstmt->execute();
    $patientresults = $patientstmt->get_result();
    while ($patientrecord = $patientresults->fetch_assoc()) {
        $patients[] = $patientrecord;
    }
    $con->close();
    $docname = 'Dr. ' . htmlspecialchars($_SESSION['firstname'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($_SESSION['lastname'], ENT_QUOTES, 'UTF-8');
    $loadAddMedicalRecord = true;
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

if ($loadAddMedicalRecord): ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Medical Records</title>
        <link rel="icon" href="/images_new/favicon.png">
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="addMedicalRecord.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
        <script src="/bootstrap-5.3.2/dist/js/bootstrap.bundle.js"></script>
        <link rel="stylesheet" href="/bootstrap-5.3.2/dist/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
            <p class="">New Medcial Record</p>
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
                        <input type="text" class="typeahead form-control" id="pname" data-provide="typeahead" autocomplete="off">
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
                        <textarea rows="5" class="form-control" id="complaints"></textarea>
                    </div>
                    <div class="inneritem">
                        <label for="treatements">Treatments</label>
                        <textarea rows="5" class="form-control" id="treatments"></textarea>
                    </div>
                </div>
                <div class="item4">
                    <div class="inneritem">
                        <label>Prescription Image</label>
                        <input type="file" class="form-control" id="prescriptionInput" accept="image/*">
                        <img src="/images/prescription.jpg" alt="" id="prescriptionImage">
                    </div>
                </div>
                <div class="item5">
                    <div class="inneritem">
                        <label for="specialnotes">Special Notes</label>
                        <textarea rows="5" class="form-control" id="specialnotes"></textarea>
                    </div>
                </div>
                <div class="item6">
                    <button class="btn btn-primary" id="addrecord">Add record</button>
                </div>
            </div>
        </div>


    </body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.min.js"></script>

    <script src="../assets/js/sanitize.js"></script>
    <script>
        var patientNames = <?php echo json_encode($patients) ?>;


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

        $('#date').val(getFormattedDate(new Date()));

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

            rid.value = "<?php echo htmlspecialchars($nextId) ?>";
            dname.value = "<?php echo htmlspecialchars($docname) ?>";
            var addrecordbtn = document.getElementById("addrecord");

            addrecordbtn.addEventListener("click", function() {
                var data = {
                    medicalrecordid: rid.value,
                    doctorid: '<?php echo htmlspecialchars($_SESSION['doctorid']) ?>', // Retrieve from session or somewhere else
                    patientid: sanitize(pid.value), // Retrieve from session or somewhere else
                    specialnotes: sanitize(specialnotes.value),
                    presentingcomplaints: sanitize(complaints.value),
                    date: sanitize(date.value),
                    treatments: sanitize(treatments.value), // Assuming you have treatments data
                    time: getCurrentTime() // Assuming you have time data
                };


                if (!data.patientid || !data.doctorid || !data.date || !data.treatments || !data.presentingcomplaints) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please fill in all required fields: patient ID, doctor ID, date, treatment, and presenting complaints.',
                        confirmButtonText: 'Okay'
                    });
                } else {
                    $.ajax({
                        type: 'POST',
                        url: 'insert_record.php',
                        data: data,
                        headers: {
                            'X-CSRF-Token': "<?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); echo $_SESSION['csrf_token']; ?>",
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Record Added!',
                                text: 'The medical record has been successfully added.',
                                confirmButtonText: 'Okay'
                            }).then(() => {
                                window.location.href = 'patientMedicalRecords.php';
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to add the medical records. Please try again later.',
                                confirmButtonText: 'Okay'
                            });
                        }
                    });
                }

            });

            $('#prescriptionInput').change(function(event) {
                var selectedFile = event.target.files[0];
                var imageUrl = URL.createObjectURL(selectedFile);
                $('#prescriptionImage').attr('src', imageUrl);
            });

            $('#pname').typeahead({
                source: function(query, process) {
                    var data = [];
                    $.each(patientNames, function(i, patient) {
                        data.push(patient.patientname);
                    });
                    process(data);
                },
                autoSelect: true,
                afterSelect: function(item) {
                    var patient = patientNames.find(patient => patient.patientname === item);
                    pid.value = patient.patientid;
                    pname.value = patient.patientname;
                    page.value = patient.patientage;
                }
            });
        });

        function getCurrentTime() {
            var currentDate = new Date();
            var hours = currentDate.getHours();
            var minutes = currentDate.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            var currentTime = hours + ':' + minutes + ampm;
            return currentTime;
        }

        function getFormattedDate(date) {
            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            var day = ('0' + date.getDate()).slice(-2);
            return year + '-' + month + '-' + day;
        }
    </script>

    </html>
<?php endif; ?>