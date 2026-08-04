<?php
session_start();

if (!empty($_SESSION['usuario'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}

exit;
