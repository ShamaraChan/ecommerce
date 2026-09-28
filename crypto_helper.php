<?php
/**
 * Funciones de cifrado simétrico (AES-256-CBC) para datos sensibles
 * como teléfono y dirección, cumpliendo con el requisito de
 * "cifrar datos sensibles con funciones de cifrado simétrico".
 *
 * La llave de cifrado vive en .env (nunca en el código fuente ni en Git).
 */
 
function encryptData(string $plainText): string
{
    $key = getEncryptionKey();
    $cipher = 'aes-256-cbc';
    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivLength);
 
    $encrypted = openssl_encrypt($plainText, $cipher, $key, OPENSSL_RAW_DATA, $iv);
 
    // Guardamos el IV junto con el texto cifrado (separados por "::"),
    // porque se necesita el mismo IV para descifrar después.
    return base64_encode($iv) . '::' . base64_encode($encrypted);
}
 
function decryptData(?string $encryptedText): string
{
    if (empty($encryptedText) || strpos($encryptedText, '::') === false) {
        // Si el dato no tiene el formato cifrado (ej. datos viejos sin migrar),
        // lo regresamos tal cual para no romper la vista.
        return $encryptedText ?? '';
    }
 
    $key = getEncryptionKey();
 
    [$ivEncoded, $encryptedEncoded] = explode('::', $encryptedText, 2);
    $iv = base64_decode($ivEncoded);
    $encrypted = base64_decode($encryptedEncoded);
 
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
 
    return $decrypted !== false ? $decrypted : '';
}
 
function getEncryptionKey(): string
{
    $keyBase64 = $_ENV['ENCRYPTION_KEY'] ?? null;
    if (!$keyBase64) {
        throw new Exception('ENCRYPTION_KEY no está configurada en .env');
    }
 
    // La llave se guarda en .env codificada en base64 (32 bytes reales = AES-256)
    return base64_decode($keyBase64);
}