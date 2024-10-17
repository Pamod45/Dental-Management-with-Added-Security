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

$loadProfileUpdateUI = false;
$logger = createLogger('patient_dashboard.log');
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
    $patientid = $_SESSION['patientid'];

    if (isset($_POST['newPassword'])) {
        $userid = $_SESSION['userid'];
        $oldPassword = $_POST['oldPassword'];
        $newPassword = $_POST['newPassword'];

        // Validate old password format
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$/', $oldPassword)) {
            echo json_encode(array("success" => false, "message" => "Error: Old password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one special character, and one number."));
            exit;
        }

        // Validate new password format
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$/', $newPassword)) {
            echo json_encode(array("success" => false, "message" => "Error: New password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one special character, and one number."));
            exit;
        }

        // Check if the old and new passwords are the same
        if ($oldPassword == $newPassword) {
            echo json_encode(array("success" => false, "message" => "Error: New password cannot be the same as the old password."));
            exit;
        }

        // Fetch the existing password from the database
        $stmt = $con->prepare("SELECT password FROM user WHERE userid = ?");
        if (!$stmt) {
            echo json_encode(array("success" => false, "message" => "Error: Failed to prepare SQL statement."));
            exit;
        }
        $stmt->bind_param("s", $userid);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                echo json_encode(array("success" => false, "message" => "Error: User not found."));
                exit;
            }

            $row = $result->fetch_assoc();
            // Verify the old password
            if (!password_verify($oldPassword, $row['password'])) {
                echo json_encode(array("success" => false, "message" => "Error: Old password is incorrect."));
                exit;
            }

            // Hash the new password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $con->prepare("UPDATE user SET password = ? WHERE userid = ?");
            if (!$stmt) {
                echo json_encode(array("success" => false, "message" => "Error: Failed to prepare SQL statement for updating password."));
                exit;
            }
            $stmt->bind_param("ss", $hashedNewPassword, $userid);
            if ($stmt->execute()) {
                echo json_encode(array("success" => true, "message" => "Password updated successfully."));
            } else {
                echo json_encode(array("success" => false, "message" => "Error occurred while updating password."));
            }
            exit;
        } else {
            echo json_encode(array("success" => false, "message" => "Error: Failed to execute SQL statement."));
            exit;
        }
    }

    if (isset($_POST['updateprofile'])) {
        $firstname = htmlspecialchars($_POST['firstname'], ENT_QUOTES, 'UTF-8');
        $lastname = htmlspecialchars($_POST['lastname'], ENT_QUOTES, 'UTF-8');
        $dob = htmlspecialchars($_POST['dob'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $contactno = htmlspecialchars($_POST['contactno'], ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');

        if (!preg_match("/^[a-zA-Z]{1,30}$/", $firstname)) {
            echo json_encode(array("success" => false, "message" => "Invalid first name."));
            exit;
        }
        if (!preg_match("/^[a-zA-Z]{1,30}$/", $lastname)) {
            echo json_encode(array("success" => false, "message" => "Invalid last name."));
            exit;
        }
        if (strlen($address) > 100) {
            echo json_encode(array("success" => false, "message" => "Address should be less than 100 characters."));
            exit;
        }
        if (!preg_match("/^\+94\d{9}$|^0\d{9}$/", $contactno)) {
            echo json_encode(array("success" => false, "message" => "Invalid contact number."));
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array("success" => false, "message" => "Invalid email format."));
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array("success" => false, "message" => "Invalid email format."));
            exit;
        }
        if (!DateTime::createFromFormat('Y-m-d', $dob)) {
            echo json_encode(array("success" => false, "message" => "Invalid date format. Use YYYY-MM-DD."));
            exit;
        }

        $currentDate = new DateTime();
        $dobDate = new DateTime($dob);

        if ($dobDate > $currentDate) {
            echo json_encode(array("success" => false, "message" => "Date of birth cannot be in the future."));
            exit;
        }
        $age = $currentDate->diff($dobDate)->y;
        if ($age <= 14) {
            echo json_encode(array("success" => false, "message" => "You must be older than 14 years."));
            exit;
        }

        $stmt = $con->prepare(
            "UPDATE patient 
             SET firstname = ?, 
                 lastname = ?, 
                 dob = ?, 
                 email = ?, 
                 contactno = ?, 
                 address = ? 
             WHERE patientid = ?"
        );
        $stmt->bind_param("sssssss", $firstname, $lastname, $dob, $email, $contactno, $address, $patientid);

        if ($stmt->execute()) {
            echo json_encode(array("success" => true, "message" => "Profile updated successfully"));
        } else {
            echo json_encode(array("success" => false, "message" => "Error occurred while updating profile: "));
        }
        $stmt->close();
        exit;
    }


    $stmt = $con->prepare(
        "SELECT p.*, (SELECT password FROM user WHERE userid = p.userid) AS password 
         FROM patient p 
         WHERE p.patientid = ?"
    );

    $stmt->bind_param("s", $patientid);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row5 = $result->fetch_assoc();
    } else {
        throw new Exception("Error occurred while fetching patient data: " . $stmt->error, 500);
    }
    $loadProfileUpdateUI = true;
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


if ($loadProfileUpdateUI) : ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Profile</title>
        <link rel="stylesheet" href="sidebar.css">
        <link rel="stylesheet" href="PUpdateProfile.css">
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
            <p>My Profile</p>
        </div>
        <div class="bottom">
            <div class="mycontainer">
                <div class="form-group">
                    <label>First Name</label>
                    <div>
                        <input type="text" class="form-control" id="firstname" value="<?php echo $row5['firstname']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <div>
                        <input type="text" class="form-control" id="lastname" value="<?php echo $row5['lastname']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" id="email" value="<?php echo $row5['email']; ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" class="form-control" id="address" value="<?php echo $row5['address']; ?>">
                </div>
                <div class="form-group">
                    <label>Date of birth</label>
                    <input type="date" class="form-control" id="dob" value="<?php echo $row5['dob']; ?>">
                </div>
                <div class="form-group">
                    <label>Contact No</label>
                    <input type="text" class="form-control" id="contact" value="<?php echo $row5['contactno']; ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <Button class="btn btn-primary" id="updatePassword" style="width: 250px; background-color:grey!important;">Change Password</Button>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary text-center" id="updateProfile">Update Profile</button>
                </div>

            </div>
        </div>


        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="background-color: #D9D9D9; width: 600px;">
                    <div class="modal-header">
                        <h5 class="modal-title text-center" id="editModalLabel">Chnage Password</h5>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <div class="form-group">
                                <label for="oldpassword">Old Password:</label>
                                <input type="text" class="form-control" id="oldpassword">
                            </div>
                            <div class="form-group">
                                <label for="newpassword">New Password:</label>
                                <input type="text" class="form-control" id="newpassword">
                            </div>
                            <div class="form-group">
                                <label for="confirmpassword">Confirm Password:</label>
                                <input type="text" class="form-control" id="confirmpassword">
                            </div>
                        </form>
                    </div>
                    <div class="footer">
                        <button type="button" class="btn btn-primary" id="changepassword">Change Password</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close">Close</button>
                    </div>
                </div>
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

        $('#updatePassword').click(function() {
            $('#editModal').modal('show');
        });

        $('#close').click(function() {
            $('#editModal').modal('hide');
        });

        $('#changepassword').click(function() {
            var oldPassword = $('#oldpassword').val();
            var newPassword = $('#newpassword').val();
            var confirmPassword = $('#confirmpassword').val();
            if (newPassword === oldPassword) {
                Swal.fire({
                    title: 'Error',
                    text: 'New password cannot be the same as old password.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            if (newPassword && !/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$/.test(newPassword)) {
                Swal.fire({
                    title: 'Error',
                    text: 'Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one special character, and one number.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    title: 'Error',
                    text: 'New password and confirm password do not match.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            $.ajax({
                type: 'POST',
                url: 'PUpdateProfile.php',
                data: {
                    oldPassword: oldPassword,
                    newPassword: newPassword
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Password changed successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            $('#editModal').modal('hide');
                            window.location.reload();

                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred while changing password:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to change password. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $('#updateProfile').click(function() {
            var firstname = $('#firstname').val().trim();
            var lastname = $('#lastname').val().trim();
            var dob = $('#dob').val().trim();
            var email = $('#email').val().trim();
            var contactno = $('#contact').val().trim();
            var address = $('#address').val().trim();

            if (!/^[a-zA-Z]{1,30}$/.test(firstname)) {
                Swal.fire({
                    title: 'Error',
                    text: 'Invalid first name. Must contain only letters and be one word not greater than 30 characters.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (!/^[a-zA-Z]{1,30}$/.test(lastname)) {
                Swal.fire({
                    title: 'Error',
                    text: 'Invalid last name. Must contain only letters and be one word not greater than 30 characters.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }


            if (address.length > 100) {
                Swal.fire({
                    title: 'Error',
                    text: 'Address should be less than 100 characters.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            if (!/^\+94\d{9}$|^0\d{9}$/.test(contactno)) {
                Swal.fire({
                    title: 'Error',
                    text: 'Invalid contact number. It should be in the format +94XXXXXXXXX or 0XXXXXXXXX.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                Swal.fire({
                    title: 'Error',
                    text: 'Invalid email address.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }
            var currentDate = new Date();
            var selectedDate = new Date(dob);
            if (selectedDate >= currentDate) {
                Swal.fire({
                    title: 'Error',
                    text: 'Date of birth must be before today.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            var age = currentDate.getFullYear() - selectedDate.getFullYear();
            var m = currentDate.getMonth() - selectedDate.getMonth();
            if (m < 0 || (m === 0 && currentDate.getDate() < selectedDate.getDate())) {
                age--;
            }
            if (age < 14) {
                Swal.fire({
                    title: 'Error',
                    text: 'You must be older than 14 years.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'PUpdateProfile.php',
                data: {
                    updateprofile: true,
                    firstname: firstname,
                    lastname: lastname,
                    dob: dob,
                    email: email,
                    contactno: contactno,
                    address: address
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success',
                            text: 'Profile updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }

                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to update profile. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>

    </html>

<?php endif; ?>