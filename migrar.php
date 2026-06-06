<?php
// Obtener las variables directamente de Railway
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$dbname = getenv('MYSQLDATABASE') ?: 'sistema_asistencia';

// Conectar usando MySQLi (El método súper estable para archivos grandes)
$mysqli = new mysqli($host, $user, $pass, $dbname, $port);

if ($mysqli->connect_error) {
    die("<h1 style='color:red;'>Error de conexión: " . $mysqli->connect_error . "</h1>");
}

$archivo_sql = 'sistema_asistencia.sql'; 

if (!file_exists($archivo_sql)) {
    die("<h1 style='color:red;'>Error: No encuentro el archivo $archivo_sql</h1>");
}

// Leer el archivo SQL
$sql = file_get_contents($archivo_sql);

// Limpiar comandos que bloquean la nube
$sql = preg_replace('/CREATE DATABASE.*/i', '', $sql);
$sql = preg_replace('/USE .*/i', '', $sql);

// Ejecutar TODAS las consultas de un solo golpe
if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    
    echo "<div style='background-color:#d1fae5; border-radius:10px; padding:30px; max-width:600px; margin:50px auto; border: 2px solid #10b981; font-family:sans-serif;'>";
    echo "<h1 style='color:#047857; text-align:center; margin:0;'>¡MIGRACIÓN EXITOSA! 🚀</h1>";
    echo "<p style='text-align:center; color:#065f46; font-size:18px;'>Las tablas se inyectaron perfectamente usando MySQLi.</p>";
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