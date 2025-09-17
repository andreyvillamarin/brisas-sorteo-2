<?php
header('Content-Type: application/json');
require 'admin/includes/db.php';

// --- CONFIGURACIÓN ---
$upload_dir = 'uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_file_size = 5 * 1024 * 1024; // 5 MB

// --- OBTENER CLAVE SECRETA RECAPTCHA ---
$stmt_config = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'recaptcha_secret_key'");
$recaptcha_secret_key = $stmt_config->fetchColumn();

// --- VALIDACIÓN RECAPTCHA ---
if (empty($_POST['recaptcha_response'])) {
    echo json_encode(['success' => false, 'message' => 'Error de validación reCAPTCHA.']);
    exit;
}

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_data = [
    'secret'   => $recaptcha_secret_key,
    'response' => $_POST['recaptcha_response'],
    'remoteip' => $_SERVER['REMOTE_ADDR'],
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($recaptcha_data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($recaptcha_url, false, $context);
$response_keys = json_decode($result, true);

if (!$response_keys['success'] || $response_keys['score'] < 0.5) { // Umbral de 0.5
    echo json_encode(['success' => false, 'message' => 'Verificación fallida. Pareces ser un robot.']);
    exit;
}

// --- VALIDACIÓN DE DATOS ---
$errors = [];
$nombre_completo = trim($_POST['nombre_completo'] ?? '');
$cedula = preg_replace('/[^0-9]/', '', $_POST['cedula'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$id_punto_venta = filter_var($_POST['id_punto_venta'] ?? '', FILTER_VALIDATE_INT);
$ciudad = trim($_POST['ciudad'] ?? '');
$valor_compra = preg_replace('/[^0-9]/', '', $_POST['valor_compra'] ?? '');

if (empty($nombre_completo)) $errors[] = 'El nombre es obligatorio.';
if (empty($cedula)) $errors[] = 'La cédula es obligatoria.';
if (!$email) $errors[] = 'El correo electrónico no es válido.';
if (!$id_punto_venta) $errors[] = 'Debes seleccionar un punto de venta.';
if (empty($ciudad)) $errors[] = 'La ciudad es obligatoria.';
if (!is_numeric($valor_compra) || $valor_compra <= 0) $errors[] = 'El valor de compra debe ser un número positivo.';

// Validar valor mínimo
if ($id_punto_venta) {
    $stmt = $pdo->prepare("SELECT valor_minimo_compra FROM puntos_venta WHERE id = ?");
    $stmt->execute([$id_punto_venta]);
    $valor_minimo = $stmt->fetchColumn();
    if ($valor_compra < $valor_minimo) {
        $errors[] = 'El valor de la compra es inferior al mínimo requerido de $' . number_format($valor_minimo, 0, ',', '.') . ' para este punto de venta.';
    }
}

// --- VALIDACIÓN DE ARCHIVO ---
if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['adjunto']['size'] > $max_file_size) {
        $errors[] = 'El archivo es demasiado grande. Máximo 5 MB.';
    }
    if (!in_array($_FILES['adjunto']['type'], $allowed_types)) {
        $errors[] = 'Tipo de archivo no permitido. Sube una imagen (JPG, PNG).';
    }
} else {
    $errors[] = 'Es obligatorio adjuntar la factura.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
    exit;
}

// --- PROCESAR SUBIDA DE ARCHIVO ---
$file_extension = pathinfo($_FILES['adjunto']['name'], PATHINFO_EXTENSION);
$new_filename = 'factura_' . $cedula . '_' . time() . '.' . $file_extension;
$destination = $upload_dir . $new_filename;

if (!move_uploaded_file($_FILES['adjunto']['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo adjunto.']);
    exit;
}

// --- GUARDAR EN BASE DE DATOS ---
try {
    $sql = "INSERT INTO registros (nombre_completo, cedula, email, id_punto_venta, ciudad, valor_compra, archivo_adjunto) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre_completo, $cedula, $email, $id_punto_venta, $ciudad, $valor_compra, $new_filename]);
    
    // --- CALCULAR OPORTUNIDADES TOTALES (LÓGICA REFACTORIZADA) ---
    // Se obtiene el historial de compras y se procesa en PHP para evitar consultas lentas con GROUP BY.
    $sql_historial = "
        SELECT r.valor_compra, pv.valor_minimo_compra
        FROM registros r
        JOIN puntos_venta pv ON r.id_punto_venta = pv.id
        WHERE r.cedula = ?
    ";
    $stmt_historial = $pdo->prepare($sql_historial);
    $stmt_historial->execute([$cedula]);
    $historial_compras = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar compras por el valor mínimo requerido
    $compras_agrupadas = [];
    foreach ($historial_compras as $compra) {
        $minimo = (int)$compra['valor_minimo_compra'];
        if ($minimo > 0) {
            if (!isset($compras_agrupadas[$minimo])) {
                $compras_agrupadas[$minimo] = 0;
            }
            $compras_agrupadas[$minimo] += $compra['valor_compra'];
        }
    }

    // Calcular oportunidades totales
    $oportunidades = 0;
    foreach ($compras_agrupadas as $minimo => $total) {
        $oportunidades += floor($total / $minimo);
    }

    echo json_encode([
        'success' => true,
        'nombre' => htmlspecialchars($nombre_completo),
        'cedula' => 'C.C. ' . htmlspecialchars($cedula),
        'factura_info' => 'Factura agregada por $' . number_format($valor_compra, 0, ',', '.'),
        'oportunidades' => $oportunidades
    ]);

} catch (PDOException $e) {
    // En producción, loguea el error en lugar de mostrarlo
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>