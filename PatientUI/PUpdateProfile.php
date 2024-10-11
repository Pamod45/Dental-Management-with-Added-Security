<?php

require("../config/dbconnection.php");
session_start();
$patientid = $_SESSION['patientid'];

if (isset($_POST['password'])) {
    $userid = $_SESSION['userid'];
    $newPassword = $_POST['password'];
    $updateQuery = "UPDATE user SET password = '$newPassword' WHERE userid = '$userid'";
    if ($con->query($updateQuery) === TRUE) {
        echo json_encode(array("message" => "Password updated successfully"));
    } else {
        echo json_encode(array("error" => "Error occurred while updating password: " . $con->error));
    }
    exit;
}

if (isset($_POST['updateprofile'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $contactno = $_POST['contactno'];
    $address = $_POST['address'];
    $updateQuery = "UPDATE patient 
                    SET firstname = '$firstname', 
                        lastname = '$lastname', 
                        dob = '$dob', 
                        email = '$email', 
                        contactno = '$contactno',
                        address='$address' 
                    WHERE patientid = '$patientid'";

    if ($con->query($updateQuery) === TRUE) {
        echo json_encode(array("message" => "Profile updated successfully"));
    } else {
        echo json_encode(array("error" => "Error occurred while updating profile."));
    }
    exit;
}


$query5 = "SELECT *,(select password from user where userid=p.userid) as password
FROM patient p WHERE patientid = '$patientid'";
$result5 = $con->query($query5);
$row5 = $result5->fetch_assoc();

?>

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
        if (oldPassword !== '<?php echo $row5["password"]; ?>') {
            Swal.fire({
                title: 'Error',
                text: 'Old password does not match.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
        if (newPassword.length !== 8) {
            // Display an error message if new password is less than eight characters long
            Swal.fire({
                title: 'Error',
                text: 'New password must be eight characters long.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return; // Exit the function
        }
        if (newPassword === oldPassword) {
            Swal.fire({
                title: 'Error',
                text: 'New password cannot be the same as old password.',
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
                password: newPassword
            },
            success: function(response) {
                console.log(response);
                Swal.fire({
                    title: 'Success',
                    text: 'Password changed successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    $('#editModal').modal('hide');
                    window.location.reload();

                });
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

        if (!/^[a-zA-Z]+$/.test(firstname) || firstname === '') {
            Swal.fire({
                title: 'Error',
                text: 'First name is invalid or empty.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!/^[a-zA-Z]+$/.test(lastname) || lastname === '') {
            Swal.fire({
                title: 'Error',
                text: 'Last name is invalid or empty.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (address === '') {
            Swal.fire({
                title: 'Error',
                text: 'Address cannot be empty.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (!/^\d{10,12}$/.test(contactno)) {
            Swal.fire({
                title: 'Error',
                text: 'Contact number must be between 10 digits.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            Swal.fire({
                title: 'Error',
                text: 'Invalid email format.',
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
            success: function(response) {
                // Handle success response
                console.log(response);
                Swal.fire({
                    title: 'Success',
                    text: 'Profile updated successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.reload();
                });
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error('Error occurred while updating profile:', error);
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