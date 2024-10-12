<?php
if (!session_id()) session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css" type="text/css">
    <link rel="icon" href="/images_new/favicon.png">
    <style>
        .was-validated .form-check-input:valid:checked {
            border-color: #007bff;
            /* Blue color */
            background-color: #007bff;
            /* Blue color */
        }
    </style>
    <?php include("../config/includes.php"); ?>
    <title>Login</title>
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <div class="row border rounded-5 p-3 bg-white shadow box-area">
            <!-- left box -->
            <div style="background: #0f376b;" class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box">
                <div class="featured-image mb-3">
                    <img src="/images_new/login.jpg" class="img-fluid rounded-2 backimage" width="250px" alt="login image">
                </div>
                <p class="text-white fs-2" style="font-weight: 600; letter-spacing: 2px;">Be
                    Verified</p>
                <p class="text-white text-wrap text-center" style="width:  17rem; ">Login in to PWS dental to access
                    online features</p>
            </div>
            <!-- right box -->
            <div class="col-md-6  right-box">
                <div class="row align-items-center">
                    <form class="was-validated" novalidate>
                        <div class="header-text mb-4">
                            <h2>Welcome back !</h2>
                            <p>We are happy to have you back</p>
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" id="username" class="form-control form-control-lg bg-light fs-6" name="txtusername" placeholder="Username" maxlength="5" minlength="5" pattern="^[A-Z][0-9]{4}$" required><!-- pattern="^\S+@\S+\.\S+$" -->
                            <div class="invalid-feedback" id="username-error">Please Enter your username</div>
                        </div>
                        <div class="input-group mb-3 rounded-3">
                            <input type="password" id="password" class="form-control form-control-lg bg-light fs-6" name="txtpassword" placeholder="Password" minlength="8" maxlength="8" required pattern="^[a-zA-Z0-9]{8}$"><!-- required pattern="^[a-zA-Z0-9]{8}$" -->
                            <div class="invalid-feedback" id="password-error">Please Enter the passsword</div>
                        </div>

                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <div class="captcha"></div>

                        <div class="input-group mb-3">
                            <button class="btn btn-lg w-100 fs-6" id="btnlogin" name="login">login</button>
                        </div>
                        <div id="lockout-message" class="text-center lock-message"></div>
                        <div class="row text-center fs-6">
                            <span style="font-size: small;">Not registered?<span>
                                    <span style="font-size: small;"><a href="signup.php">Sign Up</a></span>
                        </div>
                        <div class="row text-center fs-6 mt-2">
                            <span style="font-size: small;">Go to home page :<span>
                                    <span style="font-size: small;">
                                        <a href="home.html">Home</a>
                                    </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script>
    var validusername = false;
    var validpassword = false;
    var accountLocked = false;
    let lockoutTimer;

    function isValidInput() {
        if (validusername && validpassword && grecaptcha.getResponse().length > 0) {
            return true;
        }
        let text = "";
        if (!validusername) {
            text = $('#username-error').text();
        } else if (!validpassword) {
            text = $('#password-error').text();
        } else {
            text = "Please confirm you are not a robot.";
        }
        Swal.fire({
            title: 'Error',
            text: text,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return false;
    }

    // Function to check and toggle the button state
    function checkButtonState() {
        const loginButton = document.getElementById('btnlogin');
        loginButton.disabled = !(validusername && validpassword && grecaptcha.getResponse().length && !accountLocked); // Disable button if either is false
    }

    $(document).ready(function() {
        $('#username').on("input", function() {
            var username = $(this).val();
            if (username.length == 0) {
                $('#username-error').text("Please fill out the username field");
                validusername = false;
            } else if (!(/^[A-Z][0-9]{4}$/.test(username))) {
                $('#username-error').text("Please fill out a valid username.");
                validusername = false;
            } else {
                $('#username-error').text("");
                validusername = true;
            }
            checkButtonState(); // Check button state after validating username
        });
    });

    $('#password').on("input", function() {
        if ($(this).val().length == 0) {
            $('#password-error').text("Please fill out the password field");
            validpassword = false;
        } else if (!(/^[a-zA-Z0-9]{8}$/.test($(this).val()))) {
            $('#password-error').text("Please enter a valid password (must contain 8 characters)");
            validpassword = false;
        } else if ($(this).val().length > 8) {
            $('#password-error').text("The password number can only have 8 characters.");
            validpassword = false;
        } else {
            $('#password-error').text("");
            validpassword = true;
        }
        checkButtonState(); // Check button state after validating password
    });

    // Flag to track if reCAPTCHA has been rendered
    let isRecaptchaRendered = false;

    // Function to be called on successful reCAPTCHA completion
    function onRecaptchaSuccess() {
        checkButtonState(); // Check button state after reCAPTCHA success
    }

    // Function to be called when reCAPTCHA fails or expires
    function onRecaptchaFailure() {
        document.getElementById('btnlogin').disabled = true; // Disable the login button
    }

    // When the reCAPTCHA is rendered, set up event handlers
    window.onload = function() {
        const recaptchaElement = document.querySelector('.captcha');
        if (recaptchaElement && !isRecaptchaRendered) {
            grecaptcha.render(recaptchaElement, {
                'sitekey': '6LekfF8qAAAAABkon6_TgQ282coDEkVPWccpwi3I',
                'callback': onRecaptchaSuccess,
                'expired-callback': onRecaptchaFailure
            });
            isRecaptchaRendered = true; // Set the flag to true after rendering
        }
    };
    $('#btnlogin').on('click', function() {
        event.preventDefault();
        if (isValidInput()) {
            var uname = $('#username').val();
            var pas = $('#password').val();
            $.ajax({
                type: 'POST',
                url: 'processLogin.php',
                data: {
                    txtusername: uname,
                    txtpassword: pas,
                    'g-recaptcha-response': grecaptcha.getResponse()
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        window.location.href = result.redirectUrl; // Redirect to the appropriate dashboard
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: result.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        grecaptcha.reset();
                        $('#btnlogin').prop('disabled', true); // Disable the login button
                        displayLockoutMessage(30); // Display lockout message
                        accountLocked = true;
                        setTimeout(() => {
                            accountLocked = false;
                            checkButtonState(); // Re-enable after lockout period
                        }, 30000); // Lockout time in milliseconds (30 seconds)
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'An error occurred while processing your request. Please try again later.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    grecaptcha.reset();
                }
            });
        }
    });

    document.getElementById('btnlogin').disabled = true; // Disable the login button by default



    // Function to display lockout message
    function displayLockoutMessage(lockoutDuration) {
        const lockoutMessage = document.getElementById('lockout-message');
        lockoutMessage.style.display = 'flex'; // Show the message

        // Clear previous timer if any
        clearInterval(lockoutTimer);

        // Set the remaining time
        let remainingTime = lockoutDuration;

        // Update message and set timer
        lockoutMessage.innerText = `Your account is locked. Please try again in ${remainingTime} seconds.`;

        lockoutTimer = setInterval(function() {
            remainingTime--;

            if (remainingTime <= 0) {
                clearInterval(lockoutTimer);
                lockoutMessage.style.display = 'none'; // Hide the message after time is up
                // Optionally, you can re-enable the login button or re-enable login attempts here
            } else {
                lockoutMessage.innerText = `Your account is locked. Please try again in ${remainingTime} seconds.`;
            }
        }, 1000); // Update every second
    }
</script>

</html>