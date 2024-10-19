<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
function encryptData(data, secretKey) {
    var iv = CryptoJS.lib.WordArray.random(16); 
    var key = CryptoJS.enc.Utf8.parse(secretKey); 
    var encrypted = CryptoJS.AES.encrypt(data, key, { iv: iv });
    return CryptoJS.enc.Base64.stringify(iv.concat(encrypted.ciphertext));
}