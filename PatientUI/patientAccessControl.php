<?php
function authorizePatientAccess()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }
    // Authenticating user access
    if (!isset($_SESSION['userid'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('Location: /user/login.php');
        exit();
    }

    //Authorize only patients
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Patient') {
        header('HTTP/1.1 403 Forbidden');
        header('Location: /Errors/unauthorize.php');
        exit();
    }

    //Prevent session hijacking
    // if (
    //     $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] 
    // ) {
        //$_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']
    //     $_SESSION = array();
    //     session_unset();
    //     session_destroy();
    //     header('HTTP/1.1 403 Forbidden');
    //     header('Location: /Errors/unauthorize.php');
    //     exit();
    // }
}
