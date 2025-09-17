<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h2>Acceso de Administrador</h2>
        <?php if(isset($_GET['error'])): ?>
            <p class="error-msg">Usuario o contraseña incorrectos.</p>
        <?php endif; ?>
        <form action="auth.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" name="usuario" id="usuario" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>
            <input type="hidden" name="action" value="login">
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>