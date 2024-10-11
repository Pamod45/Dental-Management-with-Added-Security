<?php
require("../config/dbconnection.php");
session_start();
$empid = $_SESSION['employeeid'];
$branchid = $_SESSION['branchid'];

if (isset($_POST['year']) && isset($_POST['month'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $query = "SELECT appointmentdate, COUNT(*) AS appointment_count
                FROM pdms.appointment ap
                WHERE 
                    (SELECT branchid FROM doctor WHERE doctorid = ap.doctorid) = '$branchid' 
                    AND YEAR(appointmentdate) = '$year' 
                    AND MONTHNAME(appointmentdate) = '$month'
                GROUP BY appointmentdate;";
    $result = $con->query($query);
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}


if (isset($_POST['year'])) {
    $year = $_POST['year'];
    // Query to fetch available months for the selected year
    $query2 = "SELECT DISTINCT MONTHNAME(appointmentdate) AS month
    FROM pdms.appointment as ap
    WHERE (select branchid from doctor 
    where doctorid=ap.doctorid) = '$branchid' 
    AND YEAR(appointmentdate) = '$year'";
    $result2 = $con->query($query2);
    $availableMonths = array();
    while ($row2 = $result2->fetch_assoc()) {
        $availableMonths[] = $row2['month'];
    }
    echo json_encode($availableMonths);
    exit;
}


$query = "SELECT DISTINCT YEAR(appointmentdate) AS year FROM 
pdms.appointment ap where (select 
branchid from doctor where doctorid=ap.doctorid)='B0001'";
$result = $con->query($query);
while ($row = $result->fetch_assoc()) {
    $years[] = $row['year'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Patient Visit Report</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="MonthlyIncomeReport.css">
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
                <a href="AnnualProfitReport.php" class="nav-list-item">
                    <i class="fa-solid fa-book sideBarIcon"></i>
                    <span class="nav-item">Anuual Profits</span>
                </a>
            </li>
            <li>
                <a href="MonthlyIncomeReport.php" class="nav-list-item">
                    <i class="fa-solid fa-hand-holding-dollar sideBarIcon"></i>
                    <span class="nav-item">Monthly Income</span>
                </a>
            </li>
            <li>
                <a href="MonthlyExpenseReport.php" class="nav-list-item">
                    <i class="fa-solid fa-money-bill-trend-up sideBarIcon"></i>
                    <span class="nav-item">Monthly Expense</span>
                </a>
            </li>
            <li>
                <a href="MonthlyPatientVisitReport.php" class="nav-list-item">
                    <i class="fa-solid fa-bed sideBarIcon"></i>
                    <span class="nav-item">Patient Visits</span>
                </a>
            </li>
            <li>
                <a href="Expenses.php" class="nav-list-item">
                    <i class="fa-solid fa-receipt sideBarIcon"></i>
                    <span class="nav-item">Expenses</span>
                </a>
            </li>
            <li>
                <a href="checkInOut.php" class="nav-list-item">
                    <i class="fa-solid fa-users sideBarIcon"></i>
                    <span class="nav-item">check in outs</span>
                </a>
            </li>
            <li>
                <a href="BMEmplyeeRequests.php" class="nav-list-item">
                    <i class="fa-solid fa-clock-rotate-left sideBarIcon"></i>
                    <span class="nav-item">Leave requests</span>
                </a>
            </li>
            <li>
                <a href="MedicineRequests.php" class="nav-list-item">
                    <i class="fa-solid fa-notes-medical sideBarIcon"></i>
                    <span class="nav-item">Medicine Requests</span>
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
        <p>Monthly Patient Visit Report</p>
    </div>
    <div class="bottom">
        <select id="searchCriteria" class="form-select">
        </select>

        <select id="cmonth" class="form-select">
        </select>
        <button class="btn btn-primary viewReport">View Report</button>
        <table class="table table-responsive table-dark table-striped table-hover rounded ">
            <thead class="text-center">
                <tr>
                    <th class="large">Date</th>
                    <th class="xlarge"></th>
                    <th class="large">Count</th>
                </tr>
                </tr>
            </thead>
            <tbody class="text-center">
            </tbody>
        </table>
    </div>


</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
    var currentappointmentdata;
    var years = <?php echo json_encode($years) ?>;

    document.addEventListener("DOMContentLoaded", function() {
        var searchCriteria = document.getElementById("searchCriteria");
        var cmonth = document.getElementById("cmonth");

        years.forEach(function(item) {
            var option = document.createElement("option");
            option.value = item;
            option.textContent = item;
            searchCriteria.appendChild(option);
        });



        function fillTable(data) {
            var tbody = document.querySelector(".table tbody");
            var count = 0;
            // Loop through the data and create table rows
            data.forEach(function(item) {
                var row = document.createElement("tr");

                row.innerHTML = `
                    <td class="large">${item.appointmentdate}</td>
                    <td class="xlarge"></td>
                    <td class="large">${item.appointment_count}</td>
                `;
                count += parseFloat(item.appointment_count);
                tbody.appendChild(row);
            });

            var row = document.createElement("tr");
            row.classList.add("table-success");
            row.innerHTML = `
                    <td class="large fw-bold">Total Count</td>
                    <td class="xlarge fw-bold"></td>
                    <td class="large fw-bold">${count}</td>
                `;
            tbody.appendChild(row);
        }

        searchCriteria.addEventListener("change", function() {
            var selectedValue = searchCriteria.value;
            var monthSelect = document.getElementById("cmonth");
            monthSelect.innerHTML = "";

            var option = document.createElement("option");
            option.value = "Select month";
            option.textContent = "Select month";
            monthSelect.appendChild(option);
            $.ajax({
                type: "POST",
                url: "MonthlyPatientVisitReport.php",
                data: {
                    year: selectedValue
                },
                dataType: "json",
                success: function(response) {
                    response.forEach(function(month) {
                        var option = document.createElement("option");
                        option.value = month;
                        option.textContent = month;
                        monthSelect.appendChild(option);

                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error occurred while fetching data:", error);
                }
            });
            cmonth.dispatchEvent(new Event("change"));
        });

        cmonth.addEventListener("change", function() {
            var selectedMonth = cmonth.value;
            var selectedYear = searchCriteria.value;
            var tableBody = document.querySelector(".table tbody");
            tableBody.innerHTML = "";
            $.ajax({
                type: 'POST',
                url: 'MonthlyPatientVisitReport.php', // Update this with the correct path to your PHP script
                data: {
                    year: selectedYear,
                    month: selectedMonth,
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    currentappointmentdata = data;
                    if (data.length >= 1) {
                        fillTable(data); // Fill the table with the fetched data
                    } else {
                        console.log("No data");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred while fetching data:', error);
                }
            });
        });

        searchCriteria.dispatchEvent(new Event("change"));
    });
</script>

</html>