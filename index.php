<?php
require 'admin/includes/db.php';

// Obtener configuración
$stmt_config = $pdo->query("SELECT * FROM configuracion");
$config = $stmt_config->fetchAll(PDO::FETCH_KEY_PAIR);

// Obtener puntos de venta
$stmt_pdv = $pdo->query("SELECT id, nombre, valor_minimo_compra FROM puntos_venta ORDER BY nombre");
$puntos_venta = $stmt_pdv->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorteo Brisas - Participa Ahora</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <img src="assets/img/logo.png" alt="Logo de la Empresa" class="logo">
        </header>
        
        <main>
            <div class="intro-text"><?php echo $config['texto_introduccion'] ?? ''; ?></div>
            
            <form id="sorteo-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required>
                </div>
                
                <div class="form-group">
                    <label for="cedula">Cédula (sin puntos ni comas)</label>
                    <input type="text" id="cedula" name="cedula" inputmode="numeric" pattern="[0-9]*" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="id_punto_venta">Punto de Venta</label>
                    <select id="id_punto_venta" name="id_punto_venta" required>
                        <option value="" disabled selected>Selecciona un punto de venta</option>
                        <?php foreach ($puntos_venta as $pv): ?>
                        <option value="<?php echo $pv['id']; ?>" data-minimo="<?php echo $pv['valor_minimo_compra']; ?>">
                            <?php echo htmlspecialchars($pv['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" required>
                </div>

                <div class="form-group">
                    <label for="valor_compra">Valor de la Compra (sin puntos)</label>
                    <input type="text" id="valor_compra" name="valor_compra" inputmode="numeric" pattern="[0-9]*" required>
                    <small id="valor-minimo-info" class="form-text-info" style="display:none;"></small>
                </div>

                <div class="form-group">
                    <label for="adjunto">Adjunta tu Factura (Foto)</label>
                    <input type="file" id="adjunto" name="adjunto" accept="image/*" capture="environment" required>
                </div>
                
                <input type="hidden" name="recaptcha_response" id="recaptcha_response">
                
                <button type="submit" id="submit-btn">
                    <span class="btn-text">Participar</span>
                    <span class="loader" style="display: none;"></span>
                </button>
            </form>
        </main>
    </div>

    <div id="success-popup" class="popup-overlay">
        <div class="popup-content">
            <span class="popup-close">&times;</span>
            <div id="popup-info">
                <h2 id="popup-nombre"></h2>
                <p id="popup-cedula"></p>
                <p id="popup-factura"></p>
                <p class="oportunidades-texto">Tienes un total de</p>
                <div class="oportunidades-circulo">
                    <span id="popup-oportunidades"></span>
                </div>
                <p class="oportunidades-texto-small">Oportunidades para ganar</p>
            </div>
        </div>
    </div>
    
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>"></script>
    <script>
        // Pasamos datos de PHP a JS
        const puntosVenta = <?php echo json_encode($puntos_venta); ?>;
        const recaptchaSiteKey = '<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>';
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>