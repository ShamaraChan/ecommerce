<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

function exigirAdmin(): void
{
    if ((int)($_SESSION['usuario']['rol'] ?? 0) !== 1) {
        http_response_code(403);
        exit('Acceso denegado. Se requiere un usuario administrador.');
    }
}

function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}
