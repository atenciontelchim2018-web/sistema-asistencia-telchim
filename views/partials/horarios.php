<?php
// 1. Consultar los turnos maestros
$sql_turnos = "SELECT * FROM turnos ORDER BY id DESC";
$turnos = $pdo->query($sql_turnos)->fetchAll(PDO::FETCH_ASSOC);

// 2. Consultar empleados
$sql_empleados = "SELECT id, nombres, apellidos, dni FROM empleados ORDER BY apellidos ASC";
$empleados = $pdo->query($sql_empleados)->fetchAll(PDO::FETCH_ASSOC);

// 3. Consultar asignaciones
$sql_asignaciones = "SELECT ast.id, emp.nombres, emp.apellidos, emp.dni, tur.nombre_turno, tur.hora_entrada, tur.hora_salida, ast.fecha 
                     FROM asignacion_turnos ast
                     JOIN empleados emp ON ast.empleado_id = emp.id
                     JOIN turnos tur ON ast.turno_id = tur.id
                     ORDER BY ast.fecha DESC, emp.apellidos ASC";
$asignaciones = $pdo->query($sql_asignaciones)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .card-custom { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; background: #ffffff; animation: fadeInUp 0.6s ease-out; }
    .btn-gradient-primary { background: linear-gradient(135deg, #0984e3 0%, #0d47a1 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .btn-gradient-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(9, 132, 227, 0.3); color: white; }
    .btn-gradient-success { background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .btn-gradient-success:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(46, 204, 113, 0.3); color: white; }
    .nav-tabs-custom { border-bottom: 2px solid #f1f5f9; }
    .nav-tabs-custom .nav-link { border: none; color: #64748b; font-weight: 600; padding: 12px 20px; }
    .nav-tabs-custom .nav-link.active { color: #0984e3; border-bottom: 3px solid #0984e3; background: none; }
</style>

<div class="row g-4">
    <div class="col-12">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="m-0" style="color: #2c3e50; font-weight: 800;">
                    <i class="fa-solid fa-calendar-days" style="color: #0984e3;"></i> Gestión de Horarios y Turnos
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-gradient-success" onclick="abrirModalTurno()">
                        <i class="fa-solid fa-clock"></i> Crear Turno Maestro
                    </button>
                    <button class="btn btn-gradient-primary" onclick="abrirModalAsignacion()">
                        <i class="fa-solid fa-user-clock"></i> Programar Rotación
                    </button>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="horariosTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="asignaciones-tab" data-bs-toggle="tab" data-bs-target="#tab-panel-asignaciones" type="button" role="tab">
                        <i class="fa-solid fa-list-check me-2"></i>Turnos Asignados
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="turnos-tab" data-bs-toggle="tab" data-bs-target="#tab-panel-turnos" type="button" role="tab">
                        <i class="fa-solid fa-gears me-2"></i>Configuración de Turnos
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-panel-asignaciones" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaAsignaciones" style="width:100%">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Fecha</th>
                                    <th>DNI</th>
                                    <th>Empleado</th>
                                    <th>Turno Asignado</th>
                                    <th>Rango Principal</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asignaciones as $asig): ?>
                                <tr>
                                    <td><strong><?= date("d/m/Y", strtotime($asig['fecha'])) ?></strong></td>
                                    <td><?= htmlspecialchars($asig['dni']) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($asig['apellidos'] . ', ' . $asig['nombres']) ?></td>
                                    <td><span class="badge bg-primary px-3 py-2" style="border-radius:6px;"><?= htmlspecialchars($asig['nombre_turno']) ?></span></td>
                                    <td><i class="fa-regular fa-clock text-muted me-1"></i> <?= substr($asig['hora_entrada'], 0, 5) ?> - <?= substr($asig['hora_salida'], 0, 5) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-light text-danger" onclick="eliminarAsignacion(<?= $asig['id'] ?>)">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-panel-turnos" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablaTurnosMaestros" style="width:100%">
                            <thead style="background: #f8f9fa;">
                                <tr>
                                    <th>Nombre del Turno</th>
                                    <th>Entrada</th>
                                    <th>Inicio Refri.</th>
                                    <th>Fin Refri.</th>
                                    <th>Salida</th>
                                    <th>Tolerancia</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($turnos as $t): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($t['nombre_turno']) ?></td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success font-monospace px-2 py-1"><?= substr($t['hora_entrada'], 0, 5) ?></span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning font-monospace px-2 py-1"><?= !empty($t['hora_inicio_almuerzo']) ? substr($t['hora_inicio_almuerzo'], 0, 5) : '---' ?></span></td>
                                    <td><span class="badge bg-warning bg-opacity-10 text-warning font-monospace px-2 py-1"><?= !empty($t['hora_fin_almuerzo']) ? substr($t['hora_fin_almuerzo'], 0, 5) : '---' ?></span></td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger font-monospace px-2 py-1"><?= substr($t['hora_salida'], 0, 5) ?></span></td>
                                    <td><?= htmlspecialchars($t['tolerancia_minutos']) ?> min</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-light text-danger" onclick="eliminarTurnoMaestro(<?= $t['id'] ?>, '<?= htmlspecialchars($t['nombre_turno']) ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTurnoMaestro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #f8f9fa; border-radius: 20px 20px 0 0; border-bottom: none;">
                <h5 class="modal-title" style="font-weight: 800; color: #2c3e50;"><i class="fa-solid fa-clock text-success"></i> Crear Turno Maestro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../controllers/horarioController.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="crear_turno_maestro">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Nombre del Turno</label>
                        <input type="text" class="form-control bg-light border-0" name="nombre_turno" placeholder="Ej: Turno Partido (9 a 8)" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Hora de Entrada</label>
                            <input type="time" class="form-control bg-light border-0" name="hora_entrada" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Hora de Salida</label>
                            <input type="time" class="form-control bg-light border-0" name="hora_salida" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase" style="color: #d97706 !important;">Inicio Almuerzo</label>
                            <input type="time" class="form-control bg-light border-0" name="hora_inicio_almuerzo">
                            <small class="text-muted" style="font-size: 0.7rem;">Dejar vacío si es continuo</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase" style="color: #d97706 !important;">Fin Almuerzo</label>
                            <input type="time" class="form-control bg-light border-0" name="hora_fin_almuerzo">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Tolerancia (Minutos)</label>
                        <input type="number" class="form-control bg-light border-0" name="tolerancia_minutos" value="5" min="0" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gradient-success px-4">Guardar Turno</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAsignacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #f8f9fa; border-radius: 20px 20px 0 0; border-bottom: none;">
                <h5 class="modal-title" style="font-weight: 800; color: #2c3e50;"><i class="fa-solid fa-calendar-plus text-primary"></i> Planificación de Turno Rotativo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../controllers/horarioController.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" value="asignar_turno_masivo">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Empleado</label>
                        <select class="form-select bg-light border-0" name="empleado_id" required>
                            <option value="">-- Seleccionar Trabajador --</option>
                            <?php foreach($empleados as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['apellidos'] . ', ' . $e['nombres'] . ' (DNI: ' . $e['dni'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Turno a Rotar</label>
                        <select class="form-select bg-light border-0" name="turno_id" required>
                            <option value="">-- Seleccionar Horario Maestro --</option>
                            <?php foreach($turnos as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre_turno']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Desde</label>
                            <input type="date" class="form-control bg-light border-0" name="fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Hasta</label>
                            <input type="date" class="form-control bg-light border-0" name="fecha_fin" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-muted small text-uppercase">Repetir en estos días:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php 
                            $dias = ['Mon'=>'Lun', 'Tue'=>'Mar', 'Wed'=>'Mie', 'Thu'=>'Jue', 'Fri'=>'Vie', 'Sat'=>'Sab', 'Sun'=>'Dom'];
                            foreach($dias as $key => $nom): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="dias[]" value="<?= $key ?>" id="d<?= $key ?>" checked>
                                    <label class="form-check-label small" for="d<?= $key ?>"><?= $nom ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gradient-primary px-4">Programar Rotación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tablaAsignaciones')) {
            $('#tablaAsignaciones').DataTable({ "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" } });
        }
        if (!$.fn.DataTable.isDataTable('#tablaTurnosMaestros')) {
            $('#tablaTurnosMaestros').DataTable({ "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" } });
        }
    });

    function abrirModalTurno() {
        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTurnoMaestro'));
        modal.show();
    }

    function abrirModalAsignacion() {
        let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAsignacion'));
        modal.show();
    }

    function eliminarAsignacion(id) {
        Swal.fire({
            title: '¿Retirar Turno?', text: "Se eliminará la asignación horaria de esta fecha.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, retirar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../controllers/horarioController.php?accion=eliminar_asig&id=' + id;
            }
        });
    }

    function eliminarTurnoMaestro(id, nombre) {
        Swal.fire({
            title: '¿Eliminar Turno Maestro?', text: "Si eliminas '" + nombre + "', las asignaciones podrían verse afectadas.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../controllers/horarioController.php?accion=eliminar_turno&id=' + id;
            }
        });
    }
</script>