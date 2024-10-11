<?php
require("../config/dbconnection.php");
session_start();
$empid = $_SESSION['employeeid'];
$branchid = $_SESSION['branchid'];

if (isset($_POST['nextid'])) {
    $query = " SELECT CONCAT('EX', LPAD((SUBSTRING(MAX(expenseid), 3) + 1), 4, '0'))
     AS next_expense_id FROM expense;";
    $result = $con->query($query);
    $data = $result->fetch_assoc();
    echo json_encode($data);
    exit;
}

if (isset($_POST['expenseid'])) {
    $expenseid = $_POST['expenseid'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $query = "INSERT INTO expense (expenseid, date, description, amount,branchid) 
              VALUES ('$expenseid', '$date', '$description', '$amount','$branchid')";

    $result = $con->query($query);
    if ($result) {
        echo "Expense added";
    } else {
        echo "Error: Failed to add expense.";
    }
    exit;
}


$query2 =  "SELECT expenseid,amount,date,description
FROM pdms.expense where branchid='B0001' order by date desc;";
$result2 = $con->query($query2);

$expenses = array();

while ($row2 = $result2->fetch_assoc()) {
    $expenses[] = $row2;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Expenses</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="expenses.css">
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
        <p class="">Branch Expenses</p>
        <button class="btn btn-primary addexpense" id="addexpense">Add Expense</button>
    </div>
    <div class="bottom">
        <input name="" id="searchValue" class="form-control" type="text" placeholder="eg :2024/05/02"></input>
        <input name="" id="searchValue2" class="form-control" type="text" placeholder="eg :water bill"></input>
        <table class="table table-responsive table-dark table-striped table-hover rounded ">
            <thead class="text-center">
                <tr>
                    <th class="small">Expense ID</th>
                    <th class="large">Date</th>
                    <th class="xlarge">Description</th>
                    <th class="large">Amount</th>
                </tr>
            </thead>
            <tbody class="text-center">
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="background-color: #D9D9D9; width: 600px;">
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="editModalLabel">Add Expense</h5>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <div class="form-group">
                            <label id="idlabel">Expense ID:</label>
                            <input type="text" class="form-control" id="expenseID" readonly>
                        </div>
                        <div class="form-group">
                            <label>Date:</label>
                            <input type="text" class="form-control" id="date" readonly>
                        </div>
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea type="text" rows="7" class="form-control" id="Description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Amount:</label>
                            <input type="text" class="form-control" id="Amount">
                        </div>
                    </form>
                </div>
                <div class="footer">
                    <button type="button" class="btn btn-primary" id="addExpensetodb">Add Expense</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close">Close</button>
                </div>
            </div>
        </div>
    </div>

</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
    var branchexpenses = <?php echo json_encode($expenses) ?>;
    document.addEventListener("DOMContentLoaded", function() {
        var searchValue = document.getElementById("searchValue");
        var searchValue2 = document.getElementById("searchValue2");

        var tbody = document.querySelector(".table tbody");

        function fillTable(data) {
            tbody.innerHTML = "";
            // Loop through the data and create table rows
            data.forEach(function(item) {
                var row = document.createElement("tr");

                row.innerHTML = `
                    <td class="small">${item.expenseid}</td>
                    <td class="large">${item.date}</td>
                    <td class="xlarge">${item.description==null ? "" : item.description}</td>
                    <td class="large">${item.amount}</td>
                `;
                tbody.appendChild(row);
            });
        }
        fillTable(branchexpenses);


        // Add an event listener to capture changes in the input value
        searchValue2.addEventListener("input", function() {
            // Get the value entered by the user
            var inputValue = searchValue2.value.trim().toLowerCase();
            var inputValue2 = searchValue.value.trim().toLowerCase();
            // Get all the rows in the table body
            var tableRows = document.querySelectorAll(".table tbody tr");

            // Loop through each row and hide rows that don't match the search value
            tableRows.forEach(function(row) {
                var cellContent = row.cells[2].textContent.toLowerCase();
                var cellcontent2 = row.cells[1].textContent.toLowerCase();
                // Check if the cell content includes the search value
                if (cellContent.includes(inputValue) && cellcontent2.includes(inputValue2)) {
                    // Show the row if it matches the search value
                    row.style.display = "";
                } else {
                    // Hide the row if it doesn't match the search value
                    row.style.display = "none";
                }
            });
        });

        // Add an event listener to capture changes in the input value
        searchValue.addEventListener("input", function() {
            // Get the value entered by the user
            var inputValue = searchValue.value.trim().toLowerCase();
            var inputValue2 = searchValue2.value.trim().toLowerCase();
            // Get selected criteria
            var tableRows = document.querySelectorAll(".table tbody tr");

            // Loop through each row and hide rows that don't match the search value
            tableRows.forEach(function(row) {
                // Get the cell content based on the selected criteria
                var cellContent = row.cells[1].textContent.toLowerCase();
                var cellcontent2 = row.cells[2].textContent.toLowerCase();

                // Check if the cell content includes the search value
                if (cellContent.includes(inputValue) && cellcontent2.includes(inputValue2)) {
                    // Show the row if it matches the search value
                    row.style.display = "";
                } else {
                    // Hide the row if it doesn't match the search value
                    row.style.display = "none";
                }
            });
        });

        var addexpense = document.getElementById("addexpense");
        addexpense.addEventListener("click", function() {
            var currentDate = new Date();
            var year = currentDate.getFullYear();
            var month = ("0" + (currentDate.getMonth() + 1)).slice(-2);
            var day = ("0" + currentDate.getDate()).slice(-2);
            var formattedDate = year + "/" + month + "/" + day;

            $.ajax({
                type: 'POST',
                url: 'Expenses.php',
                data: {
                    nextid: true
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    var eid = document.getElementById("expenseID");
                    eid.value = data.next_expense_id; // Assign the next expense ID to expenseID element
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred while fetching data:', error);
                }
            });

            var date = document.getElementById("date");
            date.value = formattedDate;
            $('#editModal').modal('show');
        });


        var addExpensetodbbtn = document.getElementById("addExpensetodb");
        addExpensetodbbtn.addEventListener("click", function() {
            var date = document.getElementById("date").value;
            var expenseid = document.getElementById("expenseID").value;
            var description = document.getElementById("Description").value;
            var amount = document.getElementById("Amount").value;
            $.ajax({
                type: 'POST',
                url: 'Expenses.php',
                data: {
                    expenseid: expenseid,
                    date: date,
                    description: description,
                    amount: amount
                },
                success: function(response) {
                    if (response == "Expense added") {
                        Swal.fire({
                            icon: 'success',
                            title: 'Expense added successfully',
                            showConfirmButton: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#editModal').modal('hide');
                                window.location.reload();
                            }
                        });

                    }
                    $('#editModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred while fetching data:', error);
                }
            });


        });

        var closeButton = document.getElementById("close");
        closeButton.addEventListener("click", function() {
            $('#editModal').modal('hide');
        });
    });
</script>

</html>