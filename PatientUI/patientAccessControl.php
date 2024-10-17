<?php
function authorizePatientAccess()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }
    if (!isset($_SESSION['userid'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('Location: /user/login.php');
        return false;
    }

    // Check if the user is of type 'Patient'
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Patient') {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>403 Forbidden</h1>';
        echo '<p>You do not have permission to access this page.</p>';
        return false;
    }
    // Additional security headers
    header("X-Content-Type-Options: nosniff"); // Prevent MIME type sniffing
    header("X-XSS-Protection: 1; mode=block"); // Enable the cross-site scripting filter
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    return true;
}

