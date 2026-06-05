<?php
require_once '../config/db.php';

// 1. MANEJO DE ELIMINACIÓN (GET)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    
    if ($accion == 'eliminar_asig') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $pdo->prepare("DELETE FROM asignacion_turnos WHERE id = ?")->execute([$id]);
            header("Location: ../views/admin_rrhh.php?page=horarios&msg=asig_eliminada");
            exit;
        }
    }
    
    if ($accion == 'eliminar_turno') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            try {
                $pdo->prepare("DELETE FROM turnos WHERE id = ?")->execute([$id]);
                header("Location: ../views/admin_rrhh.php?page=horarios&msg=turno_eliminado");
                exit;
            } catch (PDOException $e) {
                header("Location: ../views/admin_rrhh.php?page=horarios&msg=error_fk");
                exit;
            }
        }
    }
}

// 2. MANEJO DE CREACIÓN Y ASIGNACIÓN (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';

    // CREAR TURNO MAESTRO
    if ($accion == 'crear_turno_maestro') {
        $nombre_turno = trim($_POST['nombre_turno']);
        $hora_entrada = $_POST['hora_entrada'];
        $hora_salida  = $_POST['hora_salida'];
        $tolerancia   = filter_input(INPUT_POST, 'tolerancia_minutos', FILTER_VALIDATE_INT);
        
        // Manejo de valores opcionales para almuerzo (Si vienen vacíos, se guardan como NULL)
        $hora_inicio_almuerzo = !empty($_POST['hora_inicio_almuerzo']) ? $_POST['hora_inicio_almuerzo'] : null;
        $hora_fin_almuerzo    = !empty($_POST['hora_fin_almuerzo']) ? $_POST['hora_fin_almuerzo'] : null;

        $sql = "INSERT INTO turnos (nombre_turno, hora_entrada, hora_inicio_almuerzo, hora_fin_almuerzo, hora_salida, tolerancia_minutos) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre_turno, $hora_entrada, $hora_inicio_almuerzo, $hora_fin_almuerzo, $hora_salida, $tolerancia]);

        header("Location: ../views/admin_rrhh.php?page=horarios&msg=turno_creado");
        exit;
    }

    // ASIGNAR TURNO A EMPLEADO (MASIVO ROTATIVO)
    if ($accion == 'asignar_turno_masivo') {
        $empleado_id = $_POST['empleado_id'];
        $turno_id    = $_POST['turno_id'];
        $fecha_inicio = new DateTime($_POST['fecha_inicio']);
        $fecha_fin    = new DateTime($_POST['fecha_fin']);
        $dias_seleccionados = $_POST['dias'] ?? []; // Array con los días de la semana (Mon, Tue, etc.)

        // Agregamos un día extra al final para que el bucle "DatePeriod" incluya la fecha límite
        $fecha_fin->modify('+1 day');
        $intervalo = new DateInterval('P1D'); // Avanzar de 1 en 1 día
        $periodo = new DatePeriod($fecha_inicio, $intervalo, $fecha_fin);

        $asignaciones_exitosas = 0;

        foreach ($periodo as $fecha) {
            $dia_semana = $fecha->format('D'); // Obtiene 'Mon', 'Tue', etc.
            $fecha_formateada = $fecha->format('Y-m-d');

            // Solo insertamos si el día de la semana actual está marcado en los checkbox
            if (in_array($dia_semana, $dias_seleccionados)) {
                
                // Verificar si ya tiene un turno ese mismo día para evitar errores
                $check = $pdo->prepare("SELECT id FROM asignacion_turnos WHERE empleado_id = ? AND fecha = ?");
                $check->execute([$empleado_id, $fecha_formateada]);
                
                if (!$check->fetch()) {
                    // Si el día está libre, le guardamos el turno
                    $sql = "INSERT INTO asignacion_turnos (empleado_id, turno_id, fecha) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$empleado_id, $turno_id, $fecha_formateada]);
                    $asignaciones_exitosas++;
                }
            }
        }

        // Redirigimos al finalizar el bucle
        header("Location: ../views/admin_rrhh.php?page=horarios&msg=turno_asignado");
        exit;
    }
}
?>