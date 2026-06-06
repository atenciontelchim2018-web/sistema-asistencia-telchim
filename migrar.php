<?php
// 1. DATOS EXACTOS DE TU RAILWAY (Sacados de tu captura)
$host = 'autorack.proxy.rlwy.net';
$port = '58410';
$user = 'root';
$dbname = 'railway';

// 2. LA LLAVE MAESTRA: Pega la contraseña que copiaste entre las comillas
$pass = 'hfZTZytMHrnHfCrvJjxAFIdffLSFWZkM'; 

// 3. Conexión directa
$mysqli = new mysqli($host, $user, $pass, $dbname, $port);

if ($mysqli->connect_error) {
    die("<h1 style='color:red; text-align:center; margin-top:50px;'>Error de conexion: " . $mysqli->connect_error . "</h1>");
}

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red; text-align:center;'>Error: No encuentro el archivo $archivo_sql</h1>");
}

$sql = file_get_contents($archivo_sql);

// 4. Limpiamos basura del archivo local
$lineas = explode("\n", $sql);
$sql_limpio = "";
foreach ($lineas as $linea) {
    $linea_limpia = trim($linea);
    if (stripos($linea_limpia, 'CREATE DATABASE') === 0) continue;
    if (stripos($linea_limpia, 'USE ') === 0) continue;
    $sql_limpio .= $linea . "\n";
}

// 5. Inyectamos las tablas
if ($mysqli->multi_query($sql_limpio)) {
    echo "<div style='background-color:#d1fae5; padding:40px; border-radius:10px; max-width:500px; margin:50px auto; text-align:center; font-family:sans-serif; border: 2px solid #10b981;'>";
    echo "<h1 style='color:#047857; margin-top:0;'>¡MIGRACIÓN EXITOSA! 🚀</h1>";
    echo "<p style='color:#065f46; font-size:18px;'>Tu base de datos se inyectó perfectamente en la nube.</p>";
    echo "<br><a href='index.php' style='background:#10b981; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold; font-size:16px;'>Ir al Sistema</a>";
    echo "</div>";
} else {
    echo "<h1 style='color:red; text-align:center;'>Error SQL</h1>";
    echo "<p style='text-align:center;'>" . $mysqli->error . "</p>";
}

$mysqli->close();
?>