<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo | Telchim</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.95) translateY(20px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #1f4037 0%, #99f2c8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            animation: fadeInScale 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .logo-container { text-align: center; margin-bottom: 30px; }
        .logo-container img { max-width: 120px; animation: fadeInScale 0.8s ease-out; }
        
        .header-title { text-align: center; margin-bottom: 30px; }
        .header-title h2 { color: #2c3e50; margin: 0; font-size: 22px; font-weight: 800; text-transform: uppercase; }
        .header-title p { color: #7f8c8d; font-size: 14px; margin-top: 5px; }

        .input-group { display: flex; align-items: center; background: #f1f2f6; border-radius: 12px; margin-bottom: 20px; padding: 5px 15px; transition: all 0.3s; border: 2px solid transparent; }
        .input-group:focus-within { border-color: #00b894; background: #fff; box-shadow: 0 5px 15px rgba(0, 184, 148, 0.1); }
        .input-group i { color: #7f8c8d; font-size: 18px; margin-right: 15px; }
        .input-group input { width: 100%; padding: 12px 0; border: none; background: transparent; font-size: 15px; font-weight: 600; outline: none; color: #2c3e50; }

        .btn-submit { background: #00b894; color: white; border: none; padding: 16px; width: 100%; border-radius: 12px; font-size: 16px; font-weight: 800; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 12px 20px rgba(0, 184, 148, 0.4); }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="../assets/img/logo.png" alt="Logo Telchim" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3061/3061358.png'">
        </div>

        <div class="header-title">
            <h2>Panel Maestro</h2>
            <p>Acceso seguro de Recursos Humanos</p>
        </div>

        <form action="../controllers/authController.php" method="POST" id="formLogin">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="usuario" placeholder="Usuario administrador" required autocomplete="off">
            </div>

            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>

            <button type="submit" class="btn-submit">
                Ingresar al Sistema <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error') === 'credenciales') {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: 'El usuario o contraseña son incorrectos.',
                confirmButtonColor: '#00b894'
            });
            window.history.replaceState({}, document.title, "login.php");
        }
    </script>
</body>
</html>