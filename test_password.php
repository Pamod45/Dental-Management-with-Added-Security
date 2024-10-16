<?php
// $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] ||
// $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']
session_start();
// echo $_SERVER['REMOTE_ADDR'];
 echo $_SESSION['ip_address'];
if (isset($_GET['text'])) {
    // Get the text from the URL
    $text = $_GET['text'];

    // Hash the text using bcrypt
    $hashedText = password_hash($text, PASSWORD_BCRYPT);

    // Display the original text and the hashed value
    echo "<h1>Original Text: " . htmlspecialchars($text) . "</h1>";
    echo "<h2>Bcrypt Hash: " . htmlspecialchars($hashedText) . "</h2>";
} else {
    echo "<h1>No text provided. Please provide a text parameter in the URL.</h1>";
}
?>
