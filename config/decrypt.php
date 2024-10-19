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

?>
