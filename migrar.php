<?php
// Buscamos las variables en todos los rincones posibles
$host = getenv('MYSQLHOST') ?: $_ENV['MYSQLHOST'] ?? 'localhost';
$port = getenv('MYSQLPORT') ?: $_ENV['MYSQLPORT'] ?? '3306';
$user = getenv('MYSQLUSER') ?: $_ENV['MYSQLUSER'] ?? 'root';
$pass = getenv('MYSQLPASSWORD') ?: $_ENV['MYSQLPASSWORD'] ?? '';

// ¡EL FIX ESTÁ AQUÍ! Forzamos el nombre de la nube
$dbname = 'railway'; 
if ($host === 'localhost') {
    $dbname = 'sistema_asistencia'; // Solo usa este nombre si estás en XAMPP
}

$mysqli = new mysqli($host, $user, $pass, $dbname, $port);

if ($mysqli->connect_error) {
    die("<h1 style='color:red; text-align:center; margin-top:50px;'>Error de conexión: " . $mysqli->connect_error . "</h1>");
}

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red; text-align:center;'>Error: No encuentro el archivo $archivo_sql</h1>");
}

$sql = file_get_contents($archivo_sql);

// Limpiamos la basura que deja phpMyAdmin
$sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
$sql = preg_replace('/USE[^;]+;/i', '', $sql);

if ($mysqli->multi_query($sql)) {
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