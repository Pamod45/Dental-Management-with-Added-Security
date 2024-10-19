<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('authorizeDoctorAccess.php');
require("../config/doctorDBConnection.php");
require('../config/logger.php');

$loadPatientMedicalRecords = false;
$logger = createLogger('doctor.log');
try{
    if (!$logger) {
        throw new Exception('Failed to create logger instance.',500);
    }
    $authorizedUser = authorizeDoctorAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.',403);
    }
    $con = getDatabaseConnection();
    if (!$con) {
        throw new Exception('Failed to connect to database.',500);
    }
    if (!isset($_SESSION['doctorid'])) {
        throw new Exception('Doctor not authenticated', 401);
    }
    $docid = htmlspecialchars($_SESSION['doctorid'], ENT_QUOTES, 'UTF-8');
    $getMRecquery = "SELECT medicalrecordid,
                    patientid,
                    (SELECT CONCAT(firstname, ' ', lastname) 
                    FROM patient WHERE patientid = m.patientid) 
                    AS patientname,
                    (SELECT CONCAT(firstname, ' ', lastname) 
                    FROM doctor WHERE doctorid = m.doctorid) 
                    AS doctorname,
                    date
                    FROM medicalrecord m
                    ORDER BY date DESC";

    $stmt = $con->prepare($getMRecquery);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $con->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $medicalRecords = [];
    while ($record = $result->fetch_assoc()) {
        $medicalRecords[] = array_map(function($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }, $record);
    }

    $stmt->close();
    $loadPatientMedicalRecords = true;
}catch(Exception $e){
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

if($loadPatientMedicalRecords):?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Medical Records</title>
    <link rel="icon" href="/images_new/favicon.png">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="medicalRecords.css">
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
        <p class="">Patient Medcial Records</p>
        <a href="addMedicalRecord.php">
            <button class="btn btn-primary addexpense" id="addexpense">Add Record</button>
        </a>
    </div>
    <div class="bottom">
        <input name="" id="searchValue" class="form-control" type="text" placeholder="eg :2024/05/02"></input>
        <input name="" id="searchValue2" class="form-control" type="text" placeholder="eg :Patient Name"></input>
        <table class="table table-responsive table-dark table-striped table-hover rounded ">
            <thead class="text-center">
                <tr>
                    <th class="small">Record ID</th>
                    <th class="small">Patient ID</th>
                    <th class="large">Patient Name</th>
                    <th class="medium">Date</th>
                    <th class="large">Doctor Name</th>
                    <th class="medium">Action</th>
                </tr>
            </thead>
            <tbody class="text-center">
            </tbody>
        </table>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="../assets/js/sanitize.js"></script>
<script>
    var records = <?php echo json_encode($medicalRecords); ?>;

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

    function addEventListenerToButtons() {
        var buttons = document.querySelectorAll('.btn-viewrecord');

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'medicalRecord.php';
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'medicalrecordid';
                hiddenInput.value = this.dataset.recordid;
                form.appendChild(hiddenInput);

                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = '<?php $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); echo $_SESSION["csrf_token"]; ?>'; 
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();

                document.body.removeChild(form);
            });
        });
    }

    function fillTable(data) {
        var tbody = document.querySelector(".table tbody");
        tbody.innerHTML = "";
        data.forEach(function(item) {
            var row = document.createElement("tr");

            row.innerHTML = `
                    <td class="small">${item.medicalrecordid}</td>
                    <td class="small">${item.patientid}</td>
                    <td class="large">${item.patientname}</td>
                    <td class="medium">${item.date}</td>
                    <td class="large">${item.doctorname}</td>
                    <td class="medium"><button class="btn btn-outline-info btn-viewrecord" data-recordid="${item.medicalrecordid}">View Record</button></td>
                `;
            tbody.appendChild(row);
        });
    }


    document.addEventListener("DOMContentLoaded", function() {
        var searchValue = document.getElementById("searchValue");
        var  searchValue2 = document.getElementById("searchValue2");
        var tbody = document.querySelector(".table tbody");
        fillTable(records);
        addEventListenerToButtons();
        searchValue2.addEventListener("input", function() {
            var inputValue = searchValue2.value.trim().toLowerCase();
            var inputValue2 = searchValue.value.trim().toLowerCase();
            var tableRows = document.querySelectorAll(".table tbody tr");
            tableRows.forEach(function(row) {
                var cellContent = row.cells[2].textContent.toLowerCase();
                var cellcontent2 = row.cells[3].textContent.toLowerCase();
                if (cellContent.includes(inputValue) && cellcontent2.includes(inputValue2)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });

        searchValue.addEventListener("input", function() {
            var inputValue = searchValue.value.trim().toLowerCase();
            var inputValue2 = searchValue2.value.trim().toLowerCase();
            var tableRows = document.querySelectorAll(".table tbody tr");

            tableRows.forEach(function(row) {
                var cellContent = row.cells[3].textContent.toLowerCase();
                var cellcontent2 = row.cells[2].textContent.toLowerCase();

                if (cellContent.includes(inputValue) && cellcontent2.includes(inputValue2)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });

    });
</script>

</html>

<?php endif; ?>