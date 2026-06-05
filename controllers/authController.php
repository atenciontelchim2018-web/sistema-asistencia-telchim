<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $password_ingresada = trim($_POST['password']);

    // Buscamos al usuario en la base de datos
    $sql = "SELECT id, usuario, password FROM administradores WHERE usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Como estamos en desarrollo y para que puedas entrar AHORA MISMO, 
    // permitimos el acceso si escribes admin y password123.
    // LUEGO DEBES CAMBIAR ESTO POR SEGURIDAD ESTRICTA (password_verify).
    if ($admin && ($password_ingresada === 'password123' || password_verify($password_ingresada, $admin['password']))) {
        
        // ¡Login exitoso! Creamos la credencial virtual (Sesión)
        $_SESSION['admin_logeado'] = true;
        $_SESSION['admin_usuario'] = $admin['usuario'];
        
        // Lo enviamos directo a la sala de control
        header("Location: ../views/admin_rrhh.php");
        exit;
    } else {
        // Falló. Lo regresamos con un aviso de error
        header("Location: ../views/login.php?error=credenciales");
        exit;
    }
}
?>