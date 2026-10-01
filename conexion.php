<?php
// ==========================================
// 1. CONFIGURACIÓN SEGURA DE COOKIES Y SESIÓN
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// FORZAR REEMISIÓN CON BANDERAS SEGURAS
setcookie(session_name(), session_id(), [
    'expires' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// ==========================================
// 2. CONEXIÓN A LA BASE DE DATOS
// ==========================================
$host = 'localhost';
$db   = 'ecommerce'; //datallizer_ecommerceA
$user = 'root'; //datallizer_novenoa
$pass = ''; //proyectonoveno2026
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('No se pudo conectar con la base de datos.');
}