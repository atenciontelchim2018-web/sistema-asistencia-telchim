<?php
require_once '../config/db.php';

// ---------------------------------------------------------
// 1. MANEJO DE ELIMINACIÓN (Vía GET)
// ---------------------------------------------------------
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id) {
        try {
            $sql = "DELETE FROM empleados WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            header("Location: ../views/admin_rrhh.php?page=empleados&msg=eliminado");
            exit;
            
        } catch (PDOException $e) {
            header("Location: ../views/admin_rrhh.php?page=empleados&msg=error_fk");
            exit;
        }
    }
}

// ---------------------------------------------------------
// 2. MANEJO DE CREACIÓN Y EDICIÓN (Vía POST)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $accion       = $_POST['accion'] ?? 'crear';
    $id_empleado  = $_POST['id_empleado'] ?? null;
    $dni          = trim($_POST['dni']);
    $nombres      = trim($_POST['nombres']);
    $apellidos    = trim($_POST['apellidos']);
    $cargo        = trim($_POST['cargo']);
    $sede_id      = !empty($_POST['sede_id']) ? $_POST['sede_id'] : null;
    $tipo_horario = $_POST['tipo_horario'] ?? 'A';

    // Validar DNI único al crear
    if ($accion === 'crear') {
        $stmt_check = $pdo->prepare("SELECT id FROM empleados WHERE dni = ?");
        $stmt_check->execute([$dni]);
        if ($stmt_check->fetch()) {
            header("Location: ../views/admin_rrhh.php?page=empleados&msg=dni_duplicado");
            exit;
        }
    }

    // Parámetros base
    $params = [$dni, $nombres, $apellidos, $cargo, $sede_id, $tipo_horario];
    $foto_query = ""; 

    // Guardar Foto si se subió
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $directorio = '../uploads/fotos_empleados/';
        if (!file_exists($directorio)) { mkdir($directorio, 0777, true); }
        
        $nombre_limpio = preg_replace("/[^a-zA-Z0-9.]/", "", $_FILES['foto']['name']);
        $foto_nombre = time() . "_" . $nombre_limpio;
        
        move_uploaded_file($_FILES['foto']['tmp_name'], $directorio . $foto_nombre);
        
        if ($accion === 'crear') {
            $params[] = $foto_nombre; 
        } else {
            $foto_query = ", foto_perfil = ?"; 
            $params[] = $foto_nombre;
        }
    } else {
        if ($accion === 'crear') { $params[] = ''; }
    }

    try {
        if ($accion === 'crear') {
            $sql = "INSERT INTO empleados (dni, nombres, apellidos, cargo, sede_id, tipo_horario, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            header("Location: ../views/admin_rrhh.php?page=empleados&msg=creado");
            exit;
            
        } elseif ($accion === 'editar') {
            $params[] = $id_empleado; 
            
            $sql = "UPDATE empleados SET dni = ?, nombres = ?, apellidos = ?, cargo = ?, sede_id = ?, tipo_horario = ? {$foto_query} WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            header("Location: ../views/admin_rrhh.php?page=empleados&msg=editado");
            exit;
        }
        
    } catch (PDOException $e) {
        die("Error crítico en la base de datos: " . $e->getMessage());
    }
}
?>