<?php
header_remove("X-Powered-By");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/uncaught_errors.log');
if ($_SERVER['REQUEST_METHOD'] != "POST" || !isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], '/user/login.php') === false) {
    // redirect to login page
    http_response_code(405);
    header("Location: /user/login.php");
    exit();
}

require("../config/guestDBConnection.php");
require("../vendor/autoload.php");
require('../config/logger.php');
require('../vendor/autoload.php');

$logger = createLogger('guest.log');

use \Firebase\JWT\JWT;

// Start the session securely
$cookieLifetime = 1800; // 30 minutes
if (!session_id()) {
    // Set secure session cookie parameters
    session_set_cookie_params([
        'lifetime' => $cookieLifetime, // Session cookies only last as long as the browser is open
        'path' => '/', // Accessible across the entire domain
        'domain' => 'localhost', // Replace with your actual domain
        'secure' => true, // Ensures the cookie is sent only over HTTPS connections
        'httponly' => true, // Prevents JavaScript access to session cookies
        'samesite' => 'Strict' // Prevents CSRF by ensuring the cookie is only sent for same-site requests
    ]);

    session_start();
}
header('Content-Type: application/json');
$maxAttempts = 3; // Max allowed attempts
$lockoutTime = 30; // Lockout time in seconds
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    header('Content-Type: application/json');
    // Initialize attempts and lockout time
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
    }
    if (!isset($_SESSION['lockout_time'])) {
        $_SESSION['lockout_time'] = 0;
    }
    // Check if user is currently locked out
    if ($_SESSION['lockout_time'] > time()) {
        $remainingLockout = $_SESSION['lockout_time'] - time();
        $logger->warning('Tried to access locked account ' . ($_SESSION['userid'] ?? 'unknown'));
        echo json_encode(['success' => false, 'message' => 'Your account is locked. Please try again in ' . $remainingLockout . ' seconds.']);
        exit();
    }


    // Check reCAPTCHA response
    $recaptchaSecret = '6LekfF8qAAAAAJbchVG_vTMi2m__06WZFgNRZmeu'; // Replace with your actual secret key
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    // Verify the reCAPTCHA response
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptchaSecret&response=$recaptchaResponse");
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        // reCAPTCHA validation failed
        echo json_encode(['success' => false, 'message' => 'Please confirm you are not a robot.']);
        exit();
    }

    // reCAPTCHA passed, proceed with username and password validation
    $username = trim(htmlentities($_POST['txtusername']));
    $con = getDatabaseConnection();
    if (!$con) {
        echo json_encode(['success' => false, 'message' => 'Failed to connect to database.']);
        exit;
    }
    // Use prepared statements to prevent SQL injection
    $query = $con->prepare("SELECT * FROM user WHERE userid = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    // If user exists
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verify the password using password_verify
        if (password_verify($_POST['txtpassword'], $row['password'])) {
            // Create JWT token
            $key = "abcd4658hj^"; // Your secret key
            $payload = [
                'iat' => time(), // Issued at: time when the token is generated
                'exp' => time() + (60 * 60), // Expiration time: 1 hour from now
                'userid' => $row['userid'],
                'usertype' => $row['usertype']
            ];

            // Generate JWT
            $jwt = JWT::encode($payload, $key, 'HS256');

            // Set the JWT as a cookie
            setcookie('jwtToken', $jwt, time() + (60 * 60), "/", "", true, true);

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            // Reset attempts and lockout time on successful login
            $_SESSION['attempts'] = 0;
            $_SESSION['lockout_time'] = 0;

            $_SESSION['userid'] = $row['userid'];
            $_SESSION['registereddate'] = $row['registereddate'];
            $_SESSION['usertype'] = $row['usertype'];
            $_SESSION['authenticated'] = true;

            // Prepare redirect URL based on user type
            $redirectUrl = '';
            switch ($row['usertype']) {
                case "Patient":
                    $redirectUrl = '../PatientUI/dashboard.php';
                    break;
                case "Doctor":
                    $redirectUrl = '../doctor/dashboard.php';
                    break;
                case "Employee":
                    $query2 = "SELECT (SELECT Position FROM employee_type WHERE emptypeid=e.emptypeid) AS position FROM pdms.employee e WHERE userid=?";
                    $query2Stmt = $con->prepare($query2);
                    $query2Stmt->bind_param("s", $username);
                    $query2Stmt->execute();
                    $result2 = $query2Stmt->get_result();
                    $row2 = $result2->fetch_assoc();
                    $redirectUrl = ($row2['position'] == "CounterStaff") ? '../Employee/dashboard.php' : '../branchManagerUI/dashboard.php';
                    break;
            }
            $logger->info('User ' . $username . ' logged in successfully.');
            echo json_encode(['success' => true, 'redirectUrl' => $redirectUrl]);
            exit();
        } else {
            // Handle failed login attempt
            $_SESSION['attempts']++;

            if ($_SESSION['attempts'] >= $maxAttempts) {
                $_SESSION['lockout_time'] = time() + $lockoutTime; // Set lockout time
                $_SESSION['attempts'] = 0; // Reset attempts
                $logger->error('User account ' . $username . ' locked out due to too many failed attempts.');
                echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Your account is locked for 30 seconds.']);
            } else {
                $logger->warning('Failed login attempt for user ' . $username);
                echo json_encode(['success' => false, 'message' => 'Invalid username or password. You have ' . ($maxAttempts - $_SESSION['attempts']) . ' attempts left.']);
            }
            exit();
        }
    } else {
        // Username does not exist
        $_SESSION['attempts']++;
        if ($_SESSION['attempts'] >= $maxAttempts) {
            $_SESSION['lockout_time'] = time() + $lockoutTime; // Set lockout time
            $logger->error('User account ' . $username . ' locked out due to too many failed attempts.');
            echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Your account is locked for 30 seconds.']);
        } else {
            $logger->warning('Failed login attempt for non-existent user ' . $username);
            echo json_encode(['success' => false, 'message' => 'Invalid username or password. You have ' . ($maxAttempts - $_SESSION['attempts']) . ' attempts left.']);
        }
        exit();
    }
} else {
    $logger->error('Invalid request method.');
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
