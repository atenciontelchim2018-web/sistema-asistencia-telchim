<?php
require_once '../config/db.php';
require_once '../includes/funciones.php';

// 1. Configuración Regional
date_default_timezone_set('America/Lima');

// 2. Recepción de datos
$lat_usuario = $_POST['latitud'] ?? 0;
$lon_usuario = $_POST['longitud'] ?? 0;
$dni_escaneado = filter_input(INPUT_POST, 'empleado_id', FILTER_DEFAULT);
$tipo_registro = $_POST['tipo_registro'] ?? 'entrada';
$motivo_permiso = filter_input(INPUT_POST, 'motivo_permiso', FILTER_DEFAULT) ?? '';

// 3. Buscar información del empleado (incluyendo su tipo de horario base)
$stmt_emp = $pdo->prepare("SELECT id, nombres, apellidos, tipo_horario FROM empleados WHERE dni = ?");
$stmt_emp->execute([$dni_escaneado]);
$empleado = $stmt_emp->fetch(PDO::FETCH_ASSOC);

if (!$empleado) {
    echo json_encode(["status" => "error", "message" => "DNI no registrado en el sistema."]);
    exit;
}
$id_real_empleado = $empleado['id'];

// 4. VALIDACIÓN: Evidencia Fotográfica Obligatoria
if (!isset($_FILES['selfie']) || $_FILES['selfie']['error'] != UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "La foto selfie es obligatoria para guardar su registro."]);
    exit;
}

// 5. VALIDACIÓN: Geolocalización Dinámica por Punto de Trabajo (Sede)
$stmt_sede = $pdo->prepare("
    SELECT s.latitud, s.longitud, s.radio_metros 
    FROM empleados e
    JOIN sedes s ON e.sede_id = s.id
    WHERE e.id = ?
");
$stmt_sede->execute([$id_real_empleado]);
$sede_empleado = $stmt_sede->fetch(PDO::FETCH_ASSOC);

if (!$sede_empleado) {
    echo json_encode(["status" => "error", "message" => "No tienes un punto de trabajo (Sede) asignado. Comunícate con el administrador."]);
    exit;
}

$lat_oficina = $sede_empleado['latitud'];
$lon_oficina = $sede_empleado['longitud'];
$radio_permitido_km = ($sede_empleado['radio_metros'] ?? 50) / 1000; // Convertir metros a kilómetros

// Calculamos la distancia entre el empleado y su tienda asignada
if (calcularDistancia($lat_usuario, $lon_usuario, $lat_oficina, $lon_oficina) > $radio_permitido_km) {
    echo json_encode(["status" => "error", "message" => "Ubicación denegada. Te encuentras fuera del rango de tu punto de trabajo asignado."]);
    exit;
}

// Configurar variables de tiempo actuales
$fecha_hoy = date("Y-m-d");
$hora_actual = date("H:i:s");

// -------------------------------------------------------------------------
// LÓGICA DE ROTACIÓN SEMANAL AUTOMÁTICA (Horario A / Horario B)
// -------------------------------------------------------------------------
$semana_del_ano = (int)date('W');
$dia_de_la_semana = (int)date('N'); // 1 = Lunes, 6 = Sábado, 7 = Domingo

if ($dia_de_la_semana == 7) {
    echo json_encode(["status" => "error", "message" => "Hoy es tu día de descanso programado."]);
    exit;
}

// Determinar el horario vigente para esta semana de forma rotativa.
// Si la semana del año es Impar, se invierte automáticamente el tipo de horario base del empleado.
$horario_vigente = $empleado['tipo_horario'];
if ($semana_del_ano % 2 !== 0) {
    $horario_vigente = ($horario_vigente === 'A') ? 'B' : 'A';
}

// Definición de las reglas del día basado en la semana rotativa
// Horario A: Lun, Mie, Vie = Partido (9-1 y 3-8) // Mar, Jue, Sab = Continuo (1-8)
// Horario B: Lun, Mie, Vie = Continuo (1-8) // Mar, Jue, Sab = Partido (9-1 y 3-8)
$es_dia_impar = in_array($dia_de_la_semana, [1, 3, 5]); // Lunes, Miércoles, Viernes

if ($horario_vigente === 'A') {
    $tipo_turno_hoy = $es_dia_impar ? 'partido' : 'continuo';
} else {
    $tipo_turno_hoy = $es_dia_impar ? 'continuo' : 'partido';
}

// Establecer horas teóricas de control según el turno determinado
if ($tipo_turno_hoy === 'partido') {
    $hora_entrada_teorica = "09:00:00";
    $hora_regreso_almuerzo_teorica = "15:00:00";
} else {
    $hora_entrada_teorica = "13:00:00";
}
$hora_salida_teorica = "20:00:00";

// Procesar y guardar el archivo de la selfie
$foto_nombre = time() . "_" . $tipo_registro . "_emp_" . $id_real_empleado . ".jpg";
move_uploaded_file($_FILES["selfie"]["tmp_name"], "../uploads/evidencias/" . $foto_nombre);

// -------------------------------------------------------------------------
// PROCESAMIENTO DE LAS ACCIONES DE ASISTENCIA
// -------------------------------------------------------------------------

if ($tipo_registro === 'entrada') {
    // Verificar duplicidad de entrada principal
    $stmt_check = $pdo->prepare("SELECT id FROM asistencias WHERE empleado_id = ? AND fecha = ?");
    $stmt_check->execute([$id_real_empleado, $fecha_hoy]);
    if ($stmt_check->fetch()) {
        echo json_encode(["status" => "warning", "message" => "Ya registraste tu entrada el día de hoy."]);
        exit;
    }

    // CONTROL DE TOLERANCIA (Máximo 5 minutos)
    $hora_marcada = new DateTime($hora_actual);
    $hora_limite = new DateTime($hora_entrada_teorica);
    $hora_limite->modify("+5 minutes");

    if ($hora_marcada > $hora_limite) {
        echo json_encode([
            "status" => "error", 
            "message" => "Has excedido el tiempo de tolerancia de ingreso. Por favor, comunícate con tu superior a cargo para que se te habilite el marcado bajo una TARDANZA JUSTIFICADA."
        ]);
        exit;
    }

    $estado = "aprobado";
    $stmt_insert = $pdo->prepare("INSERT INTO asistencias (empleado_id, fecha, hora_entrada, latitud, longitud, foto_evidencia, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert->execute([$id_real_empleado, $fecha_hoy, $hora_actual, $lat_usuario, $lon_usuario, $foto_nombre, $estado]);
    
    $mensaje = "Entrada registrada correctamente. ¡Buen día de labores!";

} else {
    // Para Almuerzos y Salidas, verificar que exista una entrada previa hoy
    $stmt_check = $pdo->prepare("SELECT id, estado FROM asistencias WHERE empleado_id = ? AND fecha = ? ORDER BY id DESC LIMIT 1");
    $stmt_check->execute([$id_real_empleado, $fecha_hoy]);
    $registro = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        echo json_encode(["status" => "error", "message" => "Error: Debe registrar su entrada principal primero."]);
        exit;
    }

    $id_asistencia = $registro['id'];

    if ($tipo_registro === 'salida_almorzar') {
        if ($tipo_turno_hoy !== 'partido') {
            echo json_encode(["status" => "error", "message" => "Tu turno de hoy es continuo (1pm a 8pm) y no requiere marcar salida a almuerzo."]);
            exit;
        }
        $pdo->prepare("UPDATE asistencias SET inicio_almuerzo = ? WHERE id = ?")->execute([$hora_actual, $id_asistencia]);
        $mensaje = "Salida a almorzar registrada correctamente.";

    } elseif ($tipo_registro === 'regreso_almuerzo') {
        if ($tipo_turno_hoy !== 'partido') {
            echo json_encode(["status" => "error", "message" => "Tu turno de hoy es continuo y no requiere marcar regreso de almuerzo."]);
            exit;
        }

        // CONTROL DE TOLERANCIA AL REGRESAR DE ALMUERZO (Máximo 5 minutos hasta las 15:05)
        $hora_marcada = new DateTime($hora_actual);
        $hora_limite_almuerzo = new DateTime($hora_regreso_almuerzo_teorica);
        $hora_limite_almuerzo->modify("+5 minutes");

        if ($hora_marcada > $hora_limite_almuerzo) {
            echo json_encode([
                "status" => "error", 
                "message" => "Has excedido el tiempo de tolerancia para el retorno de refrigerio. Comunícate con tu superior a cargo para habilitar tu marcado."
            ]);
            exit;
        }

        $pdo->prepare("UPDATE asistencias SET fin_almuerzo = ? WHERE id = ?")->execute([$hora_actual, $id_asistencia]);
        $mensaje = "Retorno de almuerzo registrado a tiempo.";

    } elseif ($tipo_registro === 'salida') {
        // LÓGICA DE SOLICITUD DE PERMISO (Si se retira antes de las 20:00:00)
        if ($hora_actual < $hora_salida_teorica) {
            // Se marca como salida anticipada con estatus pendiente de aprobación del administrador
            $nuevo_estado = "permiso_solicitado";
            $pdo->prepare("UPDATE asistencias SET hora_salida = ?, motivo_permiso = ?, estado = ? WHERE id = ?")
                ->execute([$hora_actual, $motivo_permiso, $nuevo_estado, $id_asistencia]);
            
            $mensaje = "Solicitud de permiso enviada. El administrador deberá aceptar el registro para proceder a retirarte.";
        } else {
            // Salida ordinaria cumpliendo la jornada de las 8:00 PM
            $pdo->prepare("UPDATE asistencias SET hora_salida = ? WHERE id = ?")->execute([$hora_actual, $id_asistencia]);
            $mensaje = "Salida final de labores registrada correctamente. ¡Hasta mañana!";
        }
    }
}
// -------------------------------------------------------------------------
// RESOLUCIÓN DE PERMISOS DE SALIDA POR EL ADMINISTRADOR (VÍA GET)
// -------------------------------------------------------------------------
if (isset($_GET['accion']) && $_GET['accion'] == 'resolver_permiso') {
    $id_asistencia = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $decision = $_GET['decision'] ?? '';

    if ($id_asistencia && in_array($decision, ['aprobado', 'rechazado'])) {
        // Si aprueba, el estado vuelve a ser 'aprobado'. Si rechaza, se marca como 'falta_injustificada' o 'permiso_rechazado'
        $estado_final = ($decision === 'aprobado') ? 'aprobado' : 'permiso_rechazado';
        
        $stmt = $pdo->prepare("UPDATE asistencias SET estado = ? WHERE id = ?");
        $stmt->execute([$estado_final, $id_asistencia]);
        
        header("Location: ../views/admin_rrhh.php?page=asistencias&msg=permiso_" . $decision);
        exit;
    }
}

echo json_encode(["status" => "success", "message" => $mensaje]);
?>