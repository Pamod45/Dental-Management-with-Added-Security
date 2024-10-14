<?php
// auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is of type 'patient'
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Patient') {
    // Redirect unauthorized users to the error page with a 404 status code
    header("Location: ../Errors/error.php?code=404");
    exit();
}
?>