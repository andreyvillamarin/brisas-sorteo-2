<?php
// Esta variable nos ayudará a construir las rutas correctamente.
$baseURL = '/demos/brisas-sorteo/';
$current_page = basename($_GET['page'] ?? 'dashboard');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sorteo</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if ($current_page === 'configuracion'): ?>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo $baseURL; ?>admin/assets/css/admin-style.css">
    <link rel="stylesheet" href="<?php echo $baseURL; ?>admin/assets/css/custom-style.css">
</head>
<body>
<div class="admin-wrapper">