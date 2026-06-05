<?php
require_once '../config/db.php';

// Consultamos todos los empleados registrados
$sql = "SELECT dni, nombres, apellidos, cargo, foto_perfil FROM empleados ORDER BY apellidos ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotochecks | Telchim S.A.C.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }

        /* Ocultar botones al imprimir */
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; padding: 0; }
            .credencial { box-shadow: none !important; border: 1px solid #ccc; page-break-inside: avoid; }
        }

        .controles {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-imprimir {
            background-color: #00b894;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .btn-imprimir:hover { background-color: #00a080; transform: translateY(-2px); }

        .grid-credenciales {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Diseño del Fotocheck (Tamaño estándar CR80 aproximado) */
        .credencial {
            background: white;
            width: 220px;
            height: 350px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            border: 2px solid #e1e5eb;
        }

        /* Cabecera corporativa */
        .cred-header {
            background: #1e3c72; /* Color corporativo */
            width: 100%;
            padding: 15px 0;
            text-align: center;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }

        .cred-header img {
            max-width: 120px;
            height: 30px;
            object-fit: contain;
            filter: brightness(0) invert(1); /* Pone el logo en blanco */
        }

        /* Datos del empleado */
        .cred-body {
            padding: 15px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .nombres { font-size: 16px; font-weight: 900; color: #2d3436; margin: 5px 0 2px 0; line-height: 1.1; }
        .cargo { font-size: 12px; color: #00b894; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .dni { font-size: 11px; color: #636e72; margin-bottom: 15px; }

        /* El Código QR */
        .qr-container {
            background: white;
            padding: 5px;
            border: 2px solid #dfe6e9;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .qr-container img { width: 100px; height: 100px; }

        /* Pie de tarjeta */
        .cred-footer {
            background: #f8f9fa;
            width: 100%;
            padding: 8px 0;
            text-align: center;
            font-size: 10px;
            color: #b2bec3;
            border-top: 1px solid #edf2f9;
        }
    </style>
</head>
<body>

    <div class="controles no-print">
        <h2>Generador de Fotochecks QR</h2>
        <p>Se han encontrado <?php echo count($empleados); ?> empleados en el sistema.</p>
        <button class="btn-imprimir" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir Credenciales
        </button>
    </div>

    <div class="grid-credenciales">
        <?php foreach ($empleados as $emp): ?>
            <div class="credencial">
                <div class="cred-header">
                    <img src="../assets/img/logo.png" alt="TELCHIM S.A.C." onerror="this.style.display='none'; this.nextSibling.style.display='block';">
                    <span style="display:none;">TELCHIM S.A.C.</span>
                </div>
                
                <div class="cred-body">
                    <div class="qr-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($emp['dni']); ?>" alt="QR Code">
                    </div>
                    
                    <div class="nombres"><?php echo htmlspecialchars($emp['nombres'] . ' ' . $emp['apellidos']); ?></div>
                    <div class="cargo"><?php echo htmlspecialchars($emp['cargo']); ?></div>
                    <div class="dni">DNI: <?php echo htmlspecialchars($emp['dni']); ?></div>
                </div>

                <div class="cred-footer">
                    Uso exclusivo del personal
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($empleados) == 0): ?>
            <div style="grid-column: 1 / -1; text-align: center; color: #636e72;">
                <p>No hay empleados registrados en la base de datos.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>