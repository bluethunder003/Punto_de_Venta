<?php
require_once '../../includes/auth.php';
exigirRol([1]);
require_once '../../config/db.php';

$mensaje = '';
$error = '';

// 1. PROCESAR ACCIONES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar') {
        $id_producto = (int)($_POST['id_producto'] ?? 0);
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = $_POST['precio'] ?? '';
        $stock = $_POST['stock'] ?? '';

        // Validaciones 
        if (empty($codigo) || empty($nombre)) {
            $error = 'El código y el nombre son obligatorios.';
        } elseif (!is_numeric($precio) || (float)$precio < 0) {
            $error = 'El precio debe ser un número válido y no negativo.';
        } elseif (!filter_var($stock, FILTER_VALIDATE_INT) || (int)$stock < 0) {
            $error = 'El stock debe ser un número entero no negativo.';
        } else {
            if ($id_producto > 0) {
                // ACTUALIZACIÓN
                $sql = "UPDATE productos 
                        SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock 
                        WHERE id_producto = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':codigo'      => $codigo,
                    ':nombre'      => $nombre,
                    ':descripcion' => $descripcion,
                    ':precio'      => $precio,
                    ':stock'       => $stock,
                    ':id'          => $id_producto
                ]);
                $mensaje = 'Producto actualizado correctamente.';
            } else {
                // ALTA
                $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, estado) 
                        VALUES (:codigo, :nombre, :descripcion, :precio, :stock, 1)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':codigo'      => $codigo,
                    ':nombre'      => $nombre,
                    ':descripcion' => $descripcion,
                    ':precio'      => $precio,
                    ':stock'       => $stock
                ]);
                $mensaje = 'Producto registrado correctamente.';
            }
        }
    } elseif ($accion === 'eliminar') {
        // BAJA LÓGICA
        $id_producto = (int)($_POST['id_producto'] ?? 0);
        if ($id_producto > 0) {
            $sql = "UPDATE productos SET estado = 0 WHERE id_producto = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_producto]);
            $mensaje = 'Producto desactivado (baja lógica).';
        }
    }
}

// 2. CONSULTAR PRODUCTOS ACTIVOS 
$stmt = $pdo->query("SELECT id_producto, codigo, nombre, descripcion, precio, stock FROM productos WHERE estado = 1 ORDER BY nombre");
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Punto de Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <h2 class="mb-4">Productos</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4"> 
    
        <a href="../dashboard.php" class="btn btn-secondary"> 
            Regresar 
        </a> 
    </div>
        
    <!-- FORMULARIO DE  EDICIÓN -->
    <div class="card mb-4">
        <div class="card-header fw-bold">Registrar / Editar Producto</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id_producto" id="id_producto" value="0">

                <div class="col-md-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" id="codigo" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" id="descripcion" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Precio ($)</label>
                    <input type="number" step="0.01" name="precio" id="precio" class="form-control" min="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" id="stock" class="form-control" min="0" required>
                </div>

                <div class="col-12 text-end">
                    <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">Limpiar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
  
    <div class="d-flex justify-content-between align-items-center mb-4"> 
        <div>
            <p class="text-muted mb-0">
             Catálogo de productos disponibles 
            </p> 
        </div> 
    </div>
    <!-- TABLA DE PRODUCTOS -->
    <div class="card">
        
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$p['id_producto']) ?></td>
                            <td><?= htmlspecialchars($p['codigo']) ?></td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= htmlspecialchars($p['descripcion'] ?? '') ?></td>
                            <td>$<?= number_format((float)$p['precio'], 2) ?></td>
                            <td><?= htmlspecialchars((string)$p['stock']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick='editarProducto(<?= json_encode($p) ?>)'>Editar</button>
                                
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Desactivar este producto?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editarProducto(producto) {
    document.getElementById('id_producto').value = producto.id_producto;
    document.getElementById('codigo').value = producto.codigo;
    document.getElementById('nombre').value = producto.nombre;
    document.getElementById('descripcion').value = producto.descripcion || '';
    document.getElementById('precio').value = producto.precio;
    document.getElementById('stock').value = producto.stock;
}

function limpiarFormulario() {
    document.getElementById('id_producto').value = '0';
    document.getElementById('codigo').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('precio').value = '';
    document.getElementById('stock').value = '';
}
</script>
</body>
</html>