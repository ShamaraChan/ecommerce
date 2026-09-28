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
    <title>Aviso de Privacidad | Mi Empresa</title>
</head>
 
<body style="background-color: #f5f5f5;">
    <?php include 'componentes/menu.php'; ?>
 
    <div class="container-fluid">
        <div class="row justify-content-center" style="margin-top: 120px !important;">
            <div class="col-11 col-md-8 mb-5">
                <h1 class="mb-4">Aviso de Privacidad</h1>
                <p class="text-muted">Última actualización: <?= date('d/m/Y') ?></p>
 
                <h5 class="mt-4">1. Responsable del tratamiento de datos</h5>
                <p>Mi Empresa, con domicilio en Aguascalientes, México, es responsable del uso y protección de sus datos personales conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP).</p>
 
                <h5 class="mt-4">2. Datos personales que recabamos</h5>
                <p>Para procesar su pedido y brindarle el servicio, recabamos los siguientes datos:</p>
                <ul>
                    <li>Nombre completo</li>
                    <li>Correo electrónico</li>
                    <li>Teléfono</li>
                    <li>Dirección de envío (calle, número, colonia, ciudad, estado, código postal, país)</li>
                </ul>
                <p><strong>Datos de pago:</strong> Su información de tarjeta (número, CVV, fecha de expiración) <u>nunca es recibida ni almacenada por nuestro servidor</u>. Esta información se envía de forma cifrada directamente a <strong>Openpay (una empresa de BBVA)</strong>, nuestro procesador de pagos certificado bajo el estándar PCI-DSS, quien nos regresa únicamente un identificador de transacción (token) de un solo uso. No tenemos acceso, en ningún momento, al número completo de su tarjeta.</p>
 
                <h5 class="mt-4">3. Finalidad del tratamiento de sus datos</h5>
                <p>Sus datos personales serán utilizados para las siguientes finalidades:</p>
                <ul>
                    <li>Procesar y dar seguimiento a su pedido</li>
                    <li>Coordinar el envío de sus productos</li>
                    <li>Enviarle confirmaciones y notificaciones relacionadas con su compra (correo de pedido, referencia de pago SPEI, confirmación de pago)</li>
                    <li>Atender dudas, aclaraciones o solicitudes de soporte</li>
                    <li>Cumplir con obligaciones legales y fiscales aplicables</li>
                </ul>
 
                <h5 class="mt-4">4. Transferencia de datos a terceros</h5>
                <p>Para procesar su pago, compartimos los datos estrictamente necesarios (nombre, correo, teléfono) con <strong>Openpay</strong>, quien actúa como nuestro procesador de pagos. Openpay cuenta con sus propias políticas de privacidad y cumplimiento PCI-DSS. No vendemos, rentamos ni compartimos sus datos personales con terceros para fines de mercadotecnia sin su consentimiento expreso.</p>
 
                <h5 class="mt-4">5. Medidas de seguridad</h5>
                <p>Implementamos medidas técnicas para proteger su información, incluyendo:</p>
                <ul>
                    <li>Contraseñas de usuarios almacenadas con hash irreversible (bcrypt)</li>
                    <li>Consultas a base de datos mediante sentencias preparadas (PDO), que previenen inyección SQL</li>
                    <li>Credenciales de acceso y llaves de integración almacenadas fuera del código fuente público</li>
                    <li>Comunicación cifrada (HTTPS) en el proceso de pago</li>
                </ul>
 
                <h5 class="mt-4">6. Derechos ARCO</h5>
                <p>Usted tiene derecho a Acceder, Rectificar, Cancelar u Oponerse (derechos ARCO) al tratamiento de sus datos personales. Para ejercer estos derechos, puede contactarnos a través de los medios señalados en nuestra página de contacto.</p>
 
                <h5 class="mt-4">7. Cambios a este aviso</h5>
                <p>Nos reservamos el derecho de actualizar este Aviso de Privacidad. Cualquier cambio será publicado en esta misma página.</p>
            </div>
        </div>
    </div>
 
    <?php include 'footer.php'; ?>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <script src="js/menu.js"></script>
</body>
 
</html>
 