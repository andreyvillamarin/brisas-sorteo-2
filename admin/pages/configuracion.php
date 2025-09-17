<?php
// Obtener configuraci車n actual
$stmt = $pdo->query("SELECT * FROM configuracion");
$config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div class="header">
    <h1>Configuración General</h1>
</div>
<div class="card">
    <form id="configForm">
        <h3>Texto de Introducción (Frontend)</h3>
        <div id="editor" style="min-height: 150px; border: 1px solid #ccc; border-radius: 5px;"><?php echo $config['texto_introduccion'] ?? ''; ?></div>
        <input type="hidden" name="texto_introduccion" id="texto_introduccion_hidden">
        
        <h3 style="margin-top: 20px;">Google reCAPTCHA v3</h3>
        <div class="form-group">
            <label for="recaptcha_site_key">Site Key (Clave del sitio)</label>
            <input type="text" name="recaptcha_site_key" value="<?php echo htmlspecialchars($config['recaptcha_site_key']); ?>">
        </div>
        <div class="form-group">
            <label for="recaptcha_secret_key">Secret Key (Clave secreta)</label>
            <input type="text" name="recaptcha_secret_key" value="<?php echo htmlspecialchars($config['recaptcha_secret_key']); ?>">
        </div>
        <button type="submit">Guardar Cambios</button>
    </form>
    <div id="configMessage"></div>
</div>