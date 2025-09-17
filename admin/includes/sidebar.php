<?php
$current_page = basename($_GET['page'] ?? 'dashboard');
$menu_items = [
    'dashboard' => ['icon' => 'fa-tachometer-alt', 'text' => 'Dashboard'],
    'puntos_venta' => ['icon' => 'fa-store', 'text' => 'Puntos de Venta'],
    'reportes' => ['icon' => 'fa-chart-bar', 'text' => 'Reportes'],
    'sorteo' => ['icon' => 'fa-trophy', 'text' => 'Sorteo'],
    'ganadores' => ['icon' => 'fa-star', 'text' => 'Ganadores'],
    'analitica' => ['icon' => 'fa-chart-pie', 'text' => 'Analítica'],
    'configuracion' => ['icon' => 'fa-cog', 'text' => 'Configuración'],
    'usuarios' => ['icon' => 'fa-users-cog', 'text' => 'Usuarios Admin'],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="<?php echo $baseURL; ?>assets/img/logo.png" alt="Logo" class="sidebar-logo">
    </div>
    <ul class="sidebar-menu">
        <?php foreach ($menu_items as $page_key => $item): ?>
        <li class="<?php echo ($current_page === $page_key) ? 'active' : ''; ?>">
            <a href="dashboard.php?page=<?php echo $page_key; ?>">
                <i class="fas <?php echo $item['icon']; ?> icon"></i>
                <span class="text"><?php echo $item['text']; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <div class="sidebar-footer">
        <ul class="sidebar-menu">
            <li>
                <a href="auth.php?action=logout">
                    <i class="fas fa-sign-out-alt icon"></i>
                    <span class="text">Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </div>
</aside>