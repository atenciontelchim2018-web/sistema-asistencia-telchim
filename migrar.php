<?php
require_once 'config/db.php';

// Nombre del archivo SQL que acabas de mover
$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("Error: No se encuentra el archivo $archivo_sql en la carpeta principal del servidor.");
}

try {
    // Leer el contenido del archivo SQL
    $query = file_get_contents($archivo_sql);
    
    // Ejecutar el SQL en la base de datos de Railway
    $pdo->exec($query);
    echo "<h1 style='color:green; font-family:sans-serif; text-align:center; margin-top:50px;'>¡MIGRACIÓN EXITOSA!</h1>";
    echo "<p style='text-align:center; color:#555;'>Todas las tablas e información se crearon correctamente en Railway.</p>";
} catch (PDOException $e) {
    echo "<h1 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>Error al migrar</h1>";
    echo "<p style='text-align:center; color:#555;'>Detalle: " . $e->getMessage() . "</p>";
}
?>