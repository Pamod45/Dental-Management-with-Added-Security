<?php
function authorizeDoctorAccess()
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
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Doctor') {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>403 Forbidden</h1>';
        echo '<p>You do not have permission to access this page.</p>';
        return false;
    }
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    return true;
}

function authorizeDoctorAccess2()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['userid'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('Location: /user/login.php');
        return false;
    }
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Doctor') {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1>403 Forbidden</h1>';
        echo '<p>You do not have permission to access this page.</p>';
        return false;
    }
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    return true;
}


