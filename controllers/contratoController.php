<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Recibir y limpiar los datos del formulario
    $empleado_id   = filter_input(INPUT_POST, 'empleado_id', FILTER_VALIDATE_INT);
    $tipo_contrato = trim($_POST['tipo_contrato']);
    $sueldo_base   = filter_input(INPUT_POST, 'sueldo_base', FILTER_VALIDATE_FLOAT);
    $fecha_inicio  = $_POST['fecha_inicio'];
    
    // Si la fecha de fin viene vacía (ej. contrato indefinido), la dejamos como NULL
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;

    $documento_pdf = null;

    // 2. Lógica para procesar y guardar el PDF del contrato
    if (isset($_FILES['documento_pdf']) && $_FILES['documento_pdf']['error'] == UPLOAD_ERR_OK) {
        
        $directorio_pdfs = '../uploads/contratos_pdf/';
        
        // Crear la carpeta si no existe
        if (!file_exists($directorio_pdfs)) {
            mkdir($directorio_pdfs, 0777, true);
        }

        $archivo_temporal = $_FILES['documento_pdf']['tmp_name'];
        $nombre_original  = $_FILES['documento_pdf']['name'];
        $extension        = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

        // Validación de seguridad: Asegurarnos de que sea un PDF
        if ($extension === 'pdf') {
            // Renombramos el archivo para evitar duplicados y espacios (Ej: 1684930_contrato_emp_5.pdf)
            $documento_pdf = time() . "_contrato_emp_" . $empleado_id . ".pdf";
            move_uploaded_file($archivo_temporal, $directorio_pdfs . $documento_pdf);
        } else {
            // Si intentan subir algo que no es PDF, regresamos con error
            header("Location: ../views/admin_rrhh.php?msg=error_formato_pdf");
            exit;
        }
    }

    // 3. Guardar en la Base de Datos
    try {
        $sql = "INSERT INTO contratos (empleado_id, tipo_contrato, fecha_inicio, fecha_fin, sueldo_base, documento_pdf) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $empleado_id, 
            $tipo_contrato, 
            $fecha_inicio, 
            $fecha_fin, 
            $sueldo_base, 
            $documento_pdf
        ]);

        // Redirigir al dashboard con mensaje de éxito
        header("Location: ../views/admin_rrhh.php?msg=contrato_creado");
        exit;

    } catch (PDOException $e) {
        // En caso de error en la base de datos
        die("Error al registrar el contrato: " . $e->getMessage());
    }
} else {
    // Si alguien intenta entrar directamente a la URL sin enviar el formulario
    header("Location: ../views/admin_rrhh.php");
    exit;
}
?>