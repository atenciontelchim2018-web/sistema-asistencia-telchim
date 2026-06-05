<?php
// Consultar todos los empleados con su sede respectiva
$sql = "SELECT e.*, s.nombre_sede 
        FROM empleados e 
        LEFT JOIN sedes s ON e.sede_id = s.id 
        ORDER BY e.apellidos ASC";
$empleados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Consultar sedes disponibles para el formulario
$sql_sedes = "SELECT id, nombre_sede FROM sedes ORDER BY nombre_sede ASC";
$sedes = $pdo->query($sql_sedes)->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .card-empleados { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; animation: fadeInUp 0.6s ease-out; }
    .btn-nuevo { background: linear-gradient(135deg, #0984e3 0%, #0d47a1 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .btn-nuevo:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(9, 132, 227, 0.3); color: white; }
    .foto-perfil { width: 45px; height: 45px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s; }
    .foto-perfil:hover { transform: scale(1.5); z-index: 10; }
    .btn-accion { border-radius: 8px; transition: transform 0.2s; margin: 0 2px; }
    .btn-accion:hover { transform: translateY(-2px); }
    .modal.fade .modal-dialog { transition: transform 0.3s ease-out; transform: scale(0.9); }
    .modal.show .modal-dialog { transform: scale(1); }
</style>

<div class="card card-empleados p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0" style="color: #2c3e50; font-weight: 800;">
            <i class="fa-solid fa-users" style="color: #0984e3;"></i> Directorio de Empleados
        </h4>
        <button class="btn btn-nuevo" onclick="abrirModalNuevo()">
            <i class="fa-solid fa-user-plus"></i> Nuevo Empleado
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="tablaEmpleados">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th>Foto</th>
                    <th>DNI</th>
                    <th>Empleado</th>
                    <th>Cargo</th>
                    <th>Ubicación y Ciclo</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $emp): ?>
                <tr>
                    <td>
                        <?php $foto = empty($emp['foto_perfil']) ? 'default.jpg' : $emp['foto_perfil']; ?>
                        <img src="../uploads/fotos_empleados/<?php echo $foto; ?>" class="foto-perfil" alt="Foto" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($emp['nombres']); ?>&background=random'">
                    </td>
                    <td><strong><?php echo htmlspecialchars($emp['dni']); ?></strong></td>
                    <td>
                        <span style="color: #2c3e50; font-weight: 600;">
                            <?php echo htmlspecialchars($emp['apellidos'] . ', ' . $emp['nombres']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-info text-dark" style="border-radius: 8px; padding: 6px 12px;">
                            <?php echo htmlspecialchars($emp['cargo']); ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 13px;">
                            <i class="fa-solid fa-location-dot text-danger"></i> 
                            <strong><?= $emp['nombre_sede'] ? htmlspecialchars($emp['nombre_sede']) : 'Sin Sede' ?></strong><br>
                            <i class="fa-solid fa-clock text-primary"></i> Horario Base: <strong><?= $emp['tipo_horario'] ?? 'A' ?></strong>
                        </div>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light btn-accion text-dark border" title="Generar Fotocheck QR"
                                onclick='generarFotocheck(<?php echo json_encode($emp); ?>)'>
                            <i class="fa-solid fa-qrcode"></i> QR
                        </button>

                        <button class="btn btn-sm btn-light btn-accion text-primary border" title="Editar"
                                onclick='abrirModalEditar(<?php echo json_encode($emp); ?>)'>
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-sm btn-light btn-accion text-danger border" title="Eliminar"
                                onclick="eliminarEmpleado(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars($emp['nombres']); ?>')">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalFotocheck" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
            <div class="modal-body p-0 text-center bg-light" id="fotocheckPrintArea">
                </div>
            <div class="modal-footer justify-content-center bg-white border-0 p-3">
                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary rounded-pill px-4 fw-bold" onclick="imprimirFotocheck()">
                    <i class="fa-solid fa-print"></i> Imprimir Credencial
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEmpleado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: #f8f9fa; border-radius: 20px 20px 0 0; border-bottom: none;">
                <h5 class="modal-title" id="modalTitulo" style="font-weight: 800; color: #2c3e50;">
                    <i class="fa-solid fa-user-plus"></i> Registrar Empleado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEmpleado" action="../controllers/empleadoController.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="accion" id="formAccion" value="crear">
                    <input type="hidden" name="id_empleado" id="id_empleado">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Número de DNI</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-id-card"></i></span>
                                <input type="number" class="form-control border-start-0 bg-light" name="dni" id="dni" required placeholder="Ej: 71234567">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Cargo</label>
                            <select class="form-select bg-light border-0" name="cargo" id="cargo" required>
                                <option value="Asesor de Ventas">Asesor de Ventas</option>
                                <option value="Coordinador">Coordinador</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nombres</label>
                            <input type="text" class="form-control bg-light border-0" name="nombres" id="nombres" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Apellidos</label>
                            <input type="text" class="form-control bg-light border-0" name="apellidos" id="apellidos" required>
                        </div>
                    </div>

                    <hr class="text-muted">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase text-primary"><i class="fa-solid fa-location-dot"></i> Punto de Trabajo (Sede)</label>
                            <select class="form-select bg-light border-0" name="sede_id" id="sede_id" required>
                                <option value="">-- Seleccionar Sede --</option>
                                <?php foreach($sedes as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre_sede']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase text-primary"><i class="fa-solid fa-calendar-week"></i> Ciclo de Horario Base</label>
                            <select class="form-select bg-light border-0" name="tipo_horario" id="tipo_horario" required>
                                <option value="A">Horario A (Semanas Pares: Partido)</option>
                                <option value="B">Horario B (Semanas Pares: Continuo)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Foto de Perfil</label>
                        <input type="file" class="form-control border-0 bg-light" name="foto" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-nuevo px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#tablaEmpleados')) {
            $('#tablaEmpleados').DataTable({ "language": { "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" } });
        }
    });

    // --- LÓGICA PARA GENERAR LA CREDENCIAL QR ---
    function generarFotocheck(emp) {
        // Obtenemos la foto o generamos una genérica con sus iniciales
        let fotoUrl = emp.foto_perfil && emp.foto_perfil !== '' ? `../uploads/fotos_empleados/${emp.foto_perfil}` : `https://ui-avatars.com/api/?name=${encodeURIComponent(emp.nombres)}&background=random`;
        
        // Usamos la API de qrserver para generar un QR en imagen usando el DNI
        let qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${emp.dni}`;

        // Diseño del Fotocheck
        let htmlCredencial = `
            <div style="background: linear-gradient(135deg, #0f172a, #1e293b); color: white; padding: 20px; text-align: center;">
                <h5 style="margin: 0; font-weight: 900; letter-spacing: 1px;">TELCHIM S.A.C.</h5>
                <p style="margin: 0; font-size: 10px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Credencial de Acceso</p>
            </div>
            <div style="padding: 25px 20px; background: white;">
                <img src="${fotoUrl}" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 4px solid #0984e3; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h5 style="color: #0f172a; font-weight: 800; margin: 0; font-size: 18px;">${emp.nombres}</h5>
                <h6 style="color: #64748b; font-weight: 700; margin-bottom: 10px; font-size: 14px;">${emp.apellidos}</h6>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; padding: 6px 12px; font-size: 12px; border-radius: 20px; margin-bottom: 20px; display: inline-block;">${emp.cargo}</span>
                
                <div style="background: #f8fafc; padding: 15px; border-radius: 15px; border: 2px dashed #cbd5e1; display: inline-block;">
                    <img src="${qrUrl}" alt="Código QR" style="width: 130px; height: 130px;">
                </div>
                <p style="margin-top: 15px; margin-bottom: 0; font-weight: 900; color: #0f172a; letter-spacing: 3px; font-size: 16px;">DNI: ${emp.dni}</p>
            </div>
        `;

        document.getElementById('fotocheckPrintArea').innerHTML = htmlCredencial;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalFotocheck')).show();
    }

    function imprimirFotocheck() {
        let contenido = document.getElementById('fotocheckPrintArea').innerHTML;
        let ventana = window.open('', '', 'width=400,height=600');
        ventana.document.write('<html><head><title>Imprimir Credencial</title>');
        ventana.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">');
        ventana.document.write('</head><body style="padding: 20px; display: flex; justify-content: center; background: #f1f5f9;">');
        ventana.document.write('<div style="width: 320px; border: 1px solid #cbd5e1; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: white;">');
        ventana.document.write(contenido);
        ventana.document.write('</div></body></html>');
        ventana.document.close();
        ventana.focus();
        
        // Damos medio segundo para que la imagen del QR cargue desde internet antes de imprimir
        setTimeout(() => { 
            ventana.print(); 
            ventana.close(); 
        }, 800);
    }
    // ------------------------------------------

    function abrirModalNuevo() {
        document.getElementById('formEmpleado').reset();
        document.getElementById('formAccion').value = 'crear';
        document.getElementById('modalTitulo').innerHTML = '<i class="fa-solid fa-user-plus"></i> Registrar Empleado';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEmpleado')).show();
    }

    function abrirModalEditar(emp) {
        document.getElementById('formAccion').value = 'editar';
        document.getElementById('id_empleado').value = emp.id;
        document.getElementById('dni').value = emp.dni;
        document.getElementById('nombres').value = emp.nombres;
        document.getElementById('apellidos').value = emp.apellidos;
        document.getElementById('cargo').value = emp.cargo;
        document.getElementById('sede_id').value = emp.sede_id || '';
        document.getElementById('tipo_horario').value = emp.tipo_horario || 'A';
        document.getElementById('modalTitulo').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Editar Empleado';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEmpleado')).show();
    }

    function eliminarEmpleado(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?', text: "Se eliminará a " + nombre + " del sistema.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../controllers/empleadoController.php?accion=eliminar&id=' + id;
            }
        });
    }
</script>