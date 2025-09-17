<?php
// Asegurarse de que el script no sea accedido directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Acceso directo no permitido.');
}

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Error</h1><p>ID de registro no válido.</p>";
    return;
}

$registro_id = $_GET['id'];

// Lógica para manejar el envío del formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';

    if ($action === 'delete') {
        // Lógica para eliminar el registro
        $sql = "DELETE FROM registros WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$registro_id])) {
            echo '<p class="success-msg">Registro eliminado con éxito. Redirigiendo...</p>';
            echo '<script>setTimeout(() => { window.location.href = "?page=dashboard"; }, 2000);</script>';
            return;
        } else {
            echo '<p class="error-msg">Error al eliminar el registro.</p>';
        }
    } elseif ($action === 'update') {
        // Lógica para actualizar el registro (código existente)
        $nombre_completo = trim($_POST['nombre_completo']);
        $cedula = preg_replace('/[^0-9]/', '', $_POST['cedula']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $id_punto_venta = filter_var($_POST['id_punto_venta'], FILTER_VALIDATE_INT);
        $ciudad = trim($_POST['ciudad']);
        $valor_compra = preg_replace('/[^0-9]/', '', $_POST['valor_compra']);

        if ($nombre_completo && $cedula && $email && $id_punto_venta && $ciudad && is_numeric($valor_compra)) {
            $sql = "UPDATE registros SET
                        nombre_completo = ?,
                        cedula = ?,
                        email = ?,
                        id_punto_venta = ?,
                        ciudad = ?,
                        valor_compra = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$nombre_completo, $cedula, $email, $id_punto_venta, $ciudad, $valor_compra, $registro_id])) {
                echo '<p class="success-msg">Registro actualizado con éxito. Redirigiendo...</p>';
                echo '<script>setTimeout(() => { window.location.href = "?page=dashboard"; }, 2000);</script>';
                return;
            } else {
                echo '<p class="error-msg">Error al actualizar el registro.</p>';
            }
        } else {
            echo '<p class="error-msg">Por favor, complete todos los campos correctamente.</p>';
        }
    }
}

// Lógica para obtener los datos del registro para mostrarlos en el formulario (GET)
$stmt = $pdo->prepare("SELECT * FROM registros WHERE id = ?");
$stmt->execute([$registro_id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    echo "<h1>Error</h1><p>No se encontró el registro con ID " . htmlspecialchars($registro_id) . ".</p>";
    return;
}

// Obtener la lista de puntos de venta para el selector
$stmt_pdv = $pdo->query("SELECT id, nombre FROM puntos_venta ORDER BY nombre");
$puntos_venta = $stmt_pdv->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="header">
    <h1>Editar Registro</h1>
    <p>Modificando el registro de: <?php echo htmlspecialchars($registro['nombre_completo']); ?></p>
</div>

<div class="card">
    <div class="card-content">
        <form action="?page=editar_registro&id=<?php echo $registro_id; ?>" method="POST">
            <div class="form-group">
                <label for="nombre_completo">Nombre Completo</label>
                <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($registro['nombre_completo']); ?>" required>
            </div>

            <div class="form-group">
                <label for="cedula">Cédula</label>
                <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($registro['cedula']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($registro['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="id_punto_venta">Punto de Venta</label>
                <select id="id_punto_venta" name="id_punto_venta" required>
                    <option value="" disabled>Selecciona un punto de venta</option>
                    <?php foreach ($puntos_venta as $pv): ?>
                    <option value="<?php echo $pv['id']; ?>" <?php echo ($pv['id'] == $registro['id_punto_venta']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($pv['nombre']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($registro['ciudad']); ?>" required>
            </div>

            <div class="form-group">
                <label for="valor_compra">Valor de la Compra</label>
                <input type="text" id="valor_compra" name="valor_compra" value="<?php echo htmlspecialchars((int)$registro['valor_compra']); ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" name="action" value="update" class="btn-primary">Guardar Cambios</button>
                <button type="submit" name="action" value="delete" class="btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.');">Eliminar Registro</button>
                <a href="?page=dashboard" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
