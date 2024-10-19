<?php

// function authorizePatientAccess()
// {
//     if (session_status() === PHP_SESSION_NONE) {
//         session_start();
//         session_regenerate_id(true);
//     }
//     if (!isset($_SESSION['userid'])) {
//         header('HTTP/1.1 401 Unauthorized');
//         header('Location: /user/login.php');
//         return false;
//     }

//     // Check if the user is of type 'Patient'
//     if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'Patient') {
//         header('HTTP/1.1 403 Forbidden');
//         echo '<h1>403 Forbidden</h1>';
//         echo '<p>You do not have permission to access this page.</p>';
//         return false;
//     }
//     // Additional security headers
//     header("X-Content-Type-Options: nosniff"); // Prevent MIME type sniffing
//     header("X-XSS-Protection: 1; mode=block"); // Enable the cross-site scripting filter
//     header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
//     return true;
// }

require('../vendor/autoload.php'); 
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function authorizePatientAccess() {
    $key = "abcd4658hj^";

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }

    // Check if JWT access token is present
    if (isset($_COOKIE['jwtToken'])) {
        $accessToken = $_COOKIE['jwtToken'];

        try {
            $decoded = JWT::decode($accessToken, new Key($key, 'HS256'));
            if ($decoded->usertype !== 'Patient') {
                header('HTTP/1.1 403 Forbidden');
                echo '<h1>403 Forbidden</h1>';
                echo '<p>You do not have permission to access this page.</p>';
                return false;
            }

            // Save user info in session
            $_SESSION['userid'] = $decoded->userid;
            $_SESSION['usertype'] = $decoded->usertype;

            // Set security headers
            header("X-Content-Type-Options: nosniff"); 
            header("X-XSS-Protection: 1; mode=block"); 
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
            
            return true;

        } catch (\Firebase\JWT\ExpiredException $e) {
            // If the access token is expired, check for the refresh token
            if (isset($_COOKIE['refreshToken'])) {
                $refreshToken = $_COOKIE['refreshToken'];
                try {
                    // Decode the refresh token
                    $refreshDecoded = JWT::decode($refreshToken, new Key($key, 'HS256'));

                    // Generate a new access token using the refresh token information
                    $newAccessTokenPayload = [
                        'iat' => time(), // Issued at: time when the token is generated
                        'exp' => time() + (15 * 60), // New expiration time: 15 minutes from now
                        'userid' => $refreshDecoded->userid,
                        'usertype' => $refreshDecoded->usertype
                    ];
                    // Generate the new access token
                    $newAccessToken = JWT::encode($newAccessTokenPayload, $key, 'HS256');

                    // Set the new access token as a cookie
                    setcookie('jwtToken', $newAccessToken, time() + (15 * 60), "/", "", true, true);

                    // Update session data
                    $_SESSION['userid'] = $refreshDecoded->userid;
                    $_SESSION['usertype'] = $refreshDecoded->usertype;

                    return true;

                } catch (\Firebase\JWT\ExpiredException $e) {
                    header('HTTP/1.1 401 Unauthorized');
                    echo 'Refresh token has expired. Please log in again.';
                    return false;

                } catch (\Firebase\JWT\SignatureInvalidException $e) {
                    header('HTTP/1.1 401 Unauthorized');
                    echo 'Invalid refresh token signature.';
                    return false;

                } catch (Exception $e) {
                    header('HTTP/1.1 401 Unauthorized');
                    echo 'Invalid refresh token.';
                    return false;
                }
            } else {
                header('HTTP/1.1 401 Unauthorized');
                echo 'Access token expired and no refresh token available. Please log in again.';
                return false;
            }

        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            header('HTTP/1.1 401 Unauthorized');
            echo 'Invalid token signature.';
            return false;

        } catch (Exception $e) {
            header('HTTP/1.1 401 Unauthorized');
            echo 'Invalid token.';
            return false;
        }
    } else {
        header('HTTP/1.1 401 Unauthorized');
        header('Location: /user/login.php');
        return false;
    }
}
