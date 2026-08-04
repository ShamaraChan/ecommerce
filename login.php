<?php
session_start();

if (!empty($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['error_login'] ?? '';
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="centrado">
    <main class="tarjeta login">
        <h1>Iniciar sesión</h1>

        <?php if ($error): ?>
            <div class="alerta error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="validar_login.php" method="post">
            <label for="username">Usuario</label>
            <input id="username" name="username" type="text" required autofocus>

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
