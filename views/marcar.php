<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Biométrico | Telchim S.A.C.</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <style>
        :root {
            --bg-dark: #0f111a;
            --card-glass: rgba(25, 30, 45, 0.7);
            --border-glass: rgba(255, 255, 255, 0.1);
            --primary: #00f2fe;
            --primary-dark: #4facfe;
            --success: #00E676;
            --danger: #FF1744;
            --warning: #FF9100;
            --text-main: #ffffff;
            --text-muted: #94a3b8;
        }

        /* Fondo animado futurista */
        @keyframes gradientPan {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes floatUp {
            0% { opacity: 0; transform: translateY(30px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(-45deg, #090b10, #16102b, #0d1b2a, #0b1a20);
            background-size: 400% 400%;
            animation: gradientPan 20s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            color: var(--text-main);
        }

        /* Contenedor Vertical Glassmorphism */
        .card-asistencia {
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px 30px;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.2);
            width: 100%;
            max-width: 450px;
            animation: floatUp 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid var(--border-glass);
        }

        .logo-empresa { text-align: center; margin-bottom: 30px; }
        .logo-empresa img {
            max-width: 140px;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 15px rgba(255,255,255,0.2));
        }
        .logo-empresa h2 { margin: 0; font-size: 22px; font-weight: 900; letter-spacing: 2px; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .logo-empresa p { color: var(--primary); font-size: 11px; margin-top: 5px; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; }

        /* Cuadrícula de Botones 3D */
        .tipo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }

        .tipo-card {
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid var(--border-glass);
            border-radius: 22px;
            padding: 20px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        /* Estilo de los Iconos 3D */
        .tipo-card img.icon-3d { 
            width: 55px; 
            height: 55px; 
            object-fit: contain;
            margin-bottom: 12px; 
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            filter: drop-shadow(0 10px 10px rgba(0,0,0,0.4));
        }

        .tipo-card span { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

        .tipo-card:hover { background: rgba(255, 255, 255, 0.08); transform: translateY(-3px); }
        .tipo-card:hover img.icon-3d { transform: scale(1.15) rotate(-5deg); }

        /* Estados Activos de los Botones 3D */
        .tipo-card.active[data-tipo="entrada"] { border-color: var(--success); background: rgba(0, 230, 118, 0.1); box-shadow: 0 10px 25px rgba(0, 230, 118, 0.2), inset 0 0 15px rgba(0, 230, 118, 0.1); transform: scale(1.02); }
        .tipo-card.active[data-tipo="entrada"] span { color: var(--success); }

        .tipo-card.active[data-tipo="salida"] { border-color: var(--danger); background: rgba(255, 23, 68, 0.1); box-shadow: 0 10px 25px rgba(255, 23, 68, 0.2), inset 0 0 15px rgba(255, 23, 68, 0.1); transform: scale(1.02); }
        .tipo-card.active[data-tipo="salida"] span { color: var(--danger); }

        .tipo-card.active[data-tipo="salida_almorzar"], .tipo-card.active[data-tipo="regreso_almuerzo"] { border-color: var(--warning); background: rgba(255, 145, 0, 0.1); box-shadow: 0 10px 25px rgba(255, 145, 0, 0.2), inset 0 0 15px rgba(255, 145, 0, 0.1); transform: scale(1.02); }
        .tipo-card.active[data-tipo="salida_almorzar"] span, .tipo-card.active[data-tipo="regreso_almuerzo"] span { color: var(--warning); }

        /* Elementos del Formulario Adaptados a Dark Mode */
        .seccion-titulo { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px; border-bottom: 1px solid var(--border-glass); padding-bottom: 5px; }
        
        .input-group-custom { display: flex; align-items: center; background: rgba(0, 0, 0, 0.2); border: 2px solid var(--border-glass); border-radius: 15px; margin-bottom: 20px; padding: 5px 15px; transition: all 0.3s; }
        .input-group-custom:focus-within { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(0, 242, 254, 0.15); background: rgba(0, 0, 0, 0.4); }
        .input-group-custom i { color: var(--text-muted); font-size: 18px; margin-right: 15px; }
        .input-group-custom input { width: 100%; padding: 14px 0; border: none; background: transparent; font-size: 15px; font-weight: 600; outline: none; color: var(--text-main); }
        .input-group-custom input::placeholder { color: rgba(255,255,255,0.3); }

        input[type="file"] { font-size: 13px; color: var(--text-muted); }
        input[type="file"]::file-selector-button { background: rgba(255,255,255,0.1); color: white; border: none; padding: 10px 15px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; margin-right: 12px; }
        input[type="file"]::file-selector-button:hover { background: rgba(255,255,255,0.2); }

        #reader { width: 100%; border-radius: 15px; overflow: hidden; display: none; margin-bottom: 15px; border: 2px solid var(--primary); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        
        .btn-qr { background: rgba(0, 242, 254, 0.1); color: var(--primary); border: 2px dashed var(--primary); padding: 15px; width: 100%; border-radius: 15px; font-weight: 800; cursor: pointer; transition: all 0.2s; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .btn-qr:hover { background: rgba(0, 242, 254, 0.2); border-style: solid; transform: translateY(-2px); }

        /* Botón de Envío con Efecto Físico 3D */
        .btn-submit { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); 
            color: #000; 
            border: none; 
            padding: 18px; 
            width: 100%; 
            border-radius: 16px; 
            font-size: 16px; 
            font-weight: 900; 
            cursor: pointer; 
            transition: all 0.1s; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            gap: 10px;
            /* Efecto Botón Físico */
            box-shadow: 0 6px 0 #0277bd, 0 15px 20px rgba(0,0,0,0.4);
            margin-top: 10px;
        }
        .btn-submit:active { 
            transform: translateY(6px); 
            box-shadow: 0 0px 0 #0277bd, 0 5px 10px rgba(0,0,0,0.4); 
        }

        #cajaMotivo { display: none; margin-bottom: 20px; animation: floatUp 0.3s ease; }
        #cajaMotivo textarea { width: 100%; padding: 15px; border: 2px solid var(--danger); border-radius: 15px; font-family: inherit; font-size: 14px; box-sizing: border-box; outline: none; resize: none; background: rgba(255, 23, 68, 0.05); color: white; transition: 0.3s; }
        #cajaMotivo textarea::placeholder { color: rgba(255,255,255,0.4); }
        #cajaMotivo textarea:focus { background: rgba(255, 23, 68, 0.1); box-shadow: 0 0 15px rgba(255, 23, 68, 0.2); }

        .alerta-gps { font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 6px; font-weight: 600; }
    </style>
</head>
<body>

    <div class="card-asistencia">
        <div class="logo-empresa">
            <img src="../assets/img/logo.png" alt="Logo Telchim" onerror="this.src='https://via.placeholder.com/200x60/ffffff/000000?text=TELCHIM+S.A.C.'">
            <h2>TELCHIM S.A.C.</h2>
            <p>Control Biométrico</p>
        </div>

        <form id="formAsistencia">
            <input type="hidden" name="tipo_registro" id="tipo_registro_hidden" value="entrada">

            <div class="seccion-titulo">1. Tipo de Acción</div>
            <div class="tipo-grid">
                <!-- SECCIÓN DE ICONOS 3D: Carga tu imagen o usa los Emojis 3D por defecto como fallback -->
                <div class="tipo-card active" data-tipo="entrada" onclick="seleccionarTipo('entrada', this)">
                    <img src="../assets/img/entrada_3d.png" class="icon-3d" alt="Entrada" onerror="this.src='https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Check%20mark%20button/3D/check_mark_button_3d.png'">
                    <span>Entrada</span>
                </div>
                <div class="tipo-card" data-tipo="salida_almorzar" onclick="seleccionarTipo('salida_almorzar', this)">
                    <img src="../assets/img/almuerzo_ida_3d.png" class="icon-3d" alt="Inicio Almuerzo" onerror="this.src='https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Hot%20beverage/3D/hot_beverage_3d.png'">
                    <span>Ida Almuerzo</span>
                </div>
                <div class="tipo-card" data-tipo="regreso_almuerzo" onclick="seleccionarTipo('regreso_almuerzo', this)">
                    <img src="../assets/img/almuerzo_regreso_3d.png" class="icon-3d" alt="Fin Almuerzo" onerror="this.src='https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Bento%20box/3D/bento_box_3d.png'">
                    <span>Fin Almuerzo</span>
                </div>
                <div class="tipo-card" data-tipo="salida" onclick="seleccionarTipo('salida', this)">
                    <img src="../assets/img/salida_3d.png" class="icon-3d" alt="Salida" onerror="this.src='https://raw.githubusercontent.com/microsoft/fluentui-emoji/main/assets/Stop%20sign/3D/stop_sign_3d.png'">
                    <span>Salida Final</span>
                </div>
            </div>

            <div id="cajaMotivo">
                <div class="seccion-titulo text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Detalle su Permiso</div>
                <textarea name="motivo_permiso" id="motivo_permiso" rows="2" placeholder="Describa el motivo por el cual se retira antes de su hora programada..."></textarea>
            </div>

            <div class="seccion-titulo">2. Identificación</div>
            <label style="font-size: 11px; color: var(--text-muted); display: block; margin-bottom: 12px; text-align: center;">Escanee su QR o digite su DNI</label>
            
            <button type="button" class="btn-qr" id="btnEscanear">
                <i class="fa-solid fa-qrcode"></i> Abrir Lector QR
            </button>
            <div id="reader"></div>
            
            <div class="input-group-custom">
                <i class="fa-solid fa-fingerprint"></i>
                <input type="text" name="empleado_id" id="empleado_id" placeholder="ID / DNI del Empleado..." required>
            </div>

            <div class="input-group-custom">
                <i class="fa-solid fa-camera"></i>
                <input type="file" name="selfie" id="selfie" accept="image/*" capture="user" required>
            </div>

            <input type="hidden" name="latitud" id="latitud">
            <input type="hidden" name="longitud" id="longitud">

            <button type="submit" class="btn-submit">
                Registrar Ahora <i class="fa-solid fa-paper-plane"></i>
            </button>

            <div class="alerta-gps">
                <i class="fa-solid fa-satellite text-primary"></i> Sistema geolocalizado en tiempo real.
            </div>
        </form>
    </div>

    <script>
        function seleccionarTipo(tipo, elemento) {
            let tarjetas = document.querySelectorAll('.tipo-card');
            tarjetas.forEach(t => t.classList.remove('active'));
            elemento.classList.add('active');
            document.getElementById('tipo_registro_hidden').value = tipo;

            const cajaMotivo = document.getElementById('cajaMotivo');
            const inputMotivo = document.getElementById('motivo_permiso');
            
            if (tipo === 'salida') {
                cajaMotivo.style.display = 'block';
                inputMotivo.setAttribute('required', 'required');
            } else {
                cajaMotivo.style.display = 'none';
                inputMotivo.removeAttribute('required');
                inputMotivo.value = ''; 
            }
        }

        const html5QrCode = new Html5Qrcode("reader");
        document.getElementById('btnEscanear').addEventListener('click', function() {
            document.getElementById('reader').style.display = 'block';
            this.style.display = 'none';

            html5QrCode.start(
                { facingMode: { exact: "environment" } },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    document.getElementById('empleado_id').value = decodedText;
                    html5QrCode.stop();
                    document.getElementById('reader').style.display = 'none';
                    let btn = document.getElementById('btnEscanear');
                    btn.style.display = 'block';
                    btn.style.background = 'rgba(0, 230, 118, 0.1)';
                    btn.style.borderColor = '#00E676';
                    btn.style.color = '#00E676';
                    btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> QR Escaneado';
                },
                (errorMessage) => {}
            ).catch((err) => {
                html5QrCode.start(
                    { facingMode: "user" },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => {
                        document.getElementById('empleado_id').value = decodedText;
                        html5QrCode.stop();
                        document.getElementById('reader').style.display = 'none';
                        let btn = document.getElementById('btnEscanear');
                        btn.style.display = 'block';
                        btn.style.background = 'rgba(0, 230, 118, 0.1)';
                        btn.style.borderColor = '#00E676';
                        btn.style.color = '#00E676';
                        btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> QR Escaneado';
                    },
                    (errorMessage) => {}
                ).catch((err2) => {
                    document.getElementById('reader').style.display = 'none';
                    document.getElementById('btnEscanear').style.display = 'block';
                    Swal.fire({
                        title: 'Sin Acceso a Cámara',
                        text: 'Digite su DNI manualmente en el recuadro.',
                        icon: 'info',
                        background: '#1e293b',
                        color: '#fff'
                    });
                });
            });
        });

        document.getElementById('formAsistencia').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!document.getElementById('empleado_id').value) {
                return Swal.fire({title: 'Falta DNI', text: 'Escriba su DNI o escanee el QR.', icon: 'warning', background: '#1e293b', color: '#fff'});
            }

            if (!navigator.geolocation) {
                return Swal.fire({title: 'Error', text: 'Su dispositivo no soporta GPS.', icon: 'error', background: '#1e293b', color: '#fff'});
            }

            Swal.fire({ title: 'Procesando...', text: 'Validando ubicación', allowOutsideClick: false, background: '#1e293b', color: '#fff', didOpen: () => { Swal.showLoading(); } });

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitud').value = position.coords.latitude;
                    document.getElementById('longitud').value = position.coords.longitude;

                    let formData = new FormData(document.getElementById('formAsistencia'));
                    
                    fetch('../controllers/asistenciaController.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({ 
                            title: data.status === 'success' ? 'Éxito' : 'Atención', 
                            text: data.message, 
                            icon: data.status,
                            background: '#1e293b', 
                            color: '#fff',
                            confirmButtonColor: '#00f2fe'
                        }).then(() => { 
                            if (data.status === 'success') location.reload(); 
                        });
                    })
                    .catch(err => {
                        Swal.fire({title: 'Error de Red', text: 'Problema al conectar al servidor.', icon: 'error', background: '#1e293b', color: '#fff'});
                    });
                },
                function(error) { 
                    Swal.fire({title: 'GPS Requerido', text: 'Active su ubicación para marcar.', icon: 'error', background: '#1e293b', color: '#fff'}); 
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        });
    </script>
</body>
</html>