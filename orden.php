<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php';
 
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: tienda-en-linea.php');
    exit;
}
 
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE identificador = ? LIMIT 1");
$stmt->execute([$_GET['id']]);
$pedido = $stmt->fetch();
 
if (!$pedido) {
    header('Location: tienda-en-linea.php');
    exit;
}
 
$stmtVentas = $pdo->prepare("SELECT titulo, sku, cantidad, precio, descuento FROM ventas WHERE identificador = ?");
$stmtVentas->execute([$pedido['identificador']]);
$ventas = $stmtVentas->fetchAll();
 
$statusPago = $pedido['status_pago'] ?? '';
$esPagado = (strtolower($statusPago) === 'pagado');
$esPendienteSpei = (strtolower($statusPago) === 'pendiente spei');
?>
<!DOCTYPE html>
<html lang="es">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/ico.ico" />
    <title>Tu pedido | Mi Empresa</title>
</head>
 
<body style="background-color: #f5f5f5;">
    <?php include 'componentes/menu.php'; ?>
 
    <div class="container-fluid">
        <div class="row mt-5 justify-content-center" style="margin-top: 100px !important;">
            <div class="col-11 col-md-7 mt-5 mb-5">
 
                <?php if ($esPagado): ?>
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">¡Gracias por tu compra!</h2>
                        <p class="text-muted">Tu pago fue confirmado exitosamente.</p>
                    </div>
                <?php elseif ($esPendienteSpei): ?>
                    <div class="text-center mb-4">
                        <i class="bi bi-hourglass-split text-warning" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">Pedido registrado</h2>
                        <p class="text-muted">Revisa tu correo para completar el pago por SPEI antes de la fecha límite.</p>
                    </div>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <i class="bi bi-info-circle text-secondary" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">Pedido registrado</h2>
                        <p class="text-muted">Estatus actual: <?= htmlspecialchars($statusPago ?: 'En proceso') ?></p>
                    </div>
                <?php endif; ?>
 
                <div class="card p-4" style="border-radius: 15px;">
                    <p class="mb-1"><b>ID Pedido:</b> <?= htmlspecialchars($pedido['identificador'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mb-1"><b>Recibe:</b> <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellidop'] . ' ' . $pedido['apellidom'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mb-1"><b>Correo:</b> <?= htmlspecialchars($pedido['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="mb-3"><b>Envío a:</b> <?= htmlspecialchars($pedido['calle'] . ' #' . $pedido['exterior'] . ', ' . $pedido['colonia'] . ', ' . $pedido['ciudad'] . ', ' . $pedido['estado'] . '. CP ' . $pedido['postal'], ENT_QUOTES, 'UTF-8') ?></p>
 
                    <hr>
 
                    <p class="mb-2"><b>Productos:</b></p>
                    <?php foreach ($ventas as $item): ?>
                        <div class="row mb-2">
                            <div class="col-9">
                                <p class="mb-0"><strong><?= (int)$item['cantidad'] ?> x <?= htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                                <p class="mb-0 small text-muted">SKU: <?= htmlspecialchars($item['sku'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <div class="col-3 text-end">
                                <p class="mb-0">$<?= number_format($item['cantidad'] * $item['precio'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
 
                    <hr>
 
                    <div class="text-end">
                        <p class="mb-1"><b>Subtotal:</b> $<?= number_format($pedido['subtotal'], 2) ?></p>
                        <?php if ((float)$pedido['descuentoTotal'] > 0): ?>
                            <p class="mb-1 text-success"><b>Descuento:</b> -$<?= number_format($pedido['descuentoTotal'], 2) ?></p>
                        <?php endif; ?>
                        <?php if ((float)$pedido['cuponMonto'] > 0): ?>
                            <p class="mb-1 text-success"><b>Cupón (<?= htmlspecialchars($pedido['cupon'], ENT_QUOTES, 'UTF-8') ?>):</b> -$<?= number_format($pedido['cuponMonto'], 2) ?></p>
                        <?php endif; ?>
                        <p class="mb-1"><b>Envío:</b> <?= (float)$pedido['envioMonto'] > 0 ? '$' . number_format($pedido['envioMonto'], 2) : 'GRATIS' ?></p>
                        <p style="font-weight: 600;"><b>Total:</b> $<?= number_format($pedido['total'], 2) ?></p>
                    </div>
                </div>
 
                <div class="text-center mt-4">
                    <a href="tienda-en-linea.php" class="btn btn-danger">Seguir comprando</a>
                </div>
 
            </div>
        </div>
    </div>
 
    <?php include 'footer.php'; ?>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script>
        // Limpia el carrito ya que la compra se completó
        localStorage.removeItem("empresaCart");
        localStorage.removeItem("empresaCupon");
    </script>
</body>
 
</html>