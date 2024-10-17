<?php
require('../vendor/autoload.php'); // Ensure you have the Firebase JWT library
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function authorizePatientAccessFromCookie() {
    $key = "abcd4658hj^"; // Your secret key

    // Initialize the response array
    $response = [
        'verified' => false,
        'message' => ''
    ];

    // Check if the JWT cookie is set
    if (isset($_COOKIE['jwtToken'])) {
        $token = $_COOKIE['jwtToken'];

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Check if the user type is 'Patient'
            if ($decoded->usertype !== 'Patient') {
                $response['message'] = 'Access denied.';
                return $response; // Access is denied
            }

            // Access granted, return decoded data
            $response['verified'] = true;
            $response['data'] = $decoded; // Include decoded data if needed
            return $response;

        } catch (\Firebase\JWT\ExpiredException $e) {
            $response['message'] = 'Token has expired.';
            return $response;

        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $response['message'] = 'Invalid token signature.';
            return $response;

        } catch (Exception $e) {
            $response['message'] = 'Invalid token.';
            return $response;
        }
    } else {
        $response['message'] = 'Token not found.';
        return $response;
    }
}
?>
