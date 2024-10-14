<?php
// error.php
$error_code = isset($_GET['code']) ? $_GET['code'] : 'Unknown';
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
    <p>Sorry, you don't have permission to access this page.</p>
</body>
</html>
