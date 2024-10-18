<?php
function decryptData($encryptedData, $secretKey) {
    $cipher = "aes-256-cbc";
    $ivLength = openssl_cipher_iv_length($cipher);
    $data = base64_decode($encryptedData);

    // Extract the initialization vector and the encrypted data
    $iv = substr($data, 0, $ivLength);
    $encryptedText = substr($data, $ivLength);

    // Decrypt the data
    $decrypted = openssl_decrypt($encryptedText, $cipher, $secretKey, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
}

// Receive the encrypted data from the POST request
if (isset($_POST['encryptedData'])) {
    $encryptedData = $_POST['encryptedData'];
    $key = '12345678901234567890123456789012'; // 32-byte key for AES-256

    // Decrypt the data
    $decryptedData = decryptData($encryptedData, $key);

    if ($decryptedData === false) {
        echo "Decryption failed!";
    } else {
        echo "Decrypted Data: " . htmlspecialchars($decryptedData);
    }
} else {
    echo "No data received!";
}
?>
