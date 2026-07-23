<?php
require_once __DIR__ . "/db.php";
$pdo = conectarDB();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$nombre = trim($_POST["nombre"] ?? "");
$materiaId = (int)($_POST["materia_id"] ?? 0);
$anioEscolar = trim($_POST["anio_escolar"] ?? "");
$respuestasUsuario = $_POST["respuesta"] ?? [];

if ($nombre === "" || $materiaId <= 0 || $anioEscolar === "" || empty($respuestasUsuario)) {
    header("Location: index.php");
    exit;
}

$idsPreguntas = array_keys($respuestasUsuario);
if (empty($idsPreguntas)) {
    header("Location: index.php");
    exit;
}

$placeholders = implode(",", array_fill(0, count($idsPreguntas), "?"));
$stmt = $pdo->prepare("SELECT id, tema_id, respuesta_correcta FROM preguntas WHERE id IN ($placeholders)");
$stmt->execute($idsPreguntas);
$preguntasDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapl = [];
foreach ($preguntasDb as $p) {
    $mapl[$p["id"]] = $p;
}

$puntajeTotal = 0;
$totalPreguntas = count($preguntasDb);
$temasFallados = [];
$detalleResultados = [];

foreach ($respuestasUsuario as $idPregunta => $respUser) {
    if (isset($mapl[$idPregunta])) {
        $correcta = $mapl[$idPregunta]["respuesta_correcta"];
        $temaId = $mapl[$idPregunta]["tema_id"];
        $esCorrecta = (trim(strtolower($respUser)) === trim(strtolower($correcta)));

        if ($esCorrecta) {
            $puntajeTotal++;
        } else {
            if ($temaId) {
                $temasFallados[$temaId] = true;
            }
        }
        $detalleResultados[$idPregunta] = [
            "respuesta_usuario" => $respUser,
            "respuesta_correcta" => $correcta,
            "correcta" => $esCorrecta
        ];
    }
}

// Detectar el tema según el año escolar seleccionado de forma flexible
$temaAsignado = "Evaluación general de matemática";
if (strpos($anioEscolar, "1° Año") !== false) {
    $temaAsignado = "Números enteros / Álgebra básica";
} elseif (strpos($anioEscolar, "2° Año") !== false) {
    $temaAsignado = "Productos notables / Ecuaciones lineales";
} elseif (strpos($anioEscolar, "3° Año") !== false) {
    $temaAsignado = "Factorización avanzada / Ecuaciones cuadráticas";
} elseif (strpos($anioEscolar, "4° Año") !== false) {
    $temaAsignado = "Funciones y Trigonometría básica";
} elseif (strpos($anioEscolar, "5° Año") !== false) {
    $temaAsignado = "Trigonometría avanzada y Matrices";
} elseif (strpos($anioEscolar, "6° Año") !== false) {
    $temaAsignado = "Límites y Derivadas";
}

// Guardar el intento en la base de datos incluyendo el año escolar y el tema
$detalleJson = json_encode($detalleResultados);
$stmtInsert = $pdo->prepare("INSERT INTO intentos_test (nombre_estudiante, materia_id, anio_escolar, tema, puntaje_total, detalle_json) VALUES (?, ?, ?, ?, ?, ?)");
$stmtInsert->execute([$nombre, $materiaId, $anioEscolar, $temaAsignado, $puntajeTotal, $detalleJson]);

$idsTemasFallados = array_keys($temasFallados);
$paramsRevisar = [$materiaId];

if (!empty($idsTemasFallados)) {
    $sqlMaterial = "SELECT * FROM material_reforzamiento WHERE materia_id = ? AND (tema_id IN (" . implode(",", array_fill(0, count($idsTemasFallados), "?")) . ") OR tema_id IS NULL)";
    $paramsRevisar = array_merge($paramsRevisar, $idsTemasFallados);
} else {
    $sqlMaterial = "SELECT * FROM material_reforzamiento WHERE materia_id = ? AND tema_id IS NULL";
}

$stmtMat = $pdo->prepare($sqlMaterial);
$stmtMat->execute($paramsRevisar);
$materiales = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

if (empty($materiales)) {
    $stmtFallback = $pdo->prepare("SELECT * FROM material_reforzamiento WHERE materia_id = ?");
    $stmtFallback->execute([$materiaId]);
    $materiales = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado del Diagnóstico</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Resultado del Diagnóstico</h1>
        <p>Estudiante: <strong><?php echo htmlspecialchars($nombre, ENT_QUOTES, "UTF-8"); ?></strong> (<?php echo htmlspecialchars($anioEscolar, ENT_QUOTES, "UTF-8"); ?>)</p>
        <p>Tema Evaluado: <strong><?php echo htmlspecialchars($temaAsignado, ENT_QUOTES, "UTF-8"); ?></strong></p>
        <p>Tu puntaje obtenido es: <strong><?php echo $puntajeTotal; ?></strong> de <strong><?php echo $totalPreguntas; ?></strong></p>

        <h2>Material de Reforzamiento Recomendado</h2>
        <?php if (!empty($materiales)): ?>
            <ul>
                <?php foreach ($materiales as $mat): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($mat["titulo"], ENT_QUOTES, "UTF-8"); ?></strong><br>
                        <?php echo htmlspecialchars($mat["descripcion"] ?? "", ENT_QUOTES, "UTF-8"); ?><br>
                        <?php if (!empty($mat["url"])): ?>
                            <a href="<?php echo htmlspecialchars($mat["url"], ENT_QUOTES, "UTF-8"); ?>" target="_blank" class="btn" style="padding: 8px 16px; font-size: 0.9em;">Ver recurso externo &rarr;</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>¡Excelente trabajo! No tienes temas pendientes de reforzamiento básico en esta materia.</p>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="index.php" class="btn">&larr; Realizar otro diagnóstico</a>
        </p>
    </div>
</body>
</html>
