<?php
require_once __DIR__ . "/db.php";
$pdo = conectarDB();
$materias = $pdo->query("SELECT * FROM materias ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Material de Reforzamiento - Biblioteca Virtual Comunitaria</title>
<style>
    body { font-family: "Segoe UI", Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
    header { background: #1c3d2e; color: #fff; padding: 30px 20px; text-align: center; }
    header h1 { margin: 0; font-size: 1.8em; }
    .contenedor { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 10px;
                  padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    label { font-weight: bold; display: block; margin-bottom: 8px; color: #1c3d2e; }
    input[type=text], select {
        width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc;
        border-radius: 6px; box-sizing: border-box; font-size: 1em;
    }
    button {
        background: #1c3d2e; color: #fff; border: none; padding: 12px 24px;
        border-radius: 6px; font-size: 1em; cursor: pointer; width: 100%;
    }
    button:hover { background: #14291f; }
    .volver { display: inline-block; margin-top: 20px; color: #1c3d2e; text-decoration: none; }
</style>
<script>
    const temasPorGrado = {
        "7° Año (Primer Año)": [
            "Números enteros",
            "Fracciones y decimales",
            "Razones y proporciones",
            "Porcentajes",
            "Potencias y raíces",
            "Álgebra básica (variables y expresiones)",
            "Ecuaciones de primer grado",
            "Perímetro y área de figuras planas",
            "Introducción a la geometría",
            "Estadística básica (tablas y gráficas)"
        ],
        "8° Año (Segundo Año)": [
            "Operaciones con expresiones algebraicas",
            "Productos notables",
            "Factorización básica",
            "Ecuaciones lineales",
            "Sistemas de ecuaciones sencillos",
            "Funciones lineales",
            "Teorema de Pitágoras",
            "Ángulos y triángulos",
            "Polígonos",
            "Probabilidad básica"
        ],
        "9° Año (Tercer Año)": [
            "Factorización avanzada",
            "Fracciones algebraicas",
            "Ecuaciones cuadráticas",
            "Funciones lineales y cuadráticas",
            "Sistemas de ecuaciones",
            "Radicales",
            "Geometría analítica básica",
            "Circunferencia y círculo",
            "Estadística descriptiva"
        ],
        "10° Año (Cuarto Año - Inicio de Bachillerato)": [
            "Funciones",
            "Dominio y rango",
            "Funciones lineales",
            "Funciones cuadráticas",
            "Funciones exponenciales",
            "Logaritmos (introducción)",
            "Trigonometría básica",
            "Razones trigonométricas",
            "Identidades básicas",
            "Geometría analítica"
        ],
        "11° Año (Quinto Año)": [
            "Trigonometría avanzada",
            "Ley de senos",
            "Ley de cosenos",
            "Funciones exponenciales y logarítmicas",
            "Números complejos",
            "Sucesiones y series",
            "Matrices",
            "Determinantes",
            "Estadística y probabilidad"
        ],
        "12° Año (Sexto Año)": [
            "Límites",
            "Derivadas (introducción al cálculo diferencial)",
            "Aplicaciones de derivadas",
            "Integrales básicas (en algunos bachilleres)",
            "Optimización",
            "Funciones trascendentales",
            "Vectores",
            "Estadística inferencial",
            "Probabilidad avanzada"
        ]
    };

    function actualizarTemas() {
        const selectGrado = document.getElementById("anio_escolar");
        const selectTema = document.getElementById("tema");
        const gradoSeleccionado = selectGrado.value;

        selectTema.innerHTML = "";

        if (temasPorGrado[gradoSeleccionado]) {
            temasPorGrado[gradoSeleccionado].forEach(tema => {
                const option = document.createElement("option");
                option.value = tema;
                option.textContent = tema;
                selectTema.appendChild(option);
            });
        }
    }

    window.onload = function() {
        actualizarTemas();
        document.getElementById("anio_escolar").addEventListener("change", actualizarTemas);
    };
</script>
</head>
<body>
<header>
    <h1>🎯 Material de Reforzamiento</h1>
    <p>Primero hacemos un pequeño diagnóstico, luego reforzamos donde más lo necesitas</p>
</header>
<div class="contenedor">
    <form action="test.php" method="get">
        <label for="nombre">Tu nombre:</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Ej: María Pérez">

        <label for="materia">¿Qué materia quieres reforzar?</label>
        <select id="materia" name="materia_id" required>
            <?php foreach ($materias as $m): ?>
                <option value="<?= htmlspecialchars($m["id"]) ?>"><?= htmlspecialchars($m["nombre"]) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="anio_escolar">Selecciona tu año escolar:</label>
        <select id="anio_escolar" name="anio_escolar" required>
            <option value="7° Año (Primer Año)">7° Año (Primer Año)</option>
            <option value="8° Año (Segundo Año)">8° Año (Segundo Año)</option>
            <option value="9° Año (Tercer Año)">9° Año (Tercer Año)</option>
            <option value="10° Año (Cuarto Año - Inicio de Bachillerato)">10° Año (Cuarto Año - Inicio de Bachillerato)</option>
            <option value="11° Año (Quinto Año)">11° Año (Quinto Año)</option>
            <option value="12° Año (Sexto Año)">12° Año (Sexto Año)</option>
        </select>

        <label for="tema">Selecciona el tema a reforzar:</label>
        <select id="tema" name="tema" required>
            <!-- Se carga dinámicamente con JavaScript según el año escolar -->
        </select>

        <button type="submit">Comenzar diagnóstico</button>
    </form>
    <a class="volver" href="../index.html">&larr; Volver al inicio</a>
</div>
</body>
</html>
