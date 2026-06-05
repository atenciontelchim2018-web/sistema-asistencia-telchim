<?php
session_start();
if (!isset($_SESSION['admin_logeado']) || $_SESSION['admin_logeado'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Determinar qué página cargar leyendo la URL (por defecto asistencias)
$page = $_GET['page'] ?? 'asistencias';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Maestro | Telchim S.A.C.</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --primary-dark: #0f172a; --accent-blue: #3b82f6; }
        body { background: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; height: 100vh; overflow: hidden; margin: 0; }
        
        .sidebar { width: 280px; background: var(--primary-dark); color: white; display: flex; flex-direction: column; transition: 0.3s; }
        .sidebar-header { padding: 30px 20px; font-weight: 900; font-size: 20px; letter-spacing: 1px; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .nav-menu { flex: 1; padding: 20px; overflow-y: auto; }
        .nav-category { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800; margin: 20px 0 10px 10px; }
        
        /* Enlaces usando etiquetas A para navegación segura */
        .nav-link { text-decoration: none; color: #94a3b8; cursor: pointer; padding: 12px 15px; border-radius: 10px; transition: 0.3s; display: flex; align-items: center; gap: 12px; margin-bottom: 5px; font-weight: 600; }
        .nav-link:hover, .nav-link.active { background: var(--accent-blue); color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        
        /* Animación suave al entrar a cada módulo */
        .fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header"><i class="fa-solid fa-rocket me-2"></i> TELCHIM S.A.C.</div>
        <div class="nav-menu">
            <div class="nav-category">Control Operativo</div>
            <a href="?page=asistencias" class="nav-link <?=($page=='asistencias'?'active':'')?>"><i class="fa-solid fa-clock"></i> Asistencias</a>
            <a href="?page=empleados" class="nav-link <?=($page=='empleados'?'active':'')?>"><i class="fa-solid fa-users"></i> Empleados</a>
            <a href="?page=horarios" class="nav-link <?=($page=='horarios'?'active':'')?>"><i class="fa-solid fa-calendar-days"></i> Horarios</a>
            <a href="?page=sedes" class="nav-link <?=($page=='sedes'?'active':'')?>"><i class="fa-solid fa-map-location-dot"></i> Sedes y Locales</a>
            
            <div class="nav-category">Legal y Finanzas</div>
            <a href="?page=contratos" class="nav-link <?=($page=='contratos'?'active':'')?>"><i class="fa-solid fa-file-contract"></i> Contratos</a>
            <a href="?page=planillas" class="nav-link <?=($page=='planillas'?'active':'')?>"><i class="fa-solid fa-money-bill-wave"></i> Planillas</a>
        </div>
        <div class="sidebar-footer p-3">
            <a href="../controllers/logout.php" class="btn btn-danger w-100"><i class="fa-solid fa-power-off me-2"></i> Salir</a>
        </div>
    </div>

    <div class="main-content fade-in">
        <?php 
            // Inyección segura del archivo
            $archivo = "partials/$page.php";
            if(file_exists($archivo)) {
                include $archivo;
            } else {
                // Si el archivo no existe aún, muestra aviso de construcción
                echo "<div class='card p-5 text-center shadow-sm' style='border-radius:15px;'>
                        <h3 class='text-muted'><i class='fa-solid fa-person-digging mb-3' style='font-size:40px;'></i><br>Módulo en Construcción</h3>
                        <p>El módulo de <b>$page</b> estará disponible pronto.</p>
                      </div>";
            }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        const pageParam = urlParams.get('page'); // Detectamos en qué módulo estamos para personalizar las alertas

        if (msg === 'creado') {
            let texto = pageParam === 'sedes' ? 'La nueva sede fue registrada correctamente.' : 'Empleado registrado correctamente.';
            Swal.fire({ title: '¡Éxito!', text: texto, icon: 'success', confirmButtonColor: '#3b82f6' });
        } else if (msg === 'editado') {
            let texto = pageParam === 'sedes' ? 'Los datos de la sede se actualizaron.' : 'Los datos del empleado se actualizaron.';
            Swal.fire({ title: '¡Actualizado!', text: texto, icon: 'success', confirmButtonColor: '#3b82f6' });
        } else if (msg === 'eliminado') {
            let texto = pageParam === 'sedes' ? 'La sede fue retirada del sistema.' : 'El empleado fue retirado del sistema.';
            Swal.fire({ title: '¡Eliminado!', text: texto, icon: 'info', confirmButtonColor: '#3b82f6' });
        } else if (msg === 'dni_duplicado') {
            Swal.fire({ title: 'DNI Duplicado', text: 'Ese número de DNI ya está registrado en otro trabajador.', icon: 'error', confirmButtonColor: '#ef4444' });
        } else if (msg === 'error_fk') {
            let texto = pageParam === 'sedes' ? 'No puedes borrar esta sede porque hay empleados asignados a ella.' : 'No puedes borrar a este empleado porque ya tiene asistencias o contratos registrados.';
            Swal.fire({ title: 'Acción Bloqueada', text: texto, icon: 'warning', confirmButtonColor: '#f59e0b' });
        } else if (msg === 'turno_creado') {
            Swal.fire({ title: 'Turno Maestro Creado', text: 'El horario está disponible para ser asignado.', icon: 'success', confirmButtonColor: '#3b82f6' });
        } else if (msg === 'turno_asignado') {
            Swal.fire({ title: 'Turno Asignado', text: 'El empleado tiene su horario asignado correctamente para la fecha indicada.', icon: 'success', confirmButtonColor: '#3b82f6' });
        } else if (msg === 'asig_duplicada') {
            Swal.fire({ title: 'Doble Asignación', text: 'El empleado ya tiene un turno asignado para esa fecha. Elimínelo primero si desea cambiarlo.', icon: 'warning', confirmButtonColor: '#f59e0b' });
        }

        // Limpiar la URL para que no se repita la alerta al recargar (F5)
        if (msg) {
            const cleanUrl = window.location.pathname + "?page=" + (urlParams.get('page') || 'asistencias');
            window.history.replaceState(null, null, cleanUrl);
        }
    </script>
</body>
</html>