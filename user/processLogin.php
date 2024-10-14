<?php

if ($_SERVER['REQUEST_METHOD'] != "POST"){
    //do the error handling
}
require("../config/dbconnection.php");
require('../vendor/autoload.php');
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create a logger instance
$logger = new Logger('login_processor');
$logger->pushHandler(new StreamHandler('../logs/login_processor.log', Logger::DEBUG));

// Start the session securely
$cookieLifetime = 1800; // 30 minutes
if (!session_id()) {
    session_start([
        'lifetime' => $cookieLifetime,
        'cookie_secure' => true,  // Ensure cookies are sent only over HTTPS
        'cookie_httponly' => true, // Prevent JavaScript access to session cookies
        'cookie_samesite' => 'Strict' // Help mitigate CSRF attacks
    ]);
}

$maxAttempts = 3; // Max allowed attempts
$lockoutTime = 30; // Lockout time in seconds

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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
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

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
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


