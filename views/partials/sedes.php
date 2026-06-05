<?php
$sql_sedes = "SELECT * FROM sedes ORDER BY nombre_sede ASC";
$sedes = $pdo->query($sql_sedes)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .card-custom { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; background: #ffffff; animation: fadeInUp 0.6s ease-out; }
    .btn-gradient-primary { background: linear-gradient(135deg, #0984e3 0%, #0d47a1 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .btn-gradient-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(9, 132, 227, 0.3); color: white; }
    .btn-gps { background: #e0f2fe; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 8px; font-weight: 600; padding: 8px 15px; width: 100%; transition: 0.3s; }
    .btn-gps:hover { background: #bae6fd; }
</style>

<div class="card card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0" style="color: #2c3e50; font-weight: 800;">
            <i class="fa-solid fa-map-location-dot" style="color: #0984e3;"></i> Puntos de Trabajo (Sedes)
        </h4>
        <button class="btn btn-gradient-primary" onclick="abrirModalSede()">
            <i class="fa-solid fa-plus"></i> Nueva Sede
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaSedes">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th>Nombre de la Sede</th>
                    <th>Dirección</th>
                    <th>Coordenadas (Lat / Lon)</th>
                    <th>Radio (Metros)</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sedes as $s): ?>
                <tr>
                    <td class="fw-bold text-dark"><i class="fa-solid fa-store text-muted me-2"></i><?= htmlspecialchars($s['nombre_sede']) ?></td>
                    <td><?= htmlspecialchars($s['direccion']) ?></td>
                    <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($s['latitud']) ?>, <?= htmlspecialchars($s['longitud']) ?></span></td>
                    <td><span class="badge bg-success bg-opacity-10 text-success"><?= htmlspecialchars($s['radio_metros']) ?> m</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light text-primary" onclick='abrirModalEditarSede(<?= json_encode($s) ?>)'><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-light text-danger" onclick="eliminarSede(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nombre_sede']) ?>')"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #f8f9fa; border-radius: 20px 20px 0 0; border-bottom: none;">
                <h5 class="modal-title" id="modalTituloSede" style="font-weight: 800; color: #2c3e50;">Registrar Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../controllers/sedeController.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" id="accionSede" value="crear">
                    <input type="hidden" name="id_sede" id="id_sede">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Nombre del Local</label>
                        <input type="text" class="form-control bg-light border-0" name="nombre_sede" id="nombre_sede" required placeholder="Ej: Tienda Megaplaza">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Dirección</label>
                        <input type="text" class="form-control bg-light border-0" name="direccion" id="direccion" required placeholder="Ej: Av. Pardo 123">
                    </div>

                    <hr class="text-muted">
                    
                    <button type="button" class="btn-gps mb-3" onclick="obtenerCoordenadas()">
                        <i class="fa-solid fa-location-crosshairs"></i> Obtener mi ubicación actual
                    </button>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Latitud</label>
                            <input type="text" class="form-control bg-light border-0" name="latitud" id="latitud" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Longitud</label>
                            <input type="text" class="form-control bg-light border-0" name="longitud" id="longitud" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase text-success">Radio Permitido (Metros)</label>
                        <input type="number" class="form-control bg-light border-0" name="radio_metros" id="radio_metros" value="50" required>
                        <small class="text-muted" style="font-size:11px;">Rango de tolerancia para que el empleado marque asistencia.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gradient-primary px-4">Guardar Sede</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tablaSedes')) {
            $('#tablaSedes').DataTable({ "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" } });
        }
    });

    function abrirModalSede() {
        document.getElementById('accionSede').value = 'crear';
        document.getElementById('id_sede').value = '';
        document.getElementById('nombre_sede').value = '';
        document.getElementById('direccion').value = '';
        document.getElementById('latitud').value = '';
        document.getElementById('longitud').value = '';
        document.getElementById('radio_metros').value = '50';
        document.getElementById('modalTituloSede').innerHTML = '<i class="fa-solid fa-plus text-primary"></i> Registrar Sede';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSede')).show();
    }

    function abrirModalEditarSede(sede) {
        document.getElementById('accionSede').value = 'editar';
        document.getElementById('id_sede').value = sede.id;
        document.getElementById('nombre_sede').value = sede.nombre_sede;
        document.getElementById('direccion').value = sede.direccion;
        document.getElementById('latitud').value = sede.latitud;
        document.getElementById('longitud').value = sede.longitud;
        document.getElementById('radio_metros').value = sede.radio_metros;
        document.getElementById('modalTituloSede').innerHTML = '<i class="fa-solid fa-pen text-primary"></i> Editar Sede';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSede')).show();
    }

    function eliminarSede(id, nombre) {
        Swal.fire({
            title: '¿Eliminar Sede?', text: "Se borrará " + nombre + ". Asegúrate de que no haya empleados asignados aquí.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../controllers/sedeController.php?accion=eliminar&id=' + id;
            }
        });
    }

    function obtenerCoordenadas() {
        if (navigator.geolocation) {
            Swal.fire({ title: 'Buscando GPS...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitud').value = position.coords.latitude.toFixed(8);
                document.getElementById('longitud').value = position.coords.longitude.toFixed(8);
                Swal.close();
            }, function(error) {
                Swal.fire('Error', 'No se pudo obtener la ubicación. Verifica los permisos de tu navegador.', 'error');
            }, { enableHighAccuracy: true });
        } else {
            Swal.fire('Error', 'Tu navegador no soporta GPS.', 'error');
        }
    }
</script>