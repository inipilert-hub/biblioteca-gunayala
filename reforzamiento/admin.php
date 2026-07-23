<?php
session_start();
if (!isset($_SESSION["instructor_logged"]) || $_SESSION["instructor_logged"] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/db.php";
$pdo = conectarDB();

$mensaje_exito = "";
$mensaje_error = "";

// Lógica para registrar un nuevo instructor (Solo si es admin)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "registrar_instructor") {
    if (($_SESSION["instructor_rol"] ?? "") === "admin") {
        $nuevo_usuario = trim($_POST["nuevo_usuario"] ?? "");
        $nuevo_password = trim($_POST["nuevo_password"] ?? "");
        $nuevo_nombre = trim($_POST["nuevo_nombre"] ?? "");
        $nuevo_rol = $_POST["nuevo_rol"] ?? "instructor";

        if ($nuevo_usuario !== "" && $nuevo_password !== "" && $nuevo_nombre !== "") {
            // Verificar si el usuario ya existe
            $check = $pdo->prepare("SELECT id FROM instructores WHERE usuario = ?");
            $check->execute([$nuevo_usuario]);
            if ($check->fetch()) {
                $mensaje_error = "El nombre de usuario ya está en uso.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO instructores (usuario, password, nombre_completo, rol) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$nuevo_usuario, $nuevo_password, $nuevo_nombre, $nuevo_rol])) {
                    $mensaje_exito = "¡Instructor registrado exitosamente!";
                } else {
                    $mensaje_error = "Error al registrar el instructor.";
                }
            }
        } else {
            $mensaje_error = "Todos los campos de registro son obligatorios.";
        }
    }
}

$materias = $pdo->query("SELECT * FROM materias")->fetchAll(PDO::FETCH_ASSOC);

$filtroAnio = trim($_GET["anio_escolar"] ?? "");
$filtroMateria = (int)($_GET["materia_id"] ?? 0);
$buscarEstudiante = trim($_GET["buscar"] ?? "");

$sql = "SELECT i.*, m.nombre AS materia_nombre 
        FROM intentos_test i 
        JOIN materias m ON m.id = i.materia_id 
        WHERE 1=1";
$params = [];

if ($filtroAnio !== "") {
    $sql .= " AND i.anio_escolar = ?";
    $params[] = $filtroAnio;
}

if ($filtroMateria > 0) {
    $sql .= " AND i.materia_id = ?";
    $params[] = $filtroMateria;
}

if ($buscarEstudiante !== "") {
    $sql .= " AND i.nombre_estudiante LIKE ?";
    $params[] = "%" . $buscarEstudiante . "%";
}

$sql .= " ORDER BY i.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$intentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Instructores - Resultados de Diagnóstico</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .filter-form { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 13px; margin-bottom: 3px; font-weight: bold; color: #333; }
        .filter-form input, .filter-form select { padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1c3d2e; color: #fff; }
        tr:hover { background: #f9f9f9; }
        .btn-logout { background: #d9534f; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; }
        .btn-logout:hover { background: #c9302c; }
        .alert-success { background: #dff0d8; color: #3c763d; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-error { background: #f2dede; color: #a94442; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <div class="header-bar">
            <div>
                <h1>Panel de Instructores</h1>
                <p style="margin: 0; color: #666;">Bienvenido, <?= htmlspecialchars($_SESSION["instructor_nombre"]) ?> (<?= ucfirst($_SESSION["instructor_rol"]) ?>)</p>
            </div>
            <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>

        <?php if (!empty($mensaje_exito)): ?>
            <div class="alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php endif; ?>
        <?php if (!empty($mensaje_error)): ?>
            <div class="alert-error"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <?php if (($_SESSION["instructor_rol"] ?? "") === "admin"): ?>
            <div class="card">
                <h3 style="margin-top: 0; color: #1c3d2e;">Registrar Nuevo Instructor</h3>
                <form method="post" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
                    <input type="hidden" name="accion" value="registrar_instructor">
                    <div class="filter-group">
                        <label for="nuevo_nombre">Nombre Completo:</label>
                        <input type="text" name="nuevo_nombre" id="nuevo_nombre" required placeholder="Ej. Juan Pérez">
                    </div>
                    <div class="filter-group">
                        <label for="nuevo_usuario">Usuario:</label>
                        <input type="text" name="nuevo_usuario" id="nuevo_usuario" required autocomplete="off" placeholder="usuario.sistema">
                    </div>
                    <div class="filter-group">
                        <label for="nuevo_password">Contraseña:</label>
                        <input type="password" name="nuevo_password" id="nuevo_password" required placeholder="******">
                    </div>
                    <div class="filter-group">
                        <label for="nuevo_rol">Rol:</label>
                        <select name="nuevo_rol" id="nuevo_rol">
                            <option value="instructor">Instructor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="btn" style="padding: 9px 16px; height: 38px;">Crear Usuario</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-top: 0; color: #1c3d2e;">Filtrar Diagnósticos de Estudiantes</h3>
            <form method="get" class="filter-form">
                <div class="filter-group">
                    <label for="buscar">Buscar Estudiante:</label>
                    <input type="text" name="buscar" id="buscar" value="<?= htmlspecialchars($buscarEstudiante) ?>" placeholder="Nombre...">
                </div>

                <div class="filter-group">
                    <label for="anio_escolar">Año Escolar:</label>
                    <select name="anio_escolar" id="anio_escolar">
                        <option value="">-- Todos --</option>
                        <?php for($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>° Año" <?= $filtroAnio === "$i° Año" ? "selected" : "" ?>><?= $i ?>° Año</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="materia_id">Materia:</label>
                    <select name="materia_id" id="materia_id">
                        <option value="">-- Todas --</option>
                        <?php foreach($materias as $mat): ?>
                            <option value="<?= $mat["id"] ?>" <?= $filtroMateria === $mat["id"] ? "selected" : "" ?>><?= htmlspecialchars($mat["nombre"]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 5px;">
                    <button type="submit" class="btn" style="padding: 9px 16px; height: 38px;">Filtrar</button>
                    <a href="admin.php" style="padding: 9px 12px; height: 38px; background: #e0e0e0; color: #333; text-decoration: none; border-radius: 4px; box-sizing: border-box; display: inline-flex; align-items: center;">Limpiar</a>
                </div>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Estudiante</th>
                    <th>Año Escolar</th>
                    <th>Materia</th>
                    <th>Puntaje</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($intentos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #777;">No hay registros encontrados con los filtros seleccionados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($intentos as $row): ?>
                        <tr>
                            <td>#<?= $row["id"] ?></td>
                            <td><?= htmlspecialchars($row["nombre_estudiante"]) ?></td>
                            <td><strong><?= htmlspecialchars($row["anio_escolar"]) ?></strong></td>
                            <td><?= htmlspecialchars($row["materia_nombre"]) ?></td>
                            <td><?= $row["puntaje_total"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <p style="margin-top: 20px;">
            <a href="../index.html" class="btn">&larr; Volver al inicio de la plataforma</a>
        </p>
    </div>
</body>
</html>
