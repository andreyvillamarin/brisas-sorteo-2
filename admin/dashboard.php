<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Usamos __DIR__ para una ruta segura y absoluta al archivo de la base de datos.
require __DIR__ . '/includes/db.php';

// Determina la página a mostrar, por defecto el dashboard.
$page = $_GET['page'] ?? 'dashboard';

// Lista de páginas permitidas para mayor seguridad.
$allowed_pages = ['dashboard', 'puntos_venta', 'reportes', 'sorteo', 'ganadores', 'analitica', 'configuracion', 'usuarios', 'editar_registro', 'editar_punto_venta'];

// Si la página solicitada no está en la lista, se redirige al dashboard.
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Incluimos los archivos de la plantilla usando rutas absolutas del servidor.
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content-wrapper">
    <header class="top-bar">
        <button id="sidebar-toggle"><i class="fas fa-bars"></i></button>
        <div class="user-info">
            <span>Hola, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></span>
        </div>
    </header>
    <main id="main-content">
        <?php
        // Construimos la ruta absoluta a la página de contenido.
        $page_path = __DIR__ . "/pages/{$page}.php";

        // Verificamos si el archivo existe antes de incluirlo.
        if (file_exists($page_path)) {
            include $page_path;
        } else {
            echo "<h1>Error: Página no encontrada.</h1><p>El archivo <code>" . htmlspecialchars($page_path) . "</code> no existe.</p>";
        }
        ?>
    </main>
</div>

<?php
// Incluimos el pie de página.
include __DIR__ . '/includes/footer.php';
?>