<?php
// Asegurarse de que el script no sea accedido directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Acceso directo no permitido.');
}

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<h1>Error</h1><p>ID de punto de venta no válido.</p>";
    return;
}

$pdv_id = $_GET['id'];

// Lógica para manejar el envío del formulario (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear los datos del formulario
    $nombre = trim($_POST['nombre']);
    $valor_minimo = preg_replace('/[^0-9]/', '', $_POST['valor_minimo_compra']);

    // Validar los datos
    if ($nombre && is_numeric($valor_minimo)) {
        // Preparar y ejecutar la consulta de actualización
        $sql = "UPDATE puntos_venta SET nombre = ?, valor_minimo_compra = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$nombre, $valor_minimo, $pdv_id])) {
            // Redirigir a la lista de puntos de venta después de la actualización exitosa
            echo '<p class="success-msg">Punto de venta actualizado con éxito. Redirigiendo...</p>';
            echo '<script>setTimeout(() => { window.location.href = "?page=puntos_venta"; }, 2000);</script>';
            return; // Detener la ejecución para no mostrar el formulario de nuevo
        } else {
            echo '<p class="error-msg">Error al actualizar el punto de venta.</p>';
        }
    } else {
        echo '<p class="error-msg">Por favor, complete todos los campos correctamente.</p>';
    }
}

// Lógica para obtener los datos del punto de venta para mostrarlos en el formulario (GET)
$stmt = $pdo->prepare("SELECT * FROM puntos_venta WHERE id = ?");
$stmt->execute([$pdv_id]);
$punto_venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$punto_venta) {
    echo "<h1>Error</h1><p>No se encontró el punto de venta con ID " . htmlspecialchars($pdv_id) . ".</p>";
    return;
}
?>

<div class="header">
    <h1>Editar Punto de Venta</h1>
    <p>Modificando: <?php echo htmlspecialchars($punto_venta['nombre']); ?></p>
</div>

<div class="card">
    <div class="card-content">
        <form action="?page=editar_punto_venta&id=<?php echo $pdv_id; ?>" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre del Punto de Venta</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($punto_venta['nombre']); ?>" required>
            </div>

            <div class="form-group">
                <label for="valor_minimo_compra">Valor Mínimo de Compra</label>
                <input type="text" id="valor_minimo_compra" name="valor_minimo_compra" value="<?php echo htmlspecialchars((int)$punto_venta['valor_minimo_compra']); ?>" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Guardar Cambios</button>
                <a href="?page=puntos_venta" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
