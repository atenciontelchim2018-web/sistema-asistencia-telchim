<?php
require_once '../config/db.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Reporte_Asistencias_" . date('Y-m-d') . ".xls");

$sql = "SELECT e.nombres, e.apellidos, a.fecha, a.hora_entrada, a.hora_salida, a.estado FROM asistencias a JOIN empleados e ON a.empleado_id = e.id";
$stmt = $pdo->query($sql);

echo "Nombre\tApellidos\tFecha\tEntrada\tSalida\tEstado\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['nombres']}\t{$row['apellidos']}\t{$row['fecha']}\t{$row['hora_entrada']}\t{$row['hora_salida']}\t{$row['estado']}\n";
}
?>