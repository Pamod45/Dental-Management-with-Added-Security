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
            <!-- Left Box -->
            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #0f376b;">
                <div class="featured-image mb-3">
                    <img src="/images_new/login.jpg" class="img-fluid rounded-2 backimage" width="250px" alt="login image">
                </div>
                <p class="text-white fs-2" style="font-weight: 600; letter-spacing: 2px;">Be Verified</p>
                <p class="text-white text-wrap text-center" style="width:  17rem;">Login in to PWS dental to access online features</p>
            </div>
            <!-- Right Box -->
            <div class="col-md-6 right-box">
                <div class="row align-items-center">
                    <form class="was-validated" novalidate>
                        <div class="header-text mb-4">
                            <h2>Welcome back!</h2>
                            <p>We are happy to have you back</p>
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" id="username" class="form-control form-control-lg bg-light fs-6" name="txtusername" placeholder="Username" maxlength="5" minlength="5" pattern="^[A-Z][0-9]{4}$" required>
                            <div class="invalid-feedback" id="username-error">Please enter your username</div>
                        </div>
                        <div class="input-group mb-3 rounded-3">
                            <input type="password" id="password" class="form-control form-control-lg bg-light fs-6" name="txtpassword" placeholder="Password" minlength="8" maxlength="20" required pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$">
                            <div class="invalid-feedback" id="password-error">Please enter your password</div>
                        </div>
                        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <div class="captcha"></div>
                        <div class="input-group mb-3">
                            <button class="btn btn-lg w-100 fs-6" id="btnlogin" name="login">Login</button>
                        </div>
                        <div id="lockout-message" class="text-center lock-message"></div>
                        <div class="row text-center fs-6">
                            <span style="font-size: small;">Not registered? <a href="signup.php">Sign Up</a></span>
                        </div>
                        <div class="row text-center fs-6 mt-2">
                            <span style="font-size: small;">Go to home page: <a href="home.html">Home</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <script>
        function encryptData(data, secretKey) {
            var iv = CryptoJS.lib.WordArray.random(16);
            var key = CryptoJS.enc.Utf8.parse(secretKey);
            var encrypted = CryptoJS.AES.encrypt(data, key, {
                iv: iv
            });
            return CryptoJS.enc.Base64.stringify(iv.concat(encrypted.ciphertext));
        }
        var validusername = false;
        var validpassword = false;

        function isValidInput() {
            if (validusername && validpassword && grecaptcha.getResponse().length > 0) {
                return true;
            }
            let text = !validusername ? $('#username-error').text() :
                !validpassword ? $('#password-error').text() :
                "Please confirm you are not a robot.";

            Swal.fire({
                title: 'Error',
                text: text,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }

        $(document).ready(function() {
            $('#username').on("input", function() {
                var username = $(this).val();
                if (username.length == 0) {
                    $('#username-error').text("Please fill out the username field");
                    validusername = false;
                } else if (!(/^[A-Z][0-9]{4}$/.test(username))) {
                    $('#username-error').text("Please enter a valid username.");
                    validusername = false;
                } else {
                    $('#username-error').text("");
                    validusername = true;
                }
            });

            $('#password').on("input", function() {
                const passwordValue = $(this).val();
                if (passwordValue.length == 0) {
                    $('#password-error').text("Please fill out the password field");
                    validpassword = false;
                } else if (!(/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[0-9]).{8,20}$/.test(passwordValue))) {
                    $('#password-error').text("Please enter a valid password");
                    validpassword = false;
                }  else {
                    $('#password-error').text("");
                    validpassword = true;
                }
            });
        });

        let isRecaptchaRendered = false;

        function onRecaptchaSuccess() {
        }

        function onRecaptchaFailure() {
        }

        window.onload = function() {
            const recaptchaElement = document.querySelector('.captcha');
            if (recaptchaElement && !isRecaptchaRendered) {
                grecaptcha.render(recaptchaElement, {
                    'sitekey': '6LekfF8qAAAAABkon6_TgQ282coDEkVPWccpwi3I',
                    'callback': onRecaptchaSuccess,
                    'expired-callback': onRecaptchaFailure
                });
                isRecaptchaRendered = true;
            }
        };

        $('#btnlogin').on('click', function(event) {
            event.preventDefault();
            if (isValidInput()) {
                var uname = $('#username').val();
                var pas = $('#password').val();
                const key = '12345678901234567890123456789012';
                $.ajax({
                    type: 'POST',
                    url: 'processLogin.php',
                    data: {
                        txtusername: uname,
                        txtpassword: encryptData(pas, key),
                        'g-recaptcha-response': grecaptcha.getResponse()
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirectUrl; // Redirect to the appropriate dashboard
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });

                            grecaptcha.reset(); // Reset reCAPTCHA
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Internal server error. Please try again later.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        grecaptcha.reset();
                    }
                });
            }
        });
    </script>
</body>

</html>