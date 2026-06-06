<?php
require_once 'config/db.php';

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red; text-align:center;'>Error: No encuentro el archivo $archivo_sql</h1>");
}

try {
    // 1. Activar el búfer de consultas (ESTA ES LA SOLUCIÓN AL ERROR 2014)
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

    // 2. Leer todo el archivo SQL
    $sql = file_get_contents($archivo_sql);
    
    // 3. Limpiar comandos locales de XAMPP que bloquean la nube
    $sql = preg_replace('/CREATE DATABASE.*/i', '', $sql);
    $sql = preg_replace('/USE .*/i', '', $sql);

    // 4. Ejecutar todo de un solo golpe
    $pdo->exec($sql);

    echo "<div style='background-color:#d1fae5; border-radius:10px; padding:30px; max-width:600px; margin:50px auto; border: 2px solid #10b981;'>";
    echo "<h1 style='color:#047857; font-family:sans-serif; text-align:center; margin:0;'>¡MIGRACIÓN EXITOSA! 🚀</h1>";
    echo "<p style='text-align:center; color:#065f46; font-size:18px;'>Todas tus tablas se han subido correctamente a Railway.</p>";
    echo "<p style='text-align:center;'><a href='index.php' style='background:#10b981; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; font-family:sans-serif; font-weight:bold;'>Ir al Sistema</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color:#fee2e2; border-radius:10px; padding:30px; max-width:600px; margin:50px auto; border: 2px solid #ef4444;'>";
    echo "<h1 style='color:#b91c1c; font-family:sans-serif; text-align:center; margin:0;'>Error de Migración</h1>";
    echo "<p style='text-align:center; color:#7f1d1d;'>Detalle: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>