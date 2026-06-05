<?php
// Consultar el historial de asistencias cruzando datos con empleados y sedes
$sql = "SELECT a.*, e.nombres, e.apellidos, e.dni, s.nombre_sede 
        FROM asistencias a
        JOIN empleados e ON a.empleado_id = e.id
        LEFT JOIN sedes s ON e.sede_id = s.id
        ORDER BY a.fecha DESC, a.hora_entrada DESC";
$asistencias = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .card-custom { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; background: #ffffff; animation: fadeInUp 0.5s ease-out; }
    .badge-estado { padding: 6px 12px; border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; font-size: 11px; text-transform: uppercase; }
    .estado-aprobado { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .estado-tardanza { background: #ffedd5; color: #ea580c; border: 1px solid #fed7aa; }
    .estado-permiso { background: #fef9c3; color: #ca8a04; border: 1px solid #fef08a; cursor: pointer; transition: 0.2s; }
    .estado-permiso:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(202, 138, 4, 0.2); }
    
    .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; border: none; }
    .btn-foto { background: #e0f2fe; color: #0284c7; }
    .btn-foto:hover { background: #bae6fd; transform: translateY(-2px); }
    .btn-mapa { background: #f3e8ff; color: #9333ea; }
    .btn-mapa:hover { background: #e9d5ff; transform: translateY(-2px); }

    .foto-visor { width: 100%; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
</style>

<div class="card card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0" style="color: #2c3e50; font-weight: 800;">
            <i class="fa-solid fa-clock-rotate-left text-primary"></i> Auditoría de Asistencias
        </h4>
        <button class="btn btn-outline-success rounded-pill fw-bold" onclick="window.print()">
            <i class="fa-solid fa-file-excel"></i> Exportar Reporte
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaAsistencias" style="width:100%; font-size: 13px;">
            <thead style="background: #f8f9fa; text-transform: uppercase; font-size: 11px; color: #64748b;">
                <tr>
                    <th>Fecha</th>
                    <th>Empleado</th>
                    <th>Sede</th>
                    <th>Entrada</th>
                    <th>Almuerzo (Ida / Vuelta)</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th class="text-center">Auditoría</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asistencias as $a): ?>
                <tr>
                    <td><strong><?= date("d/m/Y", strtotime($a['fecha'])) ?></strong></td>
                    <td>
                        <div class="text-dark fw-bold"><?= htmlspecialchars($a['apellidos'] . ', ' . $a['nombres']) ?></div>
                        <small class="text-muted">DNI: <?= htmlspecialchars($a['dni']) ?></small>
                    </td>
                    <td><i class="fa-solid fa-location-dot text-danger"></i> <?= $a['nombre_sede'] ? htmlspecialchars($a['nombre_sede']) : 'No asignada' ?></td>
                    
                    <td><span class="font-monospace text-primary fw-bold"><?= substr($a['hora_entrada'], 0, 5) ?></span></td>
                    
                    <td>
                        <?php if($a['inicio_almuerzo']): ?>
                            <span class="font-monospace text-muted"><?= substr($a['inicio_almuerzo'], 0, 5) ?></span> - 
                            <span class="font-monospace text-muted"><?= $a['fin_almuerzo'] ? substr($a['fin_almuerzo'], 0, 5) : '---' ?></span>
                        <?php else: ?>
                            <span class="badge bg-light text-muted">No aplica</span>
                        <?php endif; ?>
                    </td>

                    <td><span class="font-monospace fw-bold <?= $a['hora_salida'] ? 'text-success' : 'text-muted' ?>"><?= $a['hora_salida'] ? substr($a['hora_salida'], 0, 5) : '--:--' ?></span></td>
                    
                    <td>
                        <?php if($a['estado'] == 'aprobado'): ?>
                            <span class="badge-estado estado-aprobado"><i class="fa-solid fa-check"></i> Normal</span>
                        <?php elseif($a['estado'] == 'tardanza'): ?>
                            <span class="badge-estado estado-tardanza"><i class="fa-solid fa-triangle-exclamation"></i> Tardanza</span>
                        <?php elseif($a['estado'] == 'permiso_solicitado'): ?>
                            <span class="badge-estado estado-permiso" onclick="revisarPermiso(<?= $a['id'] ?>, '<?= htmlspecialchars(addslashes($a['motivo_permiso'])) ?>', '<?= htmlspecialchars($a['nombres']) ?>')">
                                <i class="fa-solid fa-bell"></i> Permiso Pdt.
                            </span>
                        <?php else: ?>
                            <span class="badge-estado bg-secondary text-white"><?= htmlspecialchars($a['estado']) ?></span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <button class="btn-icon btn-foto" title="Ver Selfie" onclick="verFoto('../uploads/evidencias/<?= $a['foto_evidencia'] ?>')">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                        <a href="https://www.google.com/maps?q=<?= $a['latitud'] ?>,<?= $a['longitud'] ?>" target="_blank" class="btn-icon btn-mapa" title="Ver en Mapa">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="background: transparent; border: none;">
            <div class="modal-body text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 10;"></button>
                <img src="" id="imagenEvidencia" class="foto-visor" alt="Evidencia">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tablaAsistencias')) {
            $('#tablaAsistencias').DataTable({ 
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
                "order": [[ 0, "desc" ], [ 3, "desc" ]] // Ordenar por fecha y hora más reciente
            });
        }
    });

    function verFoto(ruta) {
        document.getElementById('imagenEvidencia').src = ruta;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFoto')).show();
    }

    function revisarPermiso(id_asistencia, motivo, nombre) {
        Swal.fire({
            title: 'Permiso Solicitado',
            html: `<div class="text-start mt-3">
                     <p class="mb-1 text-muted small text-uppercase fw-bold">Empleado:</p>
                     <p class="fw-bold">${nombre}</p>
                     <p class="mb-1 text-muted small text-uppercase fw-bold">Motivo de Salida Anticipada:</p>
                     <div class="p-3 bg-light rounded border text-dark" style="font-size:14px; text-align:justify;">"${motivo}"</div>
                   </div>`,
            icon: 'info',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#10b981', // Verde aprobar
            denyButtonColor: '#ef4444',    // Rojo rechazar
            cancelButtonColor: '#6b7280',  // Gris cerrar
            confirmButtonText: '<i class="fa-solid fa-check"></i> Aprobar Salida',
            denyButtonText: '<i class="fa-solid fa-xmark"></i> Rechazar',
            cancelButtonText: 'Cerrar'
        }).then((result) => {
            if (result.isConfirmed) {
                procesarPermiso(id_asistencia, 'aprobado');
            } else if (result.isDenied) {
                procesarPermiso(id_asistencia, 'rechazado');
            }
        });
    }

    function procesarPermiso(id, decision) {
        // Redirigir al controlador con la decisión
        window.location.href = `../controllers/asistenciaController.php?accion=resolver_permiso&id=${id}&decision=${decision}`;
    }
</script>