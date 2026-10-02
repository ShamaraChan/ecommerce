<?php
require_once 'auth.php';
exigirAdmin();
require_once 'conexion.php';

$rolSesion = (int)$_SESSION['usuario']['rol'];
$usernameSesion = $_SESSION['usuario']['username'];

$usuarios = $pdo->query(
    'SELECT id, nombre, apellidopaterno, apellidomaterno, username, rol, estatus
     FROM usuarios
     ORDER BY id DESC'
)->fetchAll();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | Mi Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="css/sidenav.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">
</head>

<body class="sb-nav-fixed">
    <?php include 'sidenav.php'; ?>
    <div id="layoutSidenav_content">
        <div class="container-fluid">
            <div class="row mb-5 mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center bg-dark">
                            <h4 style="color:#fff" class="m-1">USUARIOS</h4>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrear">
                                Nuevo usuario
                            </button>
                        </div>
                        <div class="card-body" style="overflow-x:auto;">
                            <table id="miTabla" class="table table-bordered table-striped" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estatus</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $registro): ?>
                                        <tr>
                                            <td><?= (int)$registro['id'] ?></td>
                                            <td><?= htmlspecialchars(trim($registro['nombre'] . ' ' . $registro['apellidopaterno'] . ' ' . $registro['apellidomaterno'])) ?></td>
                                            <td><?= htmlspecialchars($registro['username']) ?></td>
                                            <td>
                                                <?php
                                                echo match ((int)$registro['rol']) {
                                                    1 => 'Administrador/a',
                                                    2 => 'Colaborador/a',
                                                    default => 'Otro',
                                                };
                                                ?>
                                            </td>
                                            <td>
                                                <?= (int)$registro['estatus'] === 1
                                                    ? '<span class="badge bg-success">Activo</span>'
                                                    : '<span class="badge bg-secondary">Inactivo</span>' ?>
                                            </td>
                                            <td>
                                                <?php
                                                $puedeEditar = ($rolSesion === 1) || ($rolSesion === 2 && $registro['username'] === $usernameSesion);
                                                if ($puedeEditar):
                                                ?>
                                                    <button type="button"
                                                        class="btn btn-warning btn-sm m-1 btn-editar"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditar"
                                                        data-id="<?= (int)$registro['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($registro['nombre']) ?>"
                                                        data-apellidopaterno="<?= htmlspecialchars($registro['apellidopaterno']) ?>"
                                                        data-apellidomaterno="<?= htmlspecialchars($registro['apellidomaterno']) ?>"
                                                        data-username="<?= htmlspecialchars($registro['username']) ?>"
                                                        data-rol="<?= (int)$registro['rol'] ?>"
                                                        data-estatus="<?= (int)$registro['estatus'] ?>">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($rolSesion === 1 && (int)$registro['id'] !== (int)$_SESSION['usuario']['id']): ?>
                                                    <form action="codeusuarios.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                        <button type="submit" name="delete" value="<?= (int)$registro['id'] ?>" class="btn btn-danger btn-sm m-1">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="codeusuarios.php" method="POST" class="row">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">NUEVO USUARIO</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 form-floating mb-3">
                            <input type="text" class="form-control" name="nombre" placeholder="Nombre" required>
                            <label>Nombre</label>
                        </div>
                        <div class="col-12 col-md-6 form-floating mb-3">
                            <input type="text" class="form-control" name="apellidopaterno" placeholder="Apellido paterno" required>
                            <label>Apellido paterno</label>
                        </div>
                        <div class="col-12 col-md-6 form-floating mb-3">
                            <input type="text" class="form-control" name="apellidomaterno" placeholder="Apellido materno">
                            <label>Apellido materno</label>
                        </div>
                        <div class="col-12 form-floating mb-3">
                            <input type="email" class="form-control" name="username" placeholder="Correo" required>
                            <label>Correo</label>
                        </div>
                        <div class="col-12 col-md-7 form-floating mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Contraseña" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Mínimo 8 caracteres, con mayúsculas, minúsculas y números" required>
                            <label>Contraseña</label>
                        </div>
                        <div class="col-12 col-md-5 form-floating mb-3">
                            <select class="form-select" name="rol" required>
                                <option value="" selected disabled>Rol</option>
                                <option value="1">Administrador</option>
                                <option value="2">Colaborador</option>
                            </select>
                            <label>Rol</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" name="save">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="codeusuarios.php" method="POST" class="row">
                    <input type="hidden" name="id" id="editar-id">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">EDITAR USUARIO</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 form-floating mb-3">
                            <input type="text" class="form-control" name="nombre" id="editar-nombre" placeholder="Nombre" required>
                            <label>Nombre</label>
                        </div>
                        <div class="col-12 col-md-6 form-floating mb-3">
                            <input type="text" class="form-control" name="apellidopaterno" id="editar-apellidopaterno" placeholder="Apellido paterno" required>
                            <label>Apellido paterno</label>
                        </div>
                        <div class="col-12 col-md-6 form-floating mb-3">
                            <input type="text" class="form-control" name="apellidomaterno" id="editar-apellidomaterno" placeholder="Apellido materno">
                            <label>Apellido materno</label>
                        </div>
                        <div class="col-12 form-floating mb-3">
                            <input type="email" class="form-control" name="username" id="editar-username" placeholder="Correo" required>
                            <label>Correo</label>
                        </div>
                        <div class="col-12 col-md-7 form-floating mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Dejar vacío para conservar" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Mínimo 8 caracteres, con mayúsculas, minúsculas y números">
                            <label>Nueva contraseña</label>
                        </div>
                        <div class="col-12 col-md-5 form-floating mb-3">
                            <select class="form-select" name="rol" id="editar-rol" required>
                                <option value="1">Administrador</option>
                                <option value="2">Colaborador</option>
                            </select>
                            <label>Rol</label>
                        </div>
                        <?php if ($rolSesion === 1): ?>
                        <div class="col-12 form-floating mb-3">
                            <select class="form-select" name="estatus" id="editar-estatus" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <label>Estatus</label>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" name="update">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="js/sidenav.js"></script>

    <script>
        $(document).ready(function() {
            $('#miTabla').DataTable({
                "order": [
                    [0, "desc"]
                ]
            });

            // Llenar el modal de editar con los datos de la fila seleccionada
            document.querySelectorAll('.btn-editar').forEach(function(boton) {
                boton.addEventListener('click', function() {
                    document.getElementById('editar-id').value = this.dataset.id;
                    document.getElementById('editar-nombre').value = this.dataset.nombre;
                    document.getElementById('editar-apellidopaterno').value = this.dataset.apellidopaterno;
                    document.getElementById('editar-apellidomaterno').value = this.dataset.apellidomaterno;
                    document.getElementById('editar-username').value = this.dataset.username;
                    document.getElementById('editar-rol').value = this.dataset.rol;

                    const estatusSelect = document.getElementById('editar-estatus');
                    if (estatusSelect) {
                        estatusSelect.value = this.dataset.estatus;
                    }
                });
            });
        });

        <?php if (!empty($alert)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: <?= json_encode($alert['title'] ?? 'Notificación') ?>,
                    <?php if (!empty($alert['message'])): ?>
                    text: <?= json_encode($alert['message']) ?>,
                    <?php endif; ?>
                    icon: <?= json_encode($alert['icon'] ?? 'info') ?>,
                    confirmButtonText: 'OK'
                });
            });
        <?php endif; ?>
    </script>
</body>

</html>
