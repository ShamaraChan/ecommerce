<?php
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
