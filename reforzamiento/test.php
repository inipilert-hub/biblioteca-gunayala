<?php
require_once __DIR__ . "/db.php";
$pdo = conectarDB();

$nombre = trim($_GET["nombre"] ?? "");
$materiaId = (int)($_GET["materia_id"] ?? 0);
$anioEscolar = trim($_GET["anio_escolar"] ?? "");
$temaSeleccionado = trim($_GET["tema"] ?? "");

if ($nombre === "" || $materiaId <= 0 || $anioEscolar === "" || $temaSeleccionado === "") {
    header("Location: index.php");
    exit;
}

$stmtMateria = $pdo->prepare("SELECT * FROM materias WHERE id = ?");
$stmtMateria->execute([$materiaId]);
$materia = $stmtMateria->fetch();

if (!$materia) {
    die("Materia no encontrada.");
}

// Filtrar las preguntas por materia y por el tema exacto seleccionado
$stmtPreguntas = $pdo->prepare(
    "SELECT p.*, t.nombre AS tema_nombre
     FROM preguntas p
     JOIN temas t ON t.id = p.tema_id
     WHERE t.materia_id = ? AND t.nombre = ?
     ORDER BY p.id"
);
$stmtPreguntas->execute([$materiaId, $temaSeleccionado]);
$preguntas = $stmtPreguntas->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Diagnóstico - <?= htmlspecialchars($materia["nombre"]) ?></title>
<style>
    body { font-family: "Segoe UI", Arial, sans-serif; background: #f0f2f5; margin: 0; }
    header { background: #1c3d2e; color: #fff; padding: 25px 20px; text-align: center; }
    .contenedor { max-width: 700px; margin: 30px auto; padding: 0 20px 40px; }
    .pregunta {
        background: #fff; border-radius: 10px; padding: 20px 24px; margin-bottom: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .tema-etiqueta { font-size: 0.8em; color: #888; text-transform: uppercase; letter-spacing: 0.5px; }
    .enunciado { font-weight: bold; margin: 8px 0 14px; color: #1c3d2e; }
    .opcion { display: block; padding: 8px 0; cursor: pointer; }
    .opcion input { margin-right: 10px; }
    button {
        background: #1c3d2e; color: #fff; border: none; padding: 14px 28px;
        border-radius: 6px; font-size: 1em; cursor: pointer; width: 100%; margin-top: 10px;
    }
    button:hover { background: #14291f; }
    .alerta { background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; text-align: center; margin-bottom: 20px; }
</style>
</head>
<body>
<header>
    <h1>Diagnóstico de <?= htmlspecialchars($materia["nombre"]) ?></h1>
    <p>¡Hola, <?= htmlspecialchars($nombre) ?>! (<?= htmlspecialchars($anioEscolar) ?> - Tema: <strong><?= htmlspecialchars($temaSeleccionado) ?></strong>)</p>
</header>
<div class="contenedor">
<form action="procesar_test.php" method="post">
    <input type="hidden" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
    <input type="hidden" name="materia_id" value="<?= htmlspecialchars($materiaId) ?>">
    <input type="hidden" name="anio_escolar" value="<?= htmlspecialchars($anioEscolar) ?>">
    <input type="hidden" name="tema" value="<?= htmlspecialchars($temaSeleccionado) ?>">

    <?php if (empty($preguntas)): ?>
        <div class="alerta">No hay preguntas registradas todavía para el tema <strong><?= htmlspecialchars($temaSeleccionado) ?></strong>. Por favor, selecciona otro tema o añade preguntas en la base de datos.</div>
    <?php else: ?>
        <?php foreach ($preguntas as $p): ?>
            <div class="pregunta">
                <div class="tema-etiqueta"><?= htmlspecialchars($p["tema_nombre"]) ?></div>
                <div class="enunciado"><?= htmlspecialchars($p["enunciado"]) ?></div>
                <?php foreach (["a","b","c","d"] as $letra): ?>
                    <label class="opcion">
                        <input type="radio" name="respuesta[<?= $p["id"] ?>]" value="<?= $letra ?>" required>
                        <?= htmlspecialchars($p["opcion_$letra"]) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit">Ver mis resultados</button>
    <?php endif; ?>
</form>
</div>
</body>
</html>
