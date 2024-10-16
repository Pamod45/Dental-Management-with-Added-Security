<?php
$error_code = isset($_GET['code']) ? $_GET['code'] : 'Unknown';
$error_message = 'Sorry, an unexpected error occurred.';

switch ($error_code) {
    case 403:
        $error_message = 'Sorry, you do not have permission to access this page.';
        break;
    case 404:
        $error_message = 'Sorry, the page you are looking for could not be found.';
        break;
    case 500:
        $error_message = 'Sorry, there was an internal server error.';
        break;
    default:
        $error_message = 'Sorry, an unexpected error occurred.';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo htmlspecialchars($error_code); ?></title>
</head>
<body>
    <h1>Error <?php echo htmlspecialchars($error_code); ?></h1>
    <p><?php echo htmlspecialchars($error_message); ?></p>
    <p><a href="/user/login.php">Return to Login</a></p>
</body>
</html>

