<?php
// Detecta automáticamente cualquier formato de variable de Railway, o usa XAMPP si estás en tu PC
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'sistema_asistencia';
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>