<?php
session_start();
require_once __DIR__ . "/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $pdo = conectarDB();
    $stmt = $pdo->prepare("SELECT * FROM instructores WHERE usuario = ? AND password = ?");
    $stmt->execute([$usuario, $password]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instructor) {
        $_SESSION["instructor_logged"] = true;
        $_SESSION["instructor_nombre"] = $instructor["nombre_completo"];
        $_SESSION["instructor_rol"] = $instructor["rol"] ?? "instructor";
        header("Location: admin.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Panel de Instructores</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-card { max-width: 400px; margin: 80px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .login-card h2 { margin-bottom: 20px; color: #1c3d2e; text-align: center; }
        .login-card label { display: block; margin-bottom: 5px; font-weight: bold; }
        .login-card input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .login-card button { width: 100%; padding: 10px; background: #1c3d2e; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .login-card button:hover { background: #2e624c; }
        .error { color: #d9534f; margin-bottom: 15px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Acceso Instructores</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="usuario">Usuario:</label>
            <input type="text" name="usuario" id="usuario" required autocomplete="off">

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Ingresar</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            <a href="../index.html" style="color: #666; text-decoration: none;">&larr; Volver a la Biblioteca</a>
        </p>
    </div>
</body>
</html>
