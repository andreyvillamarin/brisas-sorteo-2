<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require 'includes/db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- LÓGICA DEL SWITCH PARA MANEJAR ACCIONES ---
switch ($action) {
    case 'get_registros':
        getRegistros($pdo);
        break;
    case 'add_punto_venta':
        addPuntoVenta($pdo);
        break;
    case 'get_puntos_venta':
        getPuntosVenta($pdo);
        break;
    case 'delete_punto_venta':
        deletePuntoVenta($pdo);
        break;
    case 'get_reporte':
        getReporte($pdo);
        break;
    case 'get_participantes':
        getParticipantes($pdo);
        break;
    case 'guardar_ganador':
        guardarGanador($pdo);
        break;
    case 'get_ganadores':
        getGanadores($pdo);
        break;
    case 'get_analiticas':
        getAnaliticas($pdo);
        break;
    case 'save_config':
        saveConfig($pdo);
        break;
    case 'get_admins':
        getAdmins($pdo);
        break;
    case 'add_admin':
        addAdmin($pdo);
        break;
    case 'delete_admin':
        deleteAdmin($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
}

// --- FUNCIONES ---

function getRegistros($pdo) {
    $date = $_GET['date'] ?? date('Y-m-d');
    $sql = "
        SELECT r.id, r.nombre_completo, r.cedula, r.valor_compra, r.archivo_adjunto, pv.nombre AS punto_venta, r.fecha_registro, pv.valor_minimo_compra
        FROM registros r
        JOIN puntos_venta pv ON r.id_punto_venta = pv.id
        WHERE DATE(r.fecha_registro) = ?
        ORDER BY r.fecha_registro DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    $registros = $stmt->fetchAll();

    // Calcular oportunidades para cada registro individual
    foreach ($registros as &$reg) {
        $reg['oportunidades'] = $reg['valor_minimo_compra'] > 0 ? floor($reg['valor_compra'] / $reg['valor_minimo_compra']) : 0;
    }

    echo json_encode(['success' => true, 'data' => $registros]);
}

function addPuntoVenta($pdo) {
    $nombre = trim($_POST['nombre']);
    $valor_minimo = $_POST['valor_minimo'];
    if (empty($nombre) || !is_numeric($valor_minimo)) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        return;
    }
    $stmt = $pdo->prepare("INSERT INTO puntos_venta (nombre, valor_minimo_compra) VALUES (?, ?)");
    $stmt->execute([$nombre, $valor_minimo]);
    echo json_encode(['success' => true]);
}

function getPuntosVenta($pdo) {
    $stmt = $pdo->query("SELECT * FROM puntos_venta ORDER BY nombre");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}

function deletePuntoVenta($pdo) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM puntos_venta WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}

function getReporte($pdo) {
    $pv_id = $_GET['pv_id'];
    $inicio = $_GET['inicio'];
    $fin = $_GET['fin'];

    $sql = "
        SELECT 
            r.nombre_completo, r.cedula, r.email,
            SUM(r.valor_compra) as total_compras,
            pv.valor_minimo_compra
        FROM registros r
        JOIN puntos_venta pv ON r.id_punto_venta = pv.id
        WHERE 1=1
    ";
    $params = [];
    if (!empty($pv_id)) { $sql .= " AND r.id_punto_venta = ?"; $params[] = $pv_id; }
    if (!empty($inicio)) { $sql .= " AND DATE(r.fecha_registro) >= ?"; $params[] = $inicio; }
    if (!empty($fin)) { $sql .= " AND DATE(r.fecha_registro) <= ?"; $params[] = $fin; }
    $sql .= " GROUP BY r.cedula, r.nombre_completo, r.email, pv.valor_minimo_compra ORDER BY total_compras DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    foreach ($data as &$row) {
        $row['oportunidades'] = $row['valor_minimo_compra'] > 0 ? floor($row['total_compras'] / $row['valor_minimo_compra']) : 0;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getParticipantes($pdo) {
    $pv_id = $_GET['pv_id'];
    $inicio = $_GET['inicio'];
    $fin = $_GET['fin'];

    $sql = "
        SELECT r.cedula, r.nombre_completo, SUM(r.valor_compra) as total_compras, pv.valor_minimo_compra
        FROM registros r
        JOIN puntos_venta pv ON r.id_punto_venta = pv.id
        WHERE 1=1
    ";
    $params = [];
    if (!empty($pv_id)) { $sql .= " AND r.id_punto_venta = ?"; $params[] = $pv_id; }
    if (!empty($inicio)) { $sql .= " AND DATE(r.fecha_registro) >= ?"; $params[] = $inicio; }
    if (!empty($fin)) { $sql .= " AND DATE(r.fecha_registro) <= ?"; $params[] = $fin; }
    $sql .= " GROUP BY r.cedula, r.nombre_completo, pv.valor_minimo_compra";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();

    $participantes = [];
    foreach ($usuarios as $usuario) {
        if ($usuario['valor_minimo_compra'] > 0) {
            $oportunidades = floor($usuario['total_compras'] / $usuario['valor_minimo_compra']);
            for ($i = 0; $i < $oportunidades; $i++) {
                $participantes[] = [
                    'nombre' => $usuario['nombre_completo'],
                    'cedula' => $usuario['cedula']
                ];
            }
        }
    }
    echo json_encode(['success' => true, 'data' => $participantes]);
}

function guardarGanador($pdo) {
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $premio = $_POST['premio'];
    
    // Obtener email del ganador
    $stmt_email = $pdo->prepare("SELECT email FROM registros WHERE cedula = ? ORDER BY fecha_registro DESC LIMIT 1");
    $stmt_email->execute([$cedula]);
    $email = $stmt_email->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO ganadores (nombre_completo, cedula, email, premio) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $cedula, $email, $premio]);
    echo json_encode(['success' => true]);
}

function getGanadores($pdo) {
    $stmt = $pdo->query("SELECT * FROM ganadores ORDER BY fecha_sorteo DESC");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}

function getAnaliticas($pdo) {
    // Top 10 Clientes
    $stmt_clientes = $pdo->query("
        SELECT nombre_completo, cedula, SUM(valor_compra) AS total
        FROM registros
        GROUP BY cedula, nombre_completo
        ORDER BY total DESC
        LIMIT 10
    ");
    $top_clientes = $stmt_clientes->fetchAll();

    // Top Puntos de Venta
    $stmt_pdv = $pdo->query("
        SELECT pv.nombre, SUM(r.valor_compra) AS total
        FROM registros r
        JOIN puntos_venta pv ON r.id_punto_venta = pv.id
        GROUP BY pv.nombre
        ORDER BY total DESC
    ");
    $top_pdv = $stmt_pdv->fetchAll();

    echo json_encode(['success' => true, 'top_clientes' => $top_clientes, 'top_pdv' => $top_pdv]);
}

function saveConfig($pdo) {
    foreach ($_POST as $clave => $valor) {
        $stmt = $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
        $stmt->execute([trim($valor), $clave]);
    }
    echo json_encode(['success' => true, 'message' => 'Configuración guardada.']);
}

function getAdmins($pdo) {
    $stmt = $pdo->query("SELECT id, usuario FROM administradores");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
}

function addAdmin($pdo) {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO administradores (usuario, password_hash) VALUES (?, ?)");
    try {
        $stmt->execute([$usuario, $hash]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe.']);
    }
}

function deleteAdmin($pdo) {
    // Evitar que se borre el único admin
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM administradores");
    if ($count_stmt->fetchColumn() <= 1) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar al único administrador.']);
        return;
    }
    $id = $_POST['id'];
    // Evitar que un admin se borre a sí mismo (opcional pero recomendado)
    if ($id == $_SESSION['admin_id']) {
        echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.']);
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM administradores WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
}

?>