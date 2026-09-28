<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/ico.ico" />
    <title>Términos y Condiciones | Mi Empresa</title>
</head>
 
<body style="background-color: #f5f5f5;">
    <?php include 'componentes/menu.php'; ?>
 
    <div class="container-fluid">
        <div class="row justify-content-center" style="margin-top: 120px !important;">
            <div class="col-11 col-md-8 mb-5">
                <h1 class="mb-4">Términos y Condiciones</h1>
                <p class="text-muted">Última actualización: <?= date('d/m/Y') ?></p>
 
                <h5 class="mt-4">1. Aceptación de los términos</h5>
                <p>Al utilizar este sitio y realizar una compra, usted acepta los presentes Términos y Condiciones en su totalidad. Si no está de acuerdo con alguno de ellos, le pedimos no utilizar este sitio.</p>
 
                <h5 class="mt-4">2. Registro y cuenta</h5>
                <p>Es responsabilidad del usuario mantener la confidencialidad de su nombre de usuario y contraseña. Si el usuario pierde u olvida su contraseña, deberá seguir el proceso de recuperación correspondiente o contactar a soporte. No nos hacemos responsables por accesos no autorizados derivados del mal resguardo de las credenciales por parte del usuario.</p>
 
                <h5 class="mt-4">3. Precios y disponibilidad</h5>
                <p>Los precios mostrados en el sitio están sujetos a cambio sin previo aviso. Todos los productos están sujetos a disponibilidad de inventario. En caso de que un producto no esté disponible después de haberse realizado el pago, nos pondremos en contacto con el cliente para ofrecer un reembolso o producto alternativo.</p>
 
                <h5 class="mt-4">4. Proceso de pago</h5>
                <p>Los pagos se procesan a través de <strong>Openpay</strong>, una plataforma de pagos certificada. Aceptamos pago con tarjeta de crédito/débito y transferencia bancaria (SPEI). El pedido se considera confirmado únicamente cuando el pago ha sido aprobado por el procesador de pagos.</p>
 
                <h5 class="mt-4">5. Envíos</h5>
                <p>Los tiempos de entrega son estimados y pueden variar según la ubicación del cliente y la disponibilidad del servicio de paquetería. El costo de envío se calcula automáticamente en el carrito de compras según el monto total de la compra.</p>
 
                <h5 class="mt-4">6. Política de reembolsos y cancelaciones</h5>
                <p>Las solicitudes de cancelación deben realizarse antes de que el pedido sea enviado. Una vez enviado el producto, las devoluciones estarán sujetas a revisión caso por caso, considerando el estado del producto. Los reembolsos, cuando procedan, se realizarán por el mismo medio de pago utilizado en la compra original.</p>
 
                <h5 class="mt-4">7. Uso del cupón de descuento</h5>
                <p>Los cupones de descuento aplican únicamente dentro de su periodo de vigencia y bajo las condiciones (monto mínimo de compra, monto máximo de descuento) establecidas para cada cupón. Solo se puede aplicar un cupón por compra.</p>
 
                <h5 class="mt-4">8. Limitación de responsabilidad</h5>
                <p>No nos hacemos responsables por daños indirectos derivados del uso o la imposibilidad de uso de este sitio, incluyendo pero no limitado a interrupciones del servicio por causas de fuerza mayor, fallas del proveedor de hosting, o fallas del procesador de pagos ajenas a nuestro control.</p>
 
                <h5 class="mt-4">9. Propiedad intelectual</h5>
                <p>Todo el contenido de este sitio (textos, imágenes, logotipos) es propiedad de Mi Empresa o de sus respectivos titulares y está protegido por las leyes de propiedad intelectual aplicables.</p>
 
                <h5 class="mt-4">10. Modificaciones</h5>
                <p>Nos reservamos el derecho de modificar estos Términos y Condiciones en cualquier momento. Los cambios serán publicados en esta misma página y entrarán en vigor a partir de su publicación.</p>
            </div>
        </div>
    </div>
 
    <?php include 'footer.php'; ?>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script src="js/menu.js"></script>
</body>
 
</html>