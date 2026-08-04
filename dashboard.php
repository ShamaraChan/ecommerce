<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel principal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="barra">
        <strong>Ecommerce</strong>
        <nav>
            <?php if ((int)$_SESSION['usuario']['rol'] === 1): ?>
                <a href="usuarios.php">Usuarios</a>
            <?php endif; ?>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
    </header>

    <main class="contenedor">
        <section class="tarjeta">
            <h1>Bienvenido, <?= e($_SESSION['usuario']['nombre']) ?></h1>
            <p>Sesión iniciada como <strong><?= e($_SESSION['usuario']['username']) ?></strong>.</p>

            <?php if ((int)$_SESSION['usuario']['rol'] === 1): ?>
                <a class="boton" href="usuarios.php">Administrar usuarios</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
