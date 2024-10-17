<?php
// test_password.php
session_start();

if (isset($_GET['text'])) {
    // Get the text from the URL
    $text = $_GET['text'];

    // Hash the text using bcrypt
    $hashedText = password_hash($text, PASSWORD_BCRYPT);

    // Display the original text and the hashed value
    echo "<h1>Original Text: " . $text . "</h1>";
    echo "<h2>Bcrypt Hash: " . $hashedText . "</h2>";
} else {
    echo "<h1>No text provided. Please provide a text parameter in the URL.</h1>";
}

