<?php
// 1. LA LLAVE MAESTRA: Pega tu contraseña de Railway entre las comillas
$pass = 'hfZTZytMHrnHfCrvJjxAFIdffLSFWZkM'; 

// 2. Intentamos jalar los demás datos del servidor
$host = $_SERVER['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'localhost';
$port = $_SERVER['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '3306';
$user = $_SERVER['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root';
$dbname = 'railway';

// Si estás probando en tu PC (XAMPP), ajustamos automático
if ($host === 'localhost') {
    $dbname = 'sistema_asistencia';
    $pass = ''; 
}

$mysqli = new mysqli($host, $user, $pass, '', $port);

if ($mysqli->connect_error) {
    die("<h1 style='color:red;'>Error de conexion: " . $mysqli->connect_error . "</h1>");
}

$mysqli->select_db($dbname);

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red;'>Error: No encuentro el archivo $archivo_sql</h1>");
}

$sql = file_get_contents($archivo_sql);

// Limpiamos los comandos basura de phpMyAdmin
$lineas = explode("\n", $sql);
$sql_limpio = "";
foreach ($lineas as $linea) {
    $linea_limpia = trim($linea);
    if (stripos($linea_limpia, 'CREATE DATABASE') === 0) continue;
    if (stripos($linea_limpia, 'USE ') === 0) continue;
    $sql_limpio .= $linea . "\n";
}

// Inyectamos todas las tablas
if ($mysqli->multi_query($sql_limpio)) {
    echo "<div style='background-color:#d1fae5; padding:30px; border-radius:10px; max-width:500px; margin:50px auto; text-align:center; font-family:sans-serif;'>";
    echo "<h1 style='color:#047857;'>¡MIGRACIÓN EXITOSA! 🚀</h1>";
    echo "<p style='color:#065f46;'>Tu base de datos se inyectó perfectamente.</p>";
    echo "<a href='index.php' style='background:#10b981; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Ir al Sistema</a>";
    echo "</div>";
} else {
    echo "<h1 style='color:red; text-align:center;'>Error SQL</h1>";
    echo "<p style='text-align:center;'>" . $mysqli->error . "</p>";
}

$mysqli->close();
?>