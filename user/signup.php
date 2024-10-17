<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');

require("../config/guestDBConnection.php");
require("../vendor/autoload.php");
require('../config/logger.php');

$logger = createLogger('signup.log');

if (isset($_POST['register'])) {
    $con = getDatabaseConnection();
    if (!$con) {
        echo json_encode(['success' => false, 'message' => 'Failed to connect to database.']);
        exit;
    }
    $con->begin_transaction();

    try {
        if (!$logger) {
            throw new Exception('Failed to create logger instance.', 500);
        }

        $recaptchaSecret = '6LekfF8qAAAAAJbchVG_vTMi2m__06WZFgNRZmeu';
        $recaptchaResponse = $_POST['g-recaptcha-response'];

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
        $responseKeys = json_decode($response, true);

        if (intval($responseKeys["success"]) !== 1) {
            echo json_encode(['success' => false, 'message' => 'Please confirm you are not a robot.']);
            exit();
        }

        $password = $con->real_escape_string($_POST['password']);
        $confirmPassword = $con->real_escape_string($_POST['confirm_password']); // Capture the confirm password
        $usertype = 'Patient';
        $registereddate = date('Y-m-d');
        $loginstatus = 0;

        if ($password !== $confirmPassword) {
            throw new Exception("Passwords do not match.");
        }

        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$/', $password)) {
            throw new Exception("Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one special character, and one number.");
        }

        // Password hashing with salt
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Retrieve max user ID
        $query_userid = "SELECT MAX(userid) as max_userid FROM user";
        $result_userid = $con->query($query_userid);
        $row_userid = $result_userid->fetch_assoc();
        $max_userid = $row_userid['max_userid'];

        // Generate the next user ID
        $next_userid = $max_userid ? 'U' . sprintf('%04d', substr($max_userid, 1) + 1) : 'U0001';

        // Sanitize other input fields
        $dob = $con->real_escape_string($_POST['dob']);
        $firstname = $con->real_escape_string($_POST['fname']);
        $lastname = $con->real_escape_string($_POST['lname']);
        $email = $con->real_escape_string($_POST['email']);
        $address = $con->real_escape_string($_POST['address']);
        $contactno = $con->real_escape_string($_POST['contact']);

        if (!preg_match("/^[a-zA-Z]{1,30}$/", $firstname)) {
            echo json_encode(array("success" => false, "message" => "Invalid first name."));
            exit;
        }
        if (!preg_match("/^[a-zA-Z]{1,30}$/", $lastname)) {
            echo json_encode(array("success" => false, "message" => "Invalid last name."));
            exit;
        }
        if (strlen($address) > 100 || strlen($address) < 1) {
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

        // Prepare the SQL statement for user insertion
        $insert_query_user = $con->prepare("INSERT INTO user (userid, password, usertype, registereddate, loginstatus) 
                                            VALUES (?, ?, ?, ?, ?)");
        $insert_query_user->bind_param('ssssi', $next_userid, $hashedPassword, $usertype, $registereddate, $loginstatus);

        if (!$insert_query_user->execute()) {
            throw new Exception("Error while inserting user.");
        }

        // Retrieve max patient ID
        $query_patientid = "SELECT MAX(patientid) as max_patientid FROM patient";
        $result_patientid = $con->query($query_patientid);
        $row_patientid = $result_patientid->fetch_assoc();
        $max_patientid = $row_patientid['max_patientid'];

        // Generate the next patient ID
        $next_patientid = $max_patientid ? 'P' . sprintf('%04d', substr($max_patientid, 1) + 1) : 'P0001';

        // Prepare the SQL statement for patient insertion
        $insert_query_patient = $con->prepare("INSERT INTO patient (userid, patientid, dob, firstname, lastname, email, address, contactno) 
                                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_query_patient->bind_param('ssssssss', $next_userid, $next_patientid, $dob, $firstname, $lastname, $email, $address, $contactno);

        // Execute the INSERT query for the patient table
        if (!$insert_query_patient->execute()) {
            throw new Exception("Error while inserting patient.");
        }

        // Commit the transaction
        $con->commit();
        if ($logger)
            $logger->info("User $next_userid registered successfully.");
        echo json_encode(['success' => true, 'userid' => $next_userid]);
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $con->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/bootstrap-5.3.2/dist/js/bootstrap.bundle.js"></script>
    <link rel="stylesheet" href="signup.css">
    <link rel="icon" href="/images_new/favicon.png">
    <link rel="stylesheet" href="/bootstrap-5.3.2/dist/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://kit.fontawesome.com/637ae4e7ce.js" crossorigin="anonymous"></script>
    <title>Patient Registration</title>
</head>

<body>
    <div class="mycontainer">
        <div class="title">Sign Up</div>
        <div class="description">Create your own account</div>

        <div class="cont">
            <div class="myrow">
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="fname" placeholder="First Name" required>
                </div>
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="lname" placeholder="Last Name" required>
                </div>
            </div>
            <div class="myrow">
                <div class="mb-3 child">
                    <input type="tel" class="form-control" id="contact" placeholder="Phone Number" required pattern="[0-9]{10}">
                </div>
                <div class="mb-3 child">
                    <input type="email" class="form-control" id="email" placeholder="Email" required>
                </div>
            </div>
            <div class="myrow">
                <div class="mb-3 child">
                    <input type="date" class="form-control" id="dob" required>
                </div>
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="address" placeholder="Address" required>
                </div>
            </div>
            <div class="myrow">
                <div class="mb-3 child">
                    <input type="password" class="form-control" id="password" placeholder="Password" required>
                </div>
                <div class="mb-3 child">
                    <input type="password" class="form-control" id="confirmpassword" placeholder="Confirm Password" required>
                </div>
            </div>

            <div class="myrow">
                <div class="childspan">
                    <div class="g-recaptcha" data-sitekey="6LekfF8qAAAAABkon6_TgQ282coDEkVPWccpwi3I"></div>
                </div>
            </div>

            <div class="myrow">
                <button class="childspan btn btn-primary" id="registerButton">Register</button>
            </div>
            <div class="myrow">
                <div class="childspan text">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        $(document).ready(function() {
            $("#registerButton").click(function() {
                // Get values from input fields
                var fname = $("#fname").val();
                var lname = $("#lname").val();
                var contact = $("#contact").val();
                var email = $("#email").val();
                var dob = $("#dob").val();
                var address = $("#address").val();
                var password = $("#password").val();
                var confirmpassword = $("#confirmpassword").val();
                var captchaResponse = grecaptcha.getResponse(); // Get CAPTCHA response

                if (!/^[a-zA-Z]{1,30}$/.test(fname)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Invalid first name. Must contain only letters and be one word not greater than 30 characters.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (!/^[a-zA-Z]{1,30}$/.test(lname)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Invalid last name. Must contain only letters and be one word not greater than 30 characters.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }


                if (address.length > 100 || address.length < 1) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Address should be less than 100 characters.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                if (!/^\+94\d{9}$|^0\d{9}$/.test(contact)) {
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
                if(!dob){
                    Swal.fire({
                        title: 'Error',
                        text: 'Date of birth is required.',
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
                if (!password || !/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$/.test(password)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one special character, and one number.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                if(password!==confirmpassword){
                    Swal.fire({
                        title: 'Error',
                        text: 'Password and confirm passwords do not match.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                $.ajax({
                    url: "signup.php",
                    method: "POST",
                    data: {
                        fname: fname,
                        lname: lname,
                        contact: contact,
                        email: email,
                        dob: dob,
                        address: address,
                        password: password,
                        confirm_password: confirmpassword, // Corrected variable name
                        'g-recaptcha-response': captchaResponse, // Include reCAPTCHA response
                        register: true
                    },
                    success: function(data) {
                        var response = JSON.parse(data);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Registration Successful',
                                text: 'You can now log in!',
                            }).then(() => {
                                window.location.href = 'login.php'; // Redirect after successful registration
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Registration Failed',
                                text: response.message,
                            });
                            grecaptcha.reset();
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: error,
                        });
                        grecaptcha.reset();
                    }
                });

            });
        });
    </script>
</body>

</html>