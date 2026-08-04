<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error_login'] = 'Escribe tu usuario y contraseña.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, nombre, apellidopaterno, apellidomaterno, username, password, rol, estatus
     FROM usuarios
     WHERE username = ?
     LIMIT 1'
);
$stmt->execute([$username]);
$usuario = $stmt->fetch();

if (!$usuario || (int)$usuario['estatus'] !== 1 || !password_verify($password, $usuario['password'])) {
    $_SESSION['error_login'] = 'Usuario o contraseña incorrectos, o cuenta inactiva.';
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);

$_SESSION['usuario'] = [
    'id' => (int)$usuario['id'],
    'nombre' => $usuario['nombre'],
    'username' => $usuario['username'],
    'rol' => (int)$usuario['rol'],
];

header('Location: dashboard.php');
exit;
