<?php

// Consultamos los contratos y los vinculamos con los datos del empleado
$sql = "SELECT c.*, e.nombres, e.apellidos, e.dni 
        FROM contratos c 
        JOIN empleados e ON c.empleado_id = e.id 
        ORDER BY c.fecha_fin ASC"; // Ordenamos para que los que vencen pronto salgan primero
$contratos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Consultamos la lista de empleados para el menú desplegable del formulario
$sql_emp = "SELECT id, nombres, apellidos FROM empleados ORDER BY apellidos ASC";
$empleados_lista = $pdo->query($sql_emp)->fetchAll(PDO::FETCH_ASSOC);

// Función para calcular el estado del contrato en tiempo real
function obtenerEstadoContrato($fecha_fin) {
    if (empty($fecha_fin)) return ['clase' => 'bg-secondary', 'texto' => 'Indefinido', 'icono' => 'fa-infinity'];
    
    $hoy = new DateTime();
    $fin = new DateTime($fecha_fin);
    $diferencia = $hoy->diff($fin)->days;
    $vencido = $hoy > $fin; // true si ya pasó la fecha

    if ($vencido) {
        return ['clase' => 'bg-danger', 'texto' => 'Vencido', 'icono' => 'fa-circle-xmark'];
    } elseif ($diferencia <= 30) {
        return ['clase' => 'bg-warning text-dark', 'texto' => "Vence en $diferencia días", 'icono' => 'fa-triangle-exclamation'];
    } else {
        return ['clase' => 'bg-success', 'texto' => 'Vigente', 'icono' => 'fa-circle-check'];
    }
}
?>

<style>
    .card-contratos { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; animation: fadeInUp 0.6s ease-out; }
    .btn-nuevo { background: linear-gradient(135deg, #00b894 0%, #00a080 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s; }
    .btn-nuevo:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0, 184, 148, 0.3); color: white; }
    .btn-pdf { background: #e74c3c; color: white; border-radius: 8px; transition: transform 0.2s; padding: 5px 10px; font-size: 13px; font-weight: bold; text-decoration: none; display: inline-block; }
    .btn-pdf:hover { transform: scale(1.05); color: white; }
    .badge-estado { padding: 8px 12px; border-radius: 8px; font-weight: bold; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }
</style>

<div class="card card-contratos p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0" style="color: #2c3e50; font-weight: 800;">
            <i class="fa-solid fa-file-signature" style="color: #00b894;"></i> Gestión de Contratos
        </h4>
        <button class="btn btn-nuevo" onclick="abrirModalContrato()">
            <i class="fa-solid fa-plus"></i> Registrar Contrato
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaContratos">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Sueldo</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th class="text-center">Documento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contratos as $row): 
                    $estado = obtenerEstadoContrato($row['fecha_fin']);
                ?>
                <tr>
                    <td>
                        <span style="color: #2c3e50; font-weight: 600;">
                            <?php echo htmlspecialchars($row['apellidos'] . ', ' . $row['nombres']); ?>
                        </span><br>
                        <small class="text-muted"><i class="fa-solid fa-id-card"></i> <?php echo htmlspecialchars($row['dni']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['tipo_contrato']); ?></td>
                    <td><span style="font-weight: 700; color: #27ae60;">S/ <?php echo number_format($row['sueldo_base'], 2); ?></span></td>
                    <td><strong><?php echo $row['fecha_fin'] ? date('d/m/Y', strtotime($row['fecha_fin'])) : 'Sin fecha'; ?></strong></td>
                    <td>
                        <span class="badge-estado <?php echo $estado['clase']; ?>">
                            <i class="fa-solid <?php echo $estado['icono']; ?>"></i> <?php echo $estado['texto']; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if(!empty($row['documento_pdf'])): ?>
                            <a href="../uploads/contratos_pdf/<?php echo $row['documento_pdf']; ?>" target="_blank" class="btn-pdf">
                                <i class="fa-solid fa-file-pdf"></i> Ver PDF
                            </a>
                        <?php else: ?>
                            <span class="text-muted" style="font-size: 12px;"><i class="fa-solid fa-triangle-exclamation"></i> Falta PDF</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalContrato" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #f8f9fa; border-radius: 20px 20px 0 0; border-bottom: none;">
                <h5 class="modal-title" style="font-weight: 800; color: #2c3e50;">
                    <i class="fa-solid fa-file-contract"></i> Nuevo Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formContrato" action="../controllers/contratoController.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Asesor / Empleado</label>
                        <select class="form-select bg-light border-0" name="empleado_id" required>
                            <option value="">Seleccione un empleado...</option>
                            <?php foreach ($empleados_lista as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo htmlspecialchars($emp['apellidos'] . ', ' . $emp['nombres']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Tipo de Contrato</label>
                            <select class="form-select bg-light border-0" name="tipo_contrato" required>
                                <option value="Tiempo Completo">Tiempo Completo</option>
                                <option value="Medio Tiempo">Medio Tiempo</option>
                                <option value="Recibo por Honorarios">Recibo por Honorarios</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Sueldo Base (S/)</label>
                            <input type="number" step="0.01" class="form-control bg-light border-0" name="sueldo_base" required placeholder="1025.00">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Fecha Inicio</label>
                            <input type="date" class="form-control bg-light border-0" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Fecha Fin</label>
                            <input type="date" class="form-control bg-light border-0" name="fecha_fin">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Documento Escaneado (PDF)</label>
                        <input type="file" class="form-control bg-light border-0" name="documento_pdf" accept=".pdf">
                        <small class="text-muted">Sube el contrato firmado para tu archivo digital.</small>
                    </div>
                </div>
                
                <div class="modal-footer" style="border-top: none; padding: 0 1.5rem 1.5rem;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">Cancelar</button>
                    <button type="submit" class="btn btn-nuevo" style="width: auto;">
                        <i class="fa-solid fa-save"></i> Guardar Contrato
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tablaContratos').DataTable({
            "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
            "pageLength": 8,
            "lengthChange": false,
            "order": [[ 4, "desc" ]] // Intenta ordenar por estado/vencimiento
        });
    });

    const modalContrato = new bootstrap.Modal(document.getElementById('modalContrato'));

    function abrirModalContrato() {
        document.getElementById('formContrato').reset();
        modalContrato.show();
    }
</script>