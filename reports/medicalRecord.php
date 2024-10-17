<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

include('../config/fatalErrorWarningHandler.php');
include('../patientUI/patientAccessControl.php');
include('../patientUI/authorizePatientAccess.php');
require("../config/patientDBConnection.php");
require('../config/logger.php');

$loadMedicalReport = false;
$logger = createLogger('patient_dashboard.log');
try {
    if (!$logger) {
        throw new Exception('Failed to create logger instance.');
    }
    $authorizedUser = authorizePatientAccess();
    if (!$authorizedUser) {
        throw new Exception('User not authorized.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medicalrecordid'])) {
        $con = getDatabaseConnection();
        if (!$con) {
            throw new Exception('Failed to connect to database.');
        }
        //sanitize medical record
        $medicalrecordid = htmlspecialchars($_POST['medicalrecordid'], ENT_QUOTES, 'UTF-8');
        $query = "SELECT m.*,
            CONCAT(p.firstname, ' ', p.lastname) AS patientname,
            CONCAT(d.firstname, ' ', d.lastname) AS doctorname,
            YEAR(CURDATE()) - YEAR(p.dob) - 
            (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(p.dob, '%m%d')) AS age 
            FROM medicalrecord m 
            JOIN patient p ON m.patientid = p.patientid
            JOIN doctor d ON d.doctorid = m.doctorid
            WHERE medicalrecordid = ?";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement.");
        }
        $stmt->bind_param("s", $medicalrecordid);
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed.");
        }
        $result = $stmt->get_result();
        $record = null;
        if ($result && $result->num_rows > 0) {
            $record = $result->fetch_assoc();
            foreach ($record as $key => $value) {
                $record[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        } else {
            throw new Exception("No records found for the provided ID.");
        }
        $stmt->close();
        $con->close();
        $hasError = false;
    } else {
        throw new Exception("Invalid request or missing medical record ID.");
    }
    $loadMedicalReport = true;
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
    exit;
}

if ($loadMedicalReport) : ?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Medical Record</title>
        <link rel="stylesheet" href="medicalRecord.css">
    </head>

    <body>
        <button class="non-printable" onclick="window.print()">Download PDF</button>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
        <script src="/config/jquery-3.7.1.min.js"></script>
        <script>
            var patientData = {
                name: "<?php echo $record['patientname'] ?>",
                age: "<?php echo $record['age'] ?>",
                doctor: "Dr. <?php echo $record['doctorname'] ?>",
                visitDate: "<?php echo $record['date'] ?>",
                complaints: <?php echo json_encode(explode(',', $record['presentingcomplaints'])); ?>,
                treatments: <?php echo json_encode(explode(',', $record['treatments'])); ?>
            };
            var currentDate = new Date();
            var formattedDate = currentDate.getDate() + '/' + (currentDate.getMonth() + 1) + '/' + currentDate.getFullYear();
            var pageNumber = 0;

            function createContainer(addPatientInfo, addComplaintList, addRecommended, patientData) {
                pageNumber++;
                var container = document.createElement('div');
                container.classList.add('mycontainer', 'printable');

                // Create header
                var header = document.createElement('div');
                header.classList.add('header');
                header.innerHTML = `
            <div class="header-box">
                <p class="title">Dental Surgery Center</p>
                <p class="title-branch">Pelawatta</p>
                <p class="title-address">Address :966/F,Pannipitiya Rd, Pelawatta. | Tel : 072 2330455</p>
            </div>
            <p class="branch" style="font-size: 16pt;">Medical Record</p>
            <p class="branch">Branch - Pelawatta</p>
            <div class="divider"></div>
        `;
                container.appendChild(header);

                // Create body
                var body = document.createElement('div');
                body.classList.add('body');
                temp = 'page' + pageNumber;
                body.classList.add(temp);
                container.appendChild(body);

                if (addPatientInfo) {
                    body.innerHTML += `
                <p class="PName"><span>Patient Name</span>: ${patientData.name} </p>
                <p class="PAge"><span>Age</span>: ${patientData.age}</p>
                <p class="PDoctor"><span>Attending physician</span>: ${patientData.doctor}</p>
                <p class="PVDate"><span>Visited Date</span>: ${patientData.visitDate}</p>                
            `;
                }
                if (addComplaintList) {
                    body.innerHTML += `
                    <p class="bold margin-top">Presenting Complaints</p>
                    <p>
                    <ul class="li-complaints" id="complaintsList">
                    </ul>
                    </p>
                `;
                }
                if (addRecommended) {
                    body.innerHTML += `
                    <p class="bold margin-top">Treatments Recommended
                    <ul class="li-treaments" id="treatmentsList">
                    </ul>
                    </p>
                `;
                }

                // Create footer
                var footer = document.createElement('div');
                footer.classList.add('footer');
                footer.innerHTML = `
            <div class="footer-divider divider"></div>
            <p class="footer-date">${formattedDate}</p>
            <p class="pageNumber">${pageNumber}</p>
        `;
                container.appendChild(footer);

                document.body.appendChild(container);

                return body;
            }


            var currentPage = createContainer(true, true, false, patientData);
            document.addEventListener('DOMContentLoaded', function() {
                patientData.complaints.forEach(function(complaint) {
                    if (document.body.querySelector('.' + temp).scrollHeight > document.body.querySelector('.' + temp).clientHeight) {
                        console.log("is overflowing");
                        currentPage = createContainer(false, true, false, patientData);
                    }
                    var complaintsList = currentPage.querySelector('.li-complaints');
                    var li = document.createElement('li');
                    li.textContent = complaint;
                    complaintsList.appendChild(li);
                });
                var tempElement = document.body.querySelector('.' + temp);
                var computedMaxHeight = window.getComputedStyle(tempElement, null).getPropertyValue("max-height");

                var currentHeight = tempElement.getBoundingClientRect().height;
                if (parseFloat(computedMaxHeight) - currentHeight > 250) {
                    var paragraph = document.createElement('p');
                    paragraph.classList.add('margin-top');
                    var boldText = document.createElement('span');
                    boldText.classList.add('bold');
                    boldText.textContent = 'Treatments Recommended';
                    paragraph.appendChild(boldText);
                    var ulElement = document.createElement('ul');
                    ulElement.classList.add('li-treaments');
                    paragraph.appendChild(ulElement);
                    currentPage.appendChild(paragraph);
                } else {
                    currentPage = createContainer(false, false, true, patientData);
                }

                patientData.treatments.forEach(function(treatment) {
                    if (document.body.querySelector('.' + temp).scrollHeight > document.body.querySelector('.' + temp).clientHeight) {
                        console.log("is overflowing");
                        currentPage = createContainer(false, false, true, patientData);
                    }
                    var treatmentList = currentPage.querySelector('.li-treaments');
                    var li = document.createElement('li');
                    li.textContent = treatment;
                    treatmentList.appendChild(li);
                });
            });
            console.log(document.body.querySelector('.page1').clientHeight);
        </script>
    </body>


    </html>
<?php endif; ?>