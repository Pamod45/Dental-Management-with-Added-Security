<?php
require("../config/dbconnection.php");
if (isset($_POST['register'])) {

    $con->begin_transaction();

    try {

        $query_userid = "SELECT MAX(userid) as max_userid FROM user";
        $result_userid = $con->query($query_userid);
        $row_userid = $result_userid->fetch_assoc();
        $max_userid = $row_userid['max_userid'];

        if($max_userid==NULL)
            $next_userid = 'U0001';
        else
            $next_userid = 'U' . sprintf('%04d', substr($max_userid, 1) + 1);

        $password = $con->real_escape_string($_POST['password']);
        $usertype = 'Patient';
        $registereddate = date('Y-m-d');
        $loginstatus = 0;

        $insert_query_user = "INSERT INTO user (userid, password, usertype, registereddate, loginstatus) 
                             VALUES ('$next_userid', '$password', '$usertype', '$registereddate', $loginstatus)";


        if (!$con->query($insert_query_user)) {
            throw new Exception("Error while inserting");
        }


        $query_patientid = "SELECT MAX(patientid) as max_patientid FROM patient";
        $result_patientid = $con->query($query_patientid);
        $row_patientid = $result_patientid->fetch_assoc();
        $max_patientid = $row_patientid['max_patientid'];

        if($max_patientid==NULL)
            $next_patientid = 'P0001';
        else
            $next_patientid = 'P' . sprintf('%04d', substr($max_patientid, 1) + 1);

        $dob = $_POST['dob'];
        $firstname = $_POST['fname'];
        $lastname = $_POST['lname'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $contactno = $_POST['contact'];

        $insert_query_patient = "INSERT INTO patient (userid, patientid, dob, firstname, lastname, email, address, contactno) 
                                 VALUES ('$next_userid', '$next_patientid', '$dob', '$firstname', '$lastname', '$email', '$address', '$contactno')";

        // Execute the INSERT query for patient table
        if (!$con->query($insert_query_patient))
            throw new Exception("Error while inserting");

        // If all steps are successful, commit the transaction
        $con->commit();
        echo json_encode($next_userid);
    } catch (Exception $e) {
        // If any step fails, roll back the transaction
        $con->rollback();
        echo "Registration Failed: " . $e->getMessage();
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
        <div class="title">
            Sign Up
        </div>
        <div class="description">
            Create your own account
        </div>


        <div class="cont">
            <div class="myrow">
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="fname" placeholder="firstname">
                </div>
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="lname" placeholder="lastname">
                </div>
            </div>
            <div class="myrow">
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="contact" placeholder="phone number">
                </div>
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="email" placeholder="email">
                </div>
            </div>

            <div class="myrow">
                <div class="mb-3 child">
                    <input type="text" class="form-control" onfocus="(this.type='date')" onblur="(this.type='text')" id="dob" placeholder="date of birth">
                </div>
                <div class="mb-3 child">
                    <input type="text" class="form-control" id="address" placeholder="address">
                </div>
            </div>

            <div class="myrow">
                <div class="mb-3 child">
                    <input type="password" class="form-control" id="password" placeholder="password">
                </div>
                <div class="mb-3 child">
                    <input type="password" class="form-control" id="confirmpassword" placeholder="confirm password">
                </div>
            </div>
            <div class="myrow">
                <button class="childspan btn btn-primary" id="registerButton">Register</button>
            </div>
            <div class="myrow">
                <div class="childspan text">
                    Already have an account ? <a href="login.php">Login</a>
                </div>
                </divclass>

            </div>

        </div>

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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

            // Basic verification
            if (fname.trim() === '' || !/^[a-zA-Z]+$/.test(fname)) {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter a valid first name.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (lname.trim() === '' || !/^[a-zA-Z]+$/.test(lname)) {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter a valid last name.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (contact.trim() === '' || !/^\d{10,12}$/.test(contact)) {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter a valid contact number (10 digits).",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (email.trim() === '' || !isValidEmail(email)) {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter a valid email address.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (dob.trim() === '') {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter your date of birth.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (address.trim() === '') {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter your address.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (password.trim() === '') {
                Swal.fire({
                    title: "Error!",
                    text: "Please enter a password.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            if (password !== confirmpassword) {
                Swal.fire({
                    title: "Error!",
                    text: "Passwords and confirm password do not match.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                return;
            }

            $.ajax({
                url: "signup.php", // Replace with your backend endpoint
                type: "POST",
                data: {
                    register: true,
                    fname: fname,
                    lname: lname,
                    contact: contact,
                    email: email,
                    dob: dob,
                    address: address,
                    password: password
                },
                success: function(response) {
                    if (!response.includes("Failed")) {
                        Swal.fire({
                            title: "Registration Successful!",
                            text: `You have successfully registered.Your user id is ${response}. Use this to log in next time`,
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(function() {
                            window.location.href = "login.php";
                        });
                    } else {
                        Swal.fire({
                            title: "Registration Failed!",
                            text: response,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: "Error!",
                        text: "An error occurred while registering. Please try again later.",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                }
            });
        });
    });


    function isValidEmail(email) {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }
</script>

</html>