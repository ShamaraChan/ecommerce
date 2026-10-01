<?php
// Garantizar que la sesi0n este iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Genera o recupera el token Anti-CSRF unico de la sesion
 */
function obtener_token_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
/**
 * Imprime un campo de entrada oculto HTML con el token CSRF
 */
function campo_token_csrf() {
    $token = obtener_token_csrf();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
/**
 * Valida que el token enviado en la solicitud POST sea legitimo
 */
function validar_token_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(403);
            exit('Error 403: Solicitud rechazada por validacion de seguridad (CSRF detectado).');
        }
    }
}
?>