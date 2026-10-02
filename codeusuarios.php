<?php
require_once 'auth.php';
exigirAdmin();
require_once 'conexion.php';
require_once 'vendor/autoload.php';
 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 
// ==========================
// ELIMINAR USUARIO
// ==========================
if (isset($_POST['delete'])) {
 
    $id = filter_var($_POST['delete'], FILTER_VALIDATE_INT);
 
    if (!$id) {
        $_SESSION['alert'] = ['title' => 'ID inválido', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    if ((int)$id === (int)$_SESSION['usuario']['id']) {
        $_SESSION['alert'] = ['title' => 'No puedes eliminar tu propio usuario', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    try {
        $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
 
        $_SESSION['alert'] = [
            'title'   => 'USUARIO ELIMINADO',
            'message' => 'Usuario eliminado exitosamente',
            'icon'    => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'title'   => 'ERROR AL ELIMINAR',
            'message' => 'Notifica a soporte',
            'icon'    => 'error'
        ];
    }
 
    header('Location: usuarios.php');
    exit;
}
 
// ==========================
// EDITAR USUARIO
// ==========================
if (isset($_POST['update'])) {
 
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
 
    if (!$id) {
        $_SESSION['alert'] = ['title' => 'ID de usuario inválido', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    $rolSesion = (int)$_SESSION['usuario']['rol'];
 
    $nombre          = trim($_POST['nombre'] ?? '');
    $apellidopaterno = trim($_POST['apellidopaterno'] ?? '');
    $apellidomaterno = trim($_POST['apellidomaterno'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $rol             = (int)($_POST['rol'] ?? 2);
    $password        = $_POST['password'] ?? '';
 
    // Solo un administrador puede cambiar el estatus; si no, se conserva el actual
    if ($rolSesion === 1 && isset($_POST['estatus'])) {
        $estatus = (int)$_POST['estatus'];
    } else {
        $actual = $pdo->prepare('SELECT estatus FROM usuarios WHERE id = ?');
        $actual->execute([$id]);
        $estatus = (int)$actual->fetchColumn();
    }
 
    if ($nombre === '' || $apellidopaterno === '' || $username === '') {
        $_SESSION['alert'] = ['title' => 'Completa los campos obligatorios', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    if ($password !== '' && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $_SESSION['alert'] = ['title' => 'La contraseña debe tener mínimo 8 caracteres, mayúsculas, minúsculas y números', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
    try {
        if ($password !== '') {
            $sql = 'UPDATE usuarios
                    SET nombre = ?, apellidopaterno = ?, apellidomaterno = ?,
                        username = ?, password = ?, rol = ?, estatus = ?
                    WHERE id = ?';
            $parametros = [
                $nombre, $apellidopaterno, $apellidomaterno, $username,
                password_hash($password, PASSWORD_DEFAULT), $rol, $estatus, $id,
            ];
        } else {
            $sql = 'UPDATE usuarios
                    SET nombre = ?, apellidopaterno = ?, apellidomaterno = ?,
                        username = ?, rol = ?, estatus = ?
                    WHERE id = ?';
            $parametros = [
                $nombre, $apellidopaterno, $apellidomaterno, $username, $rol, $estatus, $id,
            ];
        }
 
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
 
        if ((int)$id === (int)$_SESSION['usuario']['id']) {
            $_SESSION['usuario']['nombre']   = $nombre;
            $_SESSION['usuario']['username'] = $username;
            $_SESSION['usuario']['rol']      = $rol;
        }
 
        $_SESSION['alert'] = [
            'title'   => 'USUARIO EDITADO',
            'message' => 'Usuario editado exitosamente',
            'icon'    => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'title'   => 'ERROR AL EDITAR',
            'message' => $e->getCode() === '23000' ? 'Ese correo ya está registrado' : 'Notifica a soporte',
            'icon'    => 'error'
        ];
    }
 
    header('Location: usuarios.php');
    exit;
}
 
// ==========================
// CREAR USUARIO
// ==========================
if (isset($_POST['save'])) {
 
    $nombre          = trim($_POST['nombre'] ?? '');
    $apellidopaterno = trim($_POST['apellidopaterno'] ?? '');
    $apellidomaterno = trim($_POST['apellidomaterno'] ?? '');
    $email           = trim($_POST['username'] ?? '');
    $password        = $_POST['password'] ?? '';
    $rol             = (int)($_POST['rol'] ?? 2);
    $estatus         = 1;
 
    if ($nombre === '' || $apellidopaterno === '' || $email === '' || $password === '') {
        $_SESSION['alert'] = ['title' => 'Completa los campos obligatorios', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $_SESSION['alert'] = ['title' => 'La contraseña debe tener mínimo 8 caracteres, mayúsculas, minúsculas y números', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
    
    $rol_nombre = $rol === 1 ? 'Administrador' : 'Colaborador';
 
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? LIMIT 1');
    $check->execute([$email]);
 
    if ($check->rowCount() > 0) {
        $_SESSION['alert'] = ['title' => 'ERROR', 'message' => 'Este correo ya está registrado', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    try {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
 
        $insert = $pdo->prepare(
            'INSERT INTO usuarios (nombre, apellidopaterno, apellidomaterno, username, password, rol, estatus)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([$nombre, $apellidopaterno, $apellidomaterno, $email, $hashed, $rol, $estatus]);
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['title' => 'ERROR', 'message' => 'Notifica a soporte', 'icon' => 'error'];
        header('Location: usuarios.php');
        exit;
    }
 
    // Envío de correo (opcional, no bloquea la creación si falla)
    $correoEnviado = false;
 
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->Port       = $_ENV['SMTP_PORT'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'tls';
 
        $mail->setFrom($_ENV['SMTP_USERNAME'], 'Mi Empresa');
        $mail->addAddress($email);
        $mail->Subject = 'NUEVO USUARIO';
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Body = '
            <html><body style="font-family: system-ui;">
                <p>Estimado/a ' . htmlspecialchars($nombre) . '</p>
                <p>Tu cuenta se creó exitosamente.</p>
                <p><b>Correo:</b> ' . htmlspecialchars($email) . '</p>
                <p><b>Contraseña:</b> ' . htmlspecialchars($password) . '</p>
                <p><b>Rol:</b> ' . htmlspecialchars($rol_nombre) . '</p>
                <p>Por seguridad no compartas tus credenciales con nadie.</p>
            </body></html>';
 
        $correoEnviado = $mail->send();
    } catch (Exception $e) {
        error_log('Error correo: ' . $e->getMessage());
    }
 
    $_SESSION['alert'] = $correoEnviado
        ? ['title' => 'SOLICITUD EXITOSA', 'message' => 'Usuario creado. Revisa tu correo electrónico', 'icon' => 'success']
        : ['title' => 'USUARIO CREADO', 'message' => 'El usuario se creó pero el correo no pudo enviarse', 'icon' => 'warning'];
 
    header('Location: usuarios.php');
    exit;
}
 
header('Location: usuarios.php');
exit;
 