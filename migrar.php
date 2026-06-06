<?php
require_once 'config/db.php';

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red;'>Error: No se encuentra el archivo $archivo_sql</h1>");
}

try {
    // 1. Leer todo el archivo SQL
    $sql = file_get_contents($archivo_sql);
    
    // 2. Limpiar comentarios y configuraciones extrañas de phpMyAdmin
    $sql = preg_replace('/--.*$/m', '', $sql); // Quita comentarios con --
    $sql = preg_replace('/^\/\*.*\*\//m', '', $sql); // Quita comentarios con /* */
    
    // 3. Separar cada tabla o instrucción por el punto y coma (;)
    $consultas = explode(';', $sql);
    
    $exito = 0;
    $errores = [];

    // 4. Ejecutar una por una
    foreach ($consultas as $consulta) {
        $consulta = trim($consulta);
        if (!empty($consulta)) {
            try {
                $pdo->exec($consulta);
                $exito++;
            } catch (PDOException $e) {
                // Si hay un error, lo guardamos pero no detenemos el proceso
                $errores[] = $e->getMessage();
            }
        }
    }

    // 5. Mostrar resultados
    if (count($errores) == 0) {
        echo "<h1 style='color:green; font-family:sans-serif; text-align:center; margin-top:50px;'>¡MIGRACIÓN EXITOSA!</h1>";
        echo "<p style='text-align:center; color:#555;'>Se ejecutaron $exito consultas correctamente.</p>";
    } else {
        echo "<h1 style='color:orange; font-family:sans-serif; text-align:center; margin-top:50px;'>Migración completada con advertencias</h1>";
        echo "<p style='text-align:center;'>Se ejecutaron $exito consultas, pero se ignoraron " . count($errores) . " detalles (normalmente son tablas que ya se habían creado en el intento anterior).</p>";
    }
    
} catch (Exception $e) {
    echo "<h1 style='color:red; text-align:center;'>Error General</h1>";
    echo "<p style='text-align:center;'>Detalle: " . $e->getMessage() . "</p>";
}
?>