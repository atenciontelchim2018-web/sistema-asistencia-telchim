<?php
// 1. Obtener los datos reales de la nube
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';

if ($host === 'localhost') {
    $dbname = 'sistema_asistencia';
}

// 2. Conectar al servidor
$mysqli = new mysqli($host, $user, $pass, '', $port);

if ($mysqli->connect_error) {
    die("<h1 style='color:red;'>Error de conexión: " . $mysqli->connect_error . "</h1>");
}

// 3. Seleccionar la base de datos explícitamente
if (!$mysqli->select_db($dbname)) {
    die("<h1 style='color:red;'>Error: No se encontró la BD '$dbname'. Detalle: " . $mysqli->error . "</h1>");
}

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red;'>Error: No encuentro el archivo $archivo_sql</h1>");
}

$sql = file_get_contents($archivo_sql);

// 4. Limpieza Extrema: Borramos línea por línea la configuración de tu PC
$lineas = explode("\n", $sql);
$sql_limpio = "";
foreach ($lineas as $linea) {
    $linea_limpia = trim($linea);
    if (stripos($linea_limpia, 'CREATE DATABASE') === 0) continue;
    if (stripos($linea_limpia, 'USE ') === 0) continue;
    $sql_limpio .= $linea . "\n";
}

// 5. EL FIX DE ORO: Le ordenamos a MySQL que use tu BD de Railway
$sql_final = "USE `$dbname`;\n" . $sql_limpio;

// 6. Ejecutar todo de un solo golpe
if ($mysqli->multi_query($sql_final)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    
    echo "<div style='background-color:#d1fae5; border-radius:10px; padding:30px; max-width:600px; margin:50px auto; border: 2px solid #10b981; font-family:sans-serif;'>";
    echo "<h1 style='color:#047857; text-align:center; margin:0;'>¡MIGRACIÓN EXITOSA! 🚀</h1>";
    echo "<p style='text-align:center; color:#065f46; font-size:18px;'>Las tablas se inyectaron perfectamente en la nube.</p>";
    echo "<p style='text-align:center;'><a href='index.php' style='background:#10b981; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Ir al Sistema</a></p>";
    echo "</div>";
} else {
    echo "<div style='background-color:#fee2e2; border-radius:10px; padding:30px; max-width:600px; margin:50px auto; border: 2px solid #ef4444; font-family:sans-serif;'>";
    echo "<h1 style='color:#b91c1c; text-align:center; margin:0;'>Error en el código SQL</h1>";
    echo "<p style='text-align:center; color:#7f1d1d;'>Detalle: " . $mysqli->error . "</p>";
    echo "</div>";
}

$mysqli->close();
?>