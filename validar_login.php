<?php
session_set_cookie_params([
    'httponly' => true,
    'secure'   => false, // cámbialo a true cuando el sitio esté en HTTPS (producción)
    'samesite' => 'Lax',
]);
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
    'SELECT id, nombre, apellidopaterno, apellidomaterno, username, password, rol, estatus,
            intentos_fallidos, bloqueado_hasta
     FROM usuarios
     WHERE username = ?
     LIMIT 1'
);
$stmt->execute([$username]);
$usuario = $stmt->fetch();

// --- Bloqueo por intentos ---
if ($usuario && $usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
    $restante = ceil((strtotime($usuario['bloqueado_hasta']) - time()) / 60);
    $_SESSION['error_login'] = "Cuenta bloqueada temporalmente. Intenta en {$restante} minuto(s).";
    header('Location: login.php');
    exit;
}

if (!$usuario || (int)$usuario['estatus'] !== 1 || !password_verify($password, $usuario['password'])) {
    if ($usuario) {
        $intentos = (int)$usuario['intentos_fallidos'] + 1;
        $bloqueadoHasta = null;
        if ($intentos >= 3) {
            $bloqueadoHasta = date('Y-m-d H:i:s', time() + 300); // 5 minutos
            $intentos = 0;
        }
        $pdo->prepare('UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?')
            ->execute([$intentos, $bloqueadoHasta, $usuario['id']]);
    }
    $_SESSION['error_login'] = 'Usuario o contraseña incorrectos, o cuenta inactiva.';
    header('Location: login.php');
    exit;
}

// Login correcto: resetea contador de intentos
$pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?')
    ->execute([$usuario['id']]);

session_regenerate_id(true);

$_SESSION['usuario'] = [
    'id' => (int)$usuario['id'],
    'nombre' => $usuario['nombre'],
    'username' => $usuario['username'],
    'rol' => (int)$usuario['rol'],
];

header('Location: dashboard.php');
exit;