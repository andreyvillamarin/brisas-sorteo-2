<?php

$db_host = 'localhost';
$db_name = 'qdosnetw_brisasorteo';
$db_user = 'qdosnetw_webmaster';
$db_pass = 'tRVy8pvXVAz8'; // 

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En un entorno de producción, no muestres errores detallados.
    // Solo registra el error en un archivo de log.
    die("Error: No se pudo conectar a la base de datos. " . $e->getMessage());
}
?>