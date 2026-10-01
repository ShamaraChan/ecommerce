<?php
require_once 'auth.php';
exigirAdmin();
require_once 'conexion.php';
require_once 'csrf.php';

// Bloquea cualquier solicitud POST que no incluya el token legítimo
validar_token_csrf();


 
// ==========================
// ELIMINAR PRODUCTO
// ==========================
if (isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
 
    try {
        $pdo->prepare("DELETE FROM productosventa WHERE id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM mediosventa WHERE idproducto = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM industriaasociadaventa WHERE idproducto = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM categoriasasociadasventa WHERE idproducto = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM subcategoriasasociadasventa WHERE idproducto = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM asociarproductos WHERE idproductopadre = ? OR idproductopack = ?")->execute([$id, $id]);
        $pdo->prepare("DELETE FROM asociartallas WHERE idproductoprincipal = ? OR idproductotalla = ?")->execute([$id, $id]);
 
        $_SESSION['alert'] = [
            'title' => 'ELIMINADO',
            'message' => 'Producto eliminado exitosamente',
            'icon' => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'title' => 'ERROR',
            'message' => 'Notifica a soporte',
            'icon' => 'error'
        ];
    }
 
    header("Location: carga-tienda-en-linea.php");
    exit;
}
 
// ==========================
// ELIMINAR UN MEDIO
// ==========================
if (isset($_POST['deletemedio'])) {
    $id = (int)$_POST['deletemedio'];
    $idproducto = (int)$_POST['idproducto'];
 
    try {
        $pdo->prepare("DELETE FROM mediosventa WHERE id = ?")->execute([$id]);
 
        $_SESSION['alert'] = [
            'title' => 'MEDIO ELIMINADO',
            'message' => 'Medio eliminado exitosamente',
            'icon' => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'title' => 'ERROR',
            'message' => 'Medio no eliminado.',
            'icon' => 'error'
        ];
    }
 
    header('Location: editarproductoventa.php?id=' . $idproducto);
    exit;
}
 
// ==========================
// EDITAR PRODUCTO
// ==========================
if (isset($_POST['update'])) {
    $idproducto = (int)$_POST['id'];
    $titulo = $_POST['titulo'];
    $subtitulo = $_POST['subtitulo'];
    $estatus = $_POST['estatus'];
    $detalles = $_POST['detalles'];
    $stock = $_POST['stock'];
    $sku = $_POST['sku'];
    $stockminimo = $_POST['stockminimo'];
    $preciounitario = $_POST['preciounitario'];
    $preciomayoreo = $_POST['preciomayoreo'];
    $cantidadmayoreo = $_POST['cantidadmayoreo'];
    $descuento = $_POST['descuento'];
    $medios_delete = $_POST['medios_delete'] ?? [];
 
    try {
        $stmt = $pdo->prepare("
            UPDATE productosventa
            SET titulo = ?, subtitulo = ?, estatus = ?, detalles = ?, stock = ?, sku = ?,
                stockminimo = ?, preciounitario = ?, preciomayoreo = ?, cantidadmayoreo = ?, descuento = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $titulo, $subtitulo, $estatus, $detalles, $stock, $sku,
            $stockminimo, $preciounitario, $preciomayoreo, $cantidadmayoreo, $descuento, $idproducto
        ]);
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'title' => 'ERROR',
            'message' => 'Error al actualizar el producto: ' . $e->getMessage(),
            'icon' => 'error'
        ];
        header('Location: editarproductoventa.php?id=' . $idproducto);
        exit;
    }
 
    // Eliminar categorías, subcategorías e industrias anteriores
    $pdo->prepare("DELETE FROM categoriasasociadasventa WHERE idproducto = ?")->execute([$idproducto]);
    $pdo->prepare("DELETE FROM subcategoriasasociadasventa WHERE idproducto = ?")->execute([$idproducto]);
    $pdo->prepare("DELETE FROM industriaasociadaventa WHERE idproducto = ?")->execute([$idproducto]);
 
    // Insertar nuevas subcategorías
    if (!empty($_POST['subcategoria'])) {
        $stmtSub = $pdo->prepare("INSERT INTO subcategoriasasociadasventa (idproducto, subcategoria) VALUES (?, ?)");
        foreach ($_POST['subcategoria'] as $subcategoria) {
            $stmtSub->execute([$idproducto, $subcategoria]);
        }
    }
 
    // Insertar nuevas categorías
    if (!empty($_POST['categoria'])) {
        $stmtCat = $pdo->prepare("INSERT INTO categoriasasociadasventa (idproducto, categoria) VALUES (?, ?)");
        foreach ($_POST['categoria'] as $categoria) {
            $stmtCat->execute([$idproducto, $categoria]);
        }
    }
 
    // Insertar nuevas industrias
    if (!empty($_POST['industria'])) {
        $stmtInd = $pdo->prepare("INSERT INTO industriaasociadaventa (idproducto, industria) VALUES (?, ?)");
        foreach ($_POST['industria'] as $industria) {
            $stmtInd->execute([$idproducto, $industria]);
        }
    }
 
    // Eliminar medios seleccionados y sus archivos
    if (!empty($medios_delete) && is_array($medios_delete)) {
        $stmtGetMedio = $pdo->prepare("SELECT medio FROM mediosventa WHERE id = ?");
        $stmtDelMedio = $pdo->prepare("DELETE FROM mediosventa WHERE id = ?");
 
        foreach ($medios_delete as $medio_id) {
            $medio_id = (int)$medio_id;
 
            $stmtGetMedio->execute([$medio_id]);
            $row = $stmtGetMedio->fetch();
 
            if ($row && file_exists($row['medio'])) {
                unlink($row['medio']);
            }
 
            $stmtDelMedio->execute([$medio_id]);
        }
    }
 
    // Guardar nuevos medios
    if (isset($_FILES['medios']) && !empty($_FILES['medios']['tmp_name'][0])) {
        $directorio = 'productosventa/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }
 
        $stmtInsertMedio = $pdo->prepare("INSERT INTO mediosventa (idproducto, medio) VALUES (?, ?)");
 
        foreach ($_FILES['medios']['tmp_name'] as $key => $tmp_name) {
            $nombre_original = $_FILES['medios']['name'][$key];
            $tipo = $_FILES['medios']['type'][$key];
            $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
 
            $nombre_archivo = uniqid() . ".jpg";
 
            if (in_array($tipo, ['image/jpeg', 'image/png', 'image/jpg'])) {
                $imagen = imagecreatefromstring(file_get_contents($tmp_name));
                if ($imagen !== false) {
                    imagejpeg($imagen, $directorio . $nombre_archivo);
                    imagedestroy($imagen);
                }
            } elseif ($ext === 'pdf' || $ext === 'mp4') {
                $nombre_archivo = uniqid() . "." . $ext;
                move_uploaded_file($tmp_name, $directorio . $nombre_archivo);
            } else {
                continue;
            }
 
            $ruta_archivo = $directorio . $nombre_archivo;
            $stmtInsertMedio->execute([$idproducto, $ruta_archivo]);
        }
    }
 
    // Asociar/actualizar producto pack
    if (!empty($_POST['idproductopack'])) {
        $idasoc = $_POST['idasoc'] ?? '';
        $idproductopadre = $_POST['idproductopack'];
        $cantidadpack = $_POST['cantidadpack'] ?? 1;
 
        if (empty($idasoc) || $idasoc == "0") {
            $pdo->prepare("
                INSERT INTO asociarproductos (idproductopack, idproductopadre, cantidadpack)
                VALUES (?, ?, ?)
            ")->execute([$idproducto, $idproductopadre, $cantidadpack]);
        } else {
            $pdo->prepare("
                UPDATE asociarproductos
                SET cantidadpack = ?, idproductopadre = ?
                WHERE idproductopack = ? AND idproductopadre = ?
            ")->execute([$cantidadpack, $idproductopadre, $idproducto, $idasoc]);
        }
    }
 
    $_SESSION['alert'] = [
        'title' => 'ACTUALIZADO',
        'message' => 'Producto actualizado con éxito',
        'icon' => 'success'
    ];
    header('Location: editarproductoventa.php?id=' . $idproducto);
    exit;
}
 
// ==========================
// CREAR PRODUCTO
// ==========================
if (isset($_POST['save'])) {
    $titulo = $_POST['titulo'];
    $subtitulo = $_POST['subtitulo'];
    $detalles = $_POST['detalles'];
    $stock = $_POST['stock'];
    $sku = $_POST['sku'];
    $stockminimo = $_POST['stockminimo'];
    $preciounitario = $_POST['preciounitario'];
    $preciomayoreo = $_POST['preciomayoreo'];
    $cantidadmayoreo = $_POST['cantidadmayoreo'];
    $descuento = $_POST['descuento'];
    $estatus = '1';
 
    try {
        $stmt = $pdo->prepare("
            INSERT INTO productosventa
                (titulo, subtitulo, detalles, stock, sku, stockminimo, preciounitario, preciomayoreo, cantidadmayoreo, descuento, estatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $titulo, $subtitulo, $detalles, $stock, $sku, $stockminimo,
            $preciounitario, $preciomayoreo, $cantidadmayoreo, $descuento, $estatus
        ]);
 
        $idproducto = $pdo->lastInsertId();
 
        if (!empty($_POST['categoria'])) {
            $stmtCat = $pdo->prepare("INSERT INTO categoriasasociadasventa (idproducto, categoria) VALUES (?, ?)");
            foreach ($_POST['categoria'] as $categoria) {
                $stmtCat->execute([$idproducto, $categoria]);
            }
        }
 
        if (!empty($_POST['subcategoria'])) {
            $stmtSub = $pdo->prepare("INSERT INTO subcategoriasasociadasventa (idproducto, subcategoria) VALUES (?, ?)");
            foreach ($_POST['subcategoria'] as $subcategoria) {
                $stmtSub->execute([$idproducto, $subcategoria]);
            }
        }
 
        if (!empty($_POST['industria'])) {
            $stmtInd = $pdo->prepare("INSERT INTO industriaasociadaventa (idproducto, industria) VALUES (?, ?)");
            foreach ($_POST['industria'] as $industria) {
                $stmtInd->execute([$idproducto, $industria]);
            }
        }
 
        if (isset($_FILES['medios']) && !empty($_FILES['medios']['tmp_name'][0])) {
            $directorio = 'productosventa/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
 
            $stmtInsertMedio = $pdo->prepare("INSERT INTO mediosventa (idproducto, medio) VALUES (?, ?)");
 
            foreach ($_FILES['medios']['tmp_name'] as $key => $tmp_name) {
                $nombre_original = $_FILES['medios']['name'][$key];
                $tipo = $_FILES['medios']['type'][$key];
                $ext = pathinfo($nombre_original, PATHINFO_EXTENSION);
 
                $nombre_archivo = uniqid() . ".jpg";
 
                if (in_array($tipo, ['image/jpeg', 'image/png', 'image/jpg'])) {
                    $imagen = imagecreatefromstring(file_get_contents($tmp_name));
                    if ($imagen !== false) {
                        imagejpeg($imagen, $directorio . $nombre_archivo);
                        imagedestroy($imagen);
                    }
                } elseif ($ext == 'pdf' || $ext == 'mp4') {
                    $nombre_archivo = uniqid() . "." . $ext;
                    move_uploaded_file($tmp_name, $directorio . $nombre_archivo);
                } else {
                    continue;
                }
 
                $ruta_archivo = $directorio . $nombre_archivo;
                $stmtInsertMedio->execute([$idproducto, $ruta_archivo]);
            }
        }
 
        if (!empty($_POST['idproductopack'])) {
            $idproductopadre = $_POST['idproductopack'];
            $cantidadpack = $_POST['cantidadpack'];
 
            $pdo->prepare("
                INSERT INTO asociarproductos (cantidadpack, idproductopack, idproductopadre)
                VALUES (?, ?, ?)
            ")->execute([$cantidadpack, $idproducto, $idproductopadre]);
        }
 
        $_SESSION['alert'] = [
            'title' => 'REGISTRADO',
            'message' => 'Producto registrado con éxito',
            'icon' => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'title' => 'ERROR',
            'message' => 'Notifica a soporte',
            'icon' => 'error'
        ];
    }
 
    header("Location: carga-tienda-en-linea.php");
    exit;
}
 
// ==========================
// DUPLICAR PRODUCTO (agregar talla vacía / clon simple)
// ==========================
if (isset($_POST['duplicar'])) {
    $idproducto = (int)$_POST['id'];
 
    try {
        $pdo->beginTransaction();
 
        // 1. Duplicar producto base
        $pdo->prepare("
            INSERT INTO productosventa (
                titulo, subtitulo, estatus, detalles, stock, sku,
                stockminimo, preciounitario, preciomayoreo,
                cantidadmayoreo, descuento, talla
            )
            SELECT
                titulo, subtitulo, estatus, detalles, stock, sku,
                stockminimo, preciounitario, preciomayoreo,
                cantidadmayoreo, descuento, 'Unitalla'
            FROM productosventa
            WHERE id = ?
        ")->execute([$idproducto]);
 
        $nuevoProductoId = $pdo->lastInsertId();
 
        // 2. Duplicar categorías
        $pdo->prepare("
            INSERT INTO categoriasasociadasventa (idproducto, categoria)
            SELECT ?, categoria FROM categoriasasociadasventa WHERE idproducto = ?
        ")->execute([$nuevoProductoId, $idproducto]);
 
        // 3. Duplicar subcategorías
        $pdo->prepare("
            INSERT INTO subcategoriasasociadasventa (idproducto, subcategoria)
            SELECT ?, subcategoria FROM subcategoriasasociadasventa WHERE idproducto = ?
        ")->execute([$nuevoProductoId, $idproducto]);
 
        // 4. Duplicar industrias
        $pdo->prepare("
            INSERT INTO industriaasociadaventa (idproducto, industria)
            SELECT ?, industria FROM industriaasociadaventa WHERE idproducto = ?
        ")->execute([$nuevoProductoId, $idproducto]);
 
        // 5. Duplicar medios (sin subir archivos, comparte las mismas rutas)
        $pdo->prepare("
            INSERT INTO mediosventa (idproducto, medio)
            SELECT ?, medio FROM mediosventa WHERE idproducto = ?
        ")->execute([$nuevoProductoId, $idproducto]);
 
        // 6. Duplicar asociarproductos (packs)
        $stmtPack = $pdo->prepare("SELECT idproductopadre, cantidadpack FROM asociarproductos WHERE idproductopack = ?");
        $stmtPack->execute([$idproducto]);
        $packs = $stmtPack->fetchAll();
 
        $stmtInsertPack = $pdo->prepare("
            INSERT INTO asociarproductos (idproductopack, idproductopadre, cantidadpack)
            VALUES (?, ?, ?)
        ");
        foreach ($packs as $row) {
            if (!empty($row['idproductopadre'])) {
                $stmtInsertPack->execute([$nuevoProductoId, $row['idproductopadre'], $row['cantidadpack']]);
            }
        }
 
        $pdo->commit();
 
        $_SESSION['alert'] = [
            'title' => 'Exito',
            'message' => 'Tallas agregadas correctamente',
            'icon' => 'success'
        ];
        header('Location: carga-tienda-en-linea.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['alert'] = [
            'title' => 'Error al duplicar el producto',
            'message' => 'Contacte a su proveedor. ' . $e->getMessage(),
            'icon' => 'error'
        ];
        header('Location: carga-tienda-en-linea.php');
        exit;
    }
}
 
// ==========================
// AGREGAR TALLAS (duplica el producto una vez por cada talla marcada)
// ==========================
if (isset($_POST['saveTalla'])) {
 
    if (
        empty($_POST['idproductoprincipal']) ||
        empty($_POST['talla']) ||
        !is_array($_POST['talla'])
    ) {
        die('Datos incompletos');
    }
 
    $idProductoPrincipal = (int)$_POST['idproductoprincipal'];
    $tallas = $_POST['talla'];
    $cantidadpack = isset($_POST['cantidadpack']) ? (int)$_POST['cantidadpack'] : 1;
 
    try {
        $pdo->beginTransaction();
 
        // 1. Producto base
        $stmt = $pdo->prepare("SELECT * FROM productosventa WHERE id = ? LIMIT 1");
        $stmt->execute([$idProductoPrincipal]);
        $producto = $stmt->fetch();
 
        if (!$producto) {
            throw new Exception('Producto no encontrado');
        }
 
        // 2. Primera talla -> UPDATE al producto original
        $primeraTalla = array_shift($tallas);
        $pdo->prepare("UPDATE productosventa SET talla = ? WHERE id = ?")
            ->execute([$primeraTalla, $idProductoPrincipal]);
 
        // 3. Preparar duplicado
        unset($producto['id']);
        $columnas = array_keys($producto);
        $placeholders = implode(',', array_fill(0, count($columnas), '?'));
 
        $stmtInsertProducto = $pdo->prepare(
            "INSERT INTO productosventa (" . implode(',', $columnas) . ") VALUES ($placeholders)"
        );
 
        $stmtInsertAsociarTallas = $pdo->prepare("
            INSERT INTO asociartallas (idproductoprincipal, idproductotalla, talla)
            VALUES (?, ?, ?)
        ");
 
        // 4. Determinar idproductopadre real (packs)
        $idProductoPadreFinal = null;
 
        $stmtPadre = $pdo->prepare("SELECT idproductopadre FROM asociarproductos WHERE idproductopadre = ? LIMIT 1");
        $stmtPadre->execute([$idProductoPrincipal]);
        $esPadre = $stmtPadre->fetch();
 
        if ($esPadre) {
            $idProductoPadreFinal = $idProductoPrincipal;
        } else {
            $stmtPack = $pdo->prepare("SELECT idproductopadre FROM asociarproductos WHERE idproductopack = ? LIMIT 1");
            $stmtPack->execute([$idProductoPrincipal]);
            $esPack = $stmtPack->fetch();
            if ($esPack) {
                $idProductoPadreFinal = $esPack['idproductopadre'];
            }
        }
 
        // 5. Función para duplicar relaciones (categorías, subcategorías, industrias, medios)
        $duplicarRelacion = function ($tabla, $idProductoOrigen, $idProductoNuevo) use ($pdo) {
            $res = $pdo->prepare("SELECT * FROM $tabla WHERE idproducto = ?");
            $res->execute([$idProductoOrigen]);
            $filas = $res->fetchAll();
 
            foreach ($filas as $row) {
                unset($row['id']);
                $row['idproducto'] = $idProductoNuevo;
 
                $cols = array_keys($row);
                $vals = array_values($row);
                $ph = implode(',', array_fill(0, count($cols), '?'));
 
                $pdo->prepare("INSERT INTO $tabla (" . implode(',', $cols) . ") VALUES ($ph)")
                    ->execute($vals);
            }
        };
 
        // 6. Duplicar por cada talla
        $stmtInsertPack = $pdo->prepare("
            INSERT INTO asociarproductos (idproductopadre, idproductopack, cantidadpack)
            VALUES (?, ?, ?)
        ");
 
        foreach ($tallas as $talla) {
            $producto['talla'] = $talla;
            $stmtInsertProducto->execute(array_values($producto));
 
            $idProductoTalla = $pdo->lastInsertId();
 
            $stmtInsertAsociarTallas->execute([$idProductoPrincipal, $idProductoTalla, $talla]);
 
            $duplicarRelacion('categoriasasociadasventa', $idProductoPrincipal, $idProductoTalla);
            $duplicarRelacion('subcategoriasasociadasventa', $idProductoPrincipal, $idProductoTalla);
            $duplicarRelacion('industriaasociadaventa', $idProductoPrincipal, $idProductoTalla);
            $duplicarRelacion('mediosventa', $idProductoPrincipal, $idProductoTalla);
 
            if (!is_null($idProductoPadreFinal)) {
                $stmtInsertPack->execute([$idProductoPadreFinal, $idProductoTalla, $cantidadpack]);
            }
        }
 
        $pdo->commit();
        header('Location: carga-tienda-en-linea.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
}
 