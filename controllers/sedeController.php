<?php
require_once '../config/db.php';

// ELIMINAR (GET)
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $pdo->prepare("DELETE FROM sedes WHERE id = ?")->execute([$id]);
            header("Location: ../views/admin_rrhh.php?page=sedes&msg=eliminado");
            exit;
        } catch (PDOException $e) {
            // Si da error es porque hay empleados asignados a esta sede
            header("Location: ../views/admin_rrhh.php?page=sedes&msg=error_fk");
            exit;
        }
    }
}

// CREAR O EDITAR (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion       = $_POST['accion'];
    $id_sede      = $_POST['id_sede'] ?? null;
    $nombre_sede  = trim($_POST['nombre_sede']);
    $direccion    = trim($_POST['direccion']);
    $latitud      = $_POST['latitud'];
    $longitud     = $_POST['longitud'];
    $radio_metros = filter_input(INPUT_POST, 'radio_metros', FILTER_VALIDATE_INT) ?? 50;

    if ($accion === 'crear') {
        $sql = "INSERT INTO sedes (nombre_sede, direccion, latitud, longitud, radio_metros) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre_sede, $direccion, $latitud, $longitud, $radio_metros]);
        header("Location: ../views/admin_rrhh.php?page=sedes&msg=creado");
        exit;
    } 
    
    if ($accion === 'editar') {
        $sql = "UPDATE sedes SET nombre_sede = ?, direccion = ?, latitud = ?, longitud = ?, radio_metros = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre_sede, $direccion, $latitud, $longitud, $radio_metros, $id_sede]);
        header("Location: ../views/admin_rrhh.php?page=sedes&msg=editado");
        exit;
    }
}
?>