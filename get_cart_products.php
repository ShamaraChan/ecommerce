<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php';
 
header('Content-Type: application/json; charset=UTF-8');
 
$ids = $_POST['ids'] ?? [];
 
if (!is_array($ids) || count($ids) === 0) {
    echo json_encode([]);
    exit;
}
 
// Sanear: solo enteros
$ids = array_filter(array_map('intval', $ids));
 
if (count($ids) === 0) {
    echo json_encode([]);
    exit;
}
 
// Construir placeholders dinámicos (?, ?, ?, ...)
$placeholders = implode(',', array_fill(0, count($ids), '?'));
 
$stmt = $pdo->prepare("
    SELECT
        p.id AS productoID,
        p.titulo,
        p.preciounitario,
        p.preciomayoreo,
        p.cantidadmayoreo,
        p.descuento,
        (SELECT medio FROM mediosventa WHERE idproducto = p.id ORDER BY id LIMIT 1) AS primer_medio
    FROM productosventa p
    WHERE p.id IN ($placeholders)
      AND p.estatus = 1
");
$stmt->execute($ids);
$productos = $stmt->fetchAll();
 
echo json_encode($productos);