<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
 
use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApiTransactionError;
use Openpay\Data\OpenpayApiRequestError;
 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
 
require 'conexion.php'; // Debe definir $pdo (PDO)
require 'crypto_helper.php';
 
// ==========================
// ELIMINAR PEDIDO
// ==========================
if (isset($_POST['delete'])) {
    $registro_id = (int)$_POST['delete'];
 
    try {
        $pdo->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$registro_id]);
    } catch (PDOException $e) {
        // silencioso, igual que el original
    }
 
    header("Location: industrias.php");
    exit;
}
 
// ==========================
// PROCESAR PAGO
// ==========================
if (isset($_POST['update'])) {
 
    if (!isset($_POST['identificador']) || empty($_POST['identificador'])) {
        die('Identificador no recibido');
    }
 
    $identificador = $_POST['identificador'];
 
    $stmt = $pdo->prepare("
        SELECT nombre, apellidop, apellidom, email, telefono, total
        FROM pedidos
        WHERE identificador = ?
        LIMIT 1
    ");
    $stmt->execute([$identificador]);
    $pedido = $stmt->fetch();
 
    if (!$pedido) {
        die('Pedido no encontrado');
    }
 
    $email = $pedido['email'];
    $pedido['telefono'] = decryptData($pedido['telefono']);
 
    $openpay = Openpay::getInstance(
        $_ENV['OPENPAY_ID'],
        $_ENV['OPENPAY_SK'],
        $_ENV['OPENPAY_COUNTRY'],
        $_SERVER['REMOTE_ADDR']
    );
 
    Openpay::setProductionMode(false);
 
    $customer = [
        'name'         => $pedido['nombre'],
        'last_name'    => trim($pedido['apellidop'] . ' ' . $pedido['apellidom']),
        'phone_number' => $pedido['telefono'],
        'email'        => $pedido['email'],
    ];
 
    $method = $_POST['payment_method'];
    $montoFinal = number_format((float)$pedido['total'], 2, '.', '');
 
    try {
        if ($method === 'card') {
            $chargeData = [
                'method'            => 'card',
                'source_id'         => $_POST["token_id"],
                'amount'            => $montoFinal,
                'description'       => 'Pedido productos #' . $identificador,
                'order_id'          => $identificador . '_' . time(),
                'device_session_id' => $_POST["deviceIdHiddenFieldName"],
                'customer'          => $customer,
                'redirect_url'      => 'https://midominio.mx/productos/openpay-respuesta.php'
            ];
        } else {
            $chargeData = [
                'method'      => 'bank_account',
                'amount'      => $montoFinal,
                'description' => 'Pedido #' . $identificador,
                'order_id'    => $identificador . '_' . time(),
                'customer'    => $customer
            ];
        }
 
        $charge = $openpay->charges->create($chargeData);
 
        if ($method === 'bank_account') {
            $vigencia = $charge->due_date;
            $bank = $charge->payment_method->bank;
            $clabe = $charge->payment_method->clabe;
            $convenio = $charge->payment_method->agreement;
            $referencia = $charge->payment_method->name;
            $url_pdf = $charge->payment_method->url_spei;
 
            $fechaObj = new DateTime($vigencia);
 
            $formateador = new IntlDateFormatter(
                'es_ES',
                IntlDateFormatter::LONG,
                IntlDateFormatter::SHORT,
                'America/Mexico_City',
                IntlDateFormatter::GREGORIAN
            );
 
            $vigenciaAmigable = $formateador->format($fechaObj);
 
            $pdo->prepare("
                UPDATE pedidos SET
                    status_pago = 'Pendiente SPEI',
                    openpay_id = ?,
                    pdf_url = ?,
                    clabe = ?,
                    vigencia = ?,
                    banco = ?,
                    convenio = ?,
                    referencia = ?
                WHERE identificador = ?
            ")->execute([$charge->id, $url_pdf, $clabe, $vigenciaAmigable, $bank, $convenio, $referencia, $identificador]);
 
            notifyCustomer($identificador, $email, $bank, $clabe, $convenio, $referencia, $url_pdf, $montoFinal, $vigenciaAmigable);
            header("Location: orden.php?id=" . $identificador);
            exit;
        } else {
            if ($charge->status == 'completed') {
                $pdo->prepare("UPDATE pedidos SET status_pago = 'Pagado', openpay_id = ? WHERE identificador = ?")
                    ->execute([$charge->id, $identificador]);
 
                header("Location: orden.php?id=" . $identificador);
                exit;
            } elseif ($charge->status == 'charge_pending') {
                if ($method === 'bank_account') {
                    header("Location: " . $charge->payment_method->url_spei);
                } else {
                    header("Location: " . $charge->payment_method->url);
                }
                exit;
            }
        }
    } catch (OpenpayApiTransactionError $e) {
        handleOpenpayError($e, $identificador);
    } catch (OpenpayApiRequestError $e) {
        handleOpenpayError($e, $identificador);
    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'title'   => 'ERROR DEL SISTEMA',
            'message' => 'Contacta a soporte: ' . $e->getMessage(),
            'icon'    => 'error'
        ];
        header("Location: pago.php?id=$identificador");
        exit;
    }
 
    exit;
}
 
function handleOpenpayError($e, $identificador)
{
    $errorCode = $e->getErrorCode();
 
    switch ($errorCode) {
        case 3001:
            $message = 'La tarjeta fue rechazada';
            break;
        case 3002:
            $message = 'La tarjeta ha expirado';
            break;
        case 3003:
            $message = 'Fondos insuficientes';
            break;
        case 3004:
            $message = 'La tarjeta fue rechazada';
            break;
        case 3005:
            $message = 'La tarjeta fue rechazada';
            break;
        case 2005:
            $message = 'La fecha de expiración es incorrecta';
            break;
        case 15001:
            $message = 'La autenticación de la tarjeta falló. Por favor, intenta con otro método de pago o contacta a tu banco.';
            break;
        default:
            $message = 'Error (' . $errorCode . '): ' . $e->getMessage();
            break;
    }
 
    $_SESSION['alert'] = [
        'title'   => 'PAGO NO APROBADO',
        'message' => $message,
        'icon'    => 'error'
    ];
 
    header("Location: pago.php?id=$identificador");
    exit;
}
 
// ==========================
// CREAR PEDIDO
// ==========================
if (isset($_POST['save'])) {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidop = trim($_POST['apellidop'] ?? '');
    $apellidom = trim($_POST['apellidom'] ?? '');
    $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $telefono  = trim($_POST['telefono'] ?? '');
    $calle     = trim($_POST['calle'] ?? '');
    $exterior  = trim($_POST['exterior'] ?? '');
    $interior  = trim($_POST['interior'] ?? '');
    $colonia   = trim($_POST['colonia'] ?? '');
    $ciudad    = trim($_POST['ciudad'] ?? '');
    $estado    = trim($_POST['estado'] ?? '');
    $postal    = trim($_POST['postal'] ?? '');
    $pais      = trim($_POST['pais'] ?? '');
    $cuponCodigo = strtoupper(trim($_POST['cuponLS'] ?? ''));
    $carritoJson = $_POST['cartLS'] ?? '[]';
    $estatus   = 1;
 
    $carrito = json_decode($carritoJson, true);
 
    if (!is_array($carrito) || count($carrito) === 0) {
        header("Location: pedido.php");
        exit;
    }
 
    // Comisión configurada (id = 4)
    $comisionFactor = 0;
    $com = $pdo->prepare("SELECT valoruno FROM configuraciones WHERE id = ? LIMIT 1");
    $com->execute([4]);
    $comRow = $com->fetch();
    if ($comRow) {
        $comisionFactor = (float)str_replace('%', '', $comRow['valoruno']) / 100;
    }
 
    // Costos de envío configurados
    $envioMinimo = 0;
    $envioCosto = 0;
    $env = $pdo->prepare("SELECT valoruno, valordos FROM configuraciones WHERE nombre = ? LIMIT 1");
    $env->execute(['Envio']);
    $envRow = $env->fetch();
    if ($envRow) {
        $envioMinimo = (float)$envRow['valoruno'];
        $envioCosto = (float)$envRow['valordos'];
    }
 
    // Calcular cada línea del carrito contra la base de datos (nunca confiar en precios del navegador)
    $subtotal = 0;
    $descuentoTotal = 0;
    $lineasVenta = [];
 
    foreach ($carrito as $item) {
        $idProducto = (int)($item['id'] ?? 0);
        $cantidad = (int)($item['cantidad'] ?? 0);
        if (!$idProducto || $cantidad <= 0) {
            continue;
        }
 
        $stmtProd = $pdo->prepare("SELECT titulo, sku, preciounitario, preciomayoreo, cantidadmayoreo, descuento FROM productosventa WHERE id = ? AND estatus = 1");
        $stmtProd->execute([$idProducto]);
        $prod = $stmtProd->fetch();
        if (!$prod) {
            continue; // producto ya no existe o está inactivo, se omite
        }
 
        $pUnitario = (float)$prod['preciounitario'];
        $pMayoreo = (float)$prod['preciomayoreo'];
        $minMayoreo = (int)$prod['cantidadmayoreo'];
        $descuentoBase = (float)$prod['descuento'];
 
        $aplicaMayoreo = ($minMayoreo > 0 && $pMayoreo > 0 && $pMayoreo < $pUnitario && $cantidad >= $minMayoreo);
        $precioBase = $aplicaMayoreo ? $pMayoreo : $pUnitario;
        $descuentoAplicable = $aplicaMayoreo ? 0 : $descuentoBase;
 
        $precioConComision = $precioBase * (1 + $comisionFactor);
 
        $subtotal += $precioConComision * $cantidad;
        $descuentoTotal += $descuentoAplicable * $cantidad;
 
        $lineasVenta[] = [
            'titulo'    => $prod['titulo'],
            'sku'       => $prod['sku'],
            'cantidad'  => $cantidad,
            'precio'    => $precioConComision,
            'descuento' => $descuentoAplicable,
        ];
    }
 
    if (count($lineasVenta) === 0) {
        header("Location: pedido.php");
        exit;
    }
 
    // Validar y calcular cupón
    $cuponMonto = 0;
    if ($cuponCodigo !== '') {
        $stmtCupon = $pdo->prepare("SELECT * FROM cupones WHERE codigo = ? AND estatus = 1 LIMIT 1");
        $stmtCupon->execute([$cuponCodigo]);
        $cupon = $stmtCupon->fetch();
 
        if ($cupon) {
            $baseParaCupon = $subtotal - $descuentoTotal;
            if ($baseParaCupon >= (float)$cupon['minimo']) {
                $cuponMonto = $baseParaCupon * ((float)$cupon['porcentaje'] / 100);
                if ((float)$cupon['maximo'] > 0 && $cuponMonto > (float)$cupon['maximo']) {
                    $cuponMonto = (float)$cupon['maximo'];
                }
            }
        } else {
            $cuponCodigo = ''; // cupón inválido, se ignora
        }
    }
 
    // Calcular envío
    $montoParaEnvio = $subtotal - $descuentoTotal - $cuponMonto;
    $envioMonto = ($montoParaEnvio < $envioMinimo) ? $envioCosto : 0;
 
    $total = max(0, $subtotal - $descuentoTotal - $cuponMonto + $envioMonto);
 
    // Cifrado de datos sensibles (teléfono y dirección) antes de guardarlos
    $telefonoCifrado = encryptData($telefono);
    $calleCifrada = encryptData($calle);
 
    try {
        $pdo->beginTransaction();
 
        $stmt = $pdo->prepare("
            INSERT INTO pedidos
                (nombre, apellidop, apellidom, email, telefono, calle, exterior, interior, colonia, ciudad, estado, postal, pais,
                 cupon, cuponMonto, descuentoTotal, subtotal, envioMonto, total, productos, estatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $nombre, $apellidop, $apellidom, $email, $telefonoCifrado, $calleCifrada,
            $exterior, $interior, $colonia, $ciudad, $estado, $postal, $pais,
            $cuponCodigo, $cuponMonto, $descuentoTotal, $subtotal, $envioMonto, $total,
            $carritoJson, $estatus
        ]);
 
        $last_id = $pdo->lastInsertId();
 
        // Generar identificador
        $folio_num = str_pad($last_id, 7, "0", STR_PAD_LEFT);
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellidop, 0, 1) . substr($apellidom, 0, 1));
        $identificador = "MIEMPRESA-$folio_num-$iniciales";
 
        $pdo->prepare("UPDATE pedidos SET identificador = ? WHERE id = ?")
            ->execute([$identificador, $last_id]);
 
        // Guardar las líneas de venta
        $stmtVenta = $pdo->prepare("
            INSERT INTO ventas (identificador, titulo, sku, cantidad, precio, descuento)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        foreach ($lineasVenta as $linea) {
            $stmtVenta->execute([
                $identificador, $linea['titulo'], $linea['sku'],
                $linea['cantidad'], $linea['precio'], $linea['descuento']
            ]);
        }
 
        $pdo->commit();
 
        header("Location: pago.php?id=$identificador");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        header("Location: pedido.php");
        exit;
    }
}
 
function notifyCustomer($identificador, $email, $bank, $clabe, $convenio, $referencia, $url_pdf, $total, $vigenciaAmigable)
{
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->Port       = $_ENV['SMTP_PORT'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USERNAME'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'ssl';
 
    $mail->setFrom($_ENV['SMTP_USERNAME'], 'MI EMPRESA');
    $mail->addAddress($email);
    $mail->Subject = 'Realiza tu pago por SPEI';
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
 
    $body = '
            <!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
 
<body style="margin:0; padding:0; background:#ffffff; font-family:Arial, sans-serif;">
 
   <div style="background-color: #f3f3f3; max-width: 600px; margin: 0px auto; text-align: center; line-height: 100px;">
     <img src="https://dominio.com/images/logo.png" 
         style="width: 90%; vertical-align: middle; display: inline-block;padding: 10px 0;" 
         alt="">
</div>
 
 
    <div style="
                max-width:600px;
                background:#ffffff;
                margin:0px auto 10px;
                padding:15px;
            ">
 
       
        <h1 style="font-size:25px; margin:30px 0; text-align:left;">
            REALIZA TU PAGO POR SPEI
        </h1>
 
        <p>Estas a un paso de finalizar tu pedido, realiza tu pago por SPEI antes del <strong>' . $vigenciaAmigable . '</strong> con los siguientes datos:</p>
 
        <div style="
                    background: #2c3b5c; 
                    color:#fff; 
                    padding:15px; 
                    border-radius:3px;
                    margin:30px 0;
                ">
            <p><strong>Beneficiario:</strong> DOMINIO</p>
            <p><strong>Concepto:</strong> Pedido #' . $identificador . '</p>
            <p><strong>Total a pagar:</strong> $' . number_format($total, 2) . '</p>
            <p><strong>Banco:</strong> ' . $bank . '</p>
            <p><strong>Referencia:</strong> ' . htmlspecialchars(implode(' ', str_split($referencia, 4)), ENT_QUOTES, 'UTF-8') . '</p>
<p><strong>CLABE (Con otros bancos):</strong> ' . htmlspecialchars(implode(' ', str_split($clabe, 4)), ENT_QUOTES, 'UTF-8') . '</p>
<p><strong>Convenio CIE (Con BBVA):</strong> ' . htmlspecialchars(implode(' ', str_split($convenio, 3)), ENT_QUOTES, 'UTF-8') . '</p>
        </div>
 
        <p>También puedes consultar la referencia de pago <a href="https://dominio.mx/productos/orden.php?id=' . $identificador . '" target="_blank">aquí</a>.</p>
 
        <p>¿Quieres cambiar tu método de pago o necesitas generar una nueva referencia SPEI? Ingresa a: <a href="https://dominio.mx/productos/pago.php?id=' . $identificador . '">https://dominio.mx/productos/pago.php?id=' . $identificador . '</a></p>
 
        <p style="text-align:center;"><strong>EQUIPO DE VENTAS</strong></p>
        <p style="text-align:center;">MI EMPRESA</p>
 
        <p style="font-size:8px; color:#555;">
            Este es un email enviado automaticamente desde el canal de comunicación del sistema de planificación de recursos empresariales MI EMPRESA.
        </p>
 
    </div>
</body>
 
</html>';
 
    $mail->Body = $body;
    try {
        $mail->send();
    } catch (Exception $e) {
        error_log('Error correo cliente: ' . $e->getMessage());
    }
}