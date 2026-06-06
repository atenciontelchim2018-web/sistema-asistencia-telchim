<?php
// 1. REEMPLAZA ESTAS 4 LÍNEAS CON TUS VARIABLES DE RAILWAY
$host = 'mysql.railway.internal'; 
$port = '3306'; 
$user = 'root'; 
$pass = 'qYdaeaYTShzZMcgKkGacKxyRejaArWtA'; 
$dbname = 'railway';

// Conexión
$conexion = new mysqli($host, $user, $pass, $dbname, $port);
if ($conexion->connect_error) {
    die("<h1 style='color:red; text-align:center;'>Error de conexión: " . $conexion->connect_error . "</h1>");
}

// 2. LA BASE DE DATOS COMPLETA (Protegida contra errores)
$sql = <<<'SQL'
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE sedes (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_sede varchar(100) NOT NULL,
  direccion varchar(255) DEFAULT NULL,
  latitud decimal(10,8) NOT NULL,
  longitud decimal(11,8) NOT NULL,
  radio_metros int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO sedes (nombre_sede, direccion, latitud, longitud, radio_metros) VALUES
('Oficina Principal Chimbote', 'Av. Pardo 123', -9.07432100, -78.59123400, 50),
('Módulo Megaplaza', 'CC Megaplaza', -9.08123400, -78.58123400, 50);

CREATE TABLE empleados (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  dni varchar(15) NOT NULL UNIQUE,
  nombres varchar(100) NOT NULL,
  apellidos varchar(100) NOT NULL,
  celular varchar(20) DEFAULT NULL,
  correo varchar(100) DEFAULT NULL,
  cargo varchar(50) DEFAULT NULL,
  foto_perfil varchar(255) DEFAULT NULL,
  tipo_horario enum('A','B') DEFAULT 'A',
  sede_id int(11) DEFAULT NULL,
  FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO empleados (dni, nombres, apellidos, celular, correo, cargo, foto_perfil, tipo_horario, sede_id) VALUES
('12345678', 'Juan Alberto', 'Pérez Malopu', NULL, NULL, 'Asesor de Ventas', '1780591872_user2160x160.jpg', 'A', NULL),
('41399541', 'YAHAIRA GENOVEVA', 'CHUQUI MIRANDA', NULL, NULL, 'Asesor de Ventas', '1780593158_Yahaira.png', 'A', NULL),
('72029840', 'ANGIE VICTORIA', 'ESQUECHE PEREZ', NULL, NULL, 'Asesor de Ventas', '1780593273_ANGIE.png', 'A', NULL),
('41699870', 'CINTHIA VANESSA', 'ARTEAGA GAZZOLO', NULL, NULL, 'Asesor de Ventas', '1780593323_Vanessa.png', 'A', NULL);

CREATE TABLE administradores (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario varchar(50) NOT NULL UNIQUE,
  password varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO administradores (usuario, password) VALUES
('admin', '$2y$10$wT2.j2n4W8H8k6T7v2B.7.Q8R.3T7T7T7T7T7T7T7T7T7T7T7T7T7');

CREATE TABLE turnos (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_turno varchar(100) NOT NULL,
  hora_entrada time NOT NULL,
  hora_inicio_almuerzo time DEFAULT NULL,
  hora_fin_almuerzo time DEFAULT NULL,
  hora_salida time NOT NULL,
  tolerancia_minutos int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO turnos (nombre_turno, hora_entrada, hora_inicio_almuerzo, hora_fin_almuerzo, hora_salida, tolerancia_minutos) VALUES
('TURNO COMPLETO', '09:00:00', '01:00:00', '03:00:00', '01:00:00', 5),
('TURNO PARCIAL', '01:00:00', NULL, NULL, '08:00:00', 5);

CREATE TABLE asignacion_turnos (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  empleado_id int(11) NOT NULL,
  turno_id int(11) NOT NULL,
  fecha date NOT NULL,
  FOREIGN KEY (empleado_id) REFERENCES empleados(id) ON DELETE CASCADE,
  FOREIGN KEY (turno_id) REFERENCES turnos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE asistencias (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  empleado_id int(11) DEFAULT NULL,
  fecha date DEFAULT NULL,
  hora_entrada datetime DEFAULT NULL,
  inicio_almuerzo datetime DEFAULT NULL,
  fin_almuerzo datetime DEFAULT NULL,
  hora_salida datetime DEFAULT NULL,
  latitud decimal(10,8) DEFAULT NULL,
  longitud decimal(10,8) DEFAULT NULL,
  foto_evidencia varchar(255) DEFAULT NULL,
  estado enum('pendiente','aprobado','tardanza_justificada','permiso_solicitado') DEFAULT 'pendiente',
  motivo_permiso text DEFAULT NULL,
  FOREIGN KEY (empleado_id) REFERENCES empleados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE configuracion_sistema (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  latitud_oficina decimal(10,8) NOT NULL,
  longitud_oficina decimal(11,8) NOT NULL,
  radio_metros int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO configuracion_sistema (latitud_oficina, longitud_oficina, radio_metros) VALUES
(-9.07432100, -78.59123400, 50);

CREATE TABLE contratos (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  empleado_id int(11) DEFAULT NULL,
  tipo_contrato varchar(50) DEFAULT NULL,
  fecha_inicio date DEFAULT NULL,
  fecha_fin date DEFAULT NULL,
  sueldo_base decimal(10,2) DEFAULT NULL,
  documento_pdf varchar(255) DEFAULT NULL,
  estado enum('activo','finalizado') DEFAULT 'activo',
  FOREIGN KEY (empleado_id) REFERENCES empleados(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
SQL;

// 3. EJECUTAR LA INYECCIÓN
if ($conexion->multi_query($sql)) {
    do {
        if ($resultado = $conexion->store_result()) {
            $resultado->free();
        }
    } while ($conexion->next_result());
    
    echo "<div style='font-family:sans-serif; text-align:center; padding:40px; background:#dcfce3; color:#166534; border-radius:10px; margin:50px auto; max-width:600px; border:2px solid #22c55e;'>";
    echo "<h1 style='margin-top:0;'>✅ ¡MIGRACIÓN PERFECTA!</h1>";
    echo "<p style='font-size:18px;'>Las tablas y los datos se crearon correctamente en la nube.</p>";
    echo "<a href='index.php' style='display:inline-block; margin-top:20px; padding:15px 30px; background:#16a34a; color:white; text-decoration:none; font-weight:bold; border-radius:5px;'>Ir a mi Sistema Web</a>";
    echo "</div>";
} else {
    echo "<h1 style='color:red; text-align:center;'>Error en la creación: " . $conexion->error . "</h1>";
}
$conexion->close();
?>