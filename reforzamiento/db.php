<?php
// db.php - Conexión a la base de datos SQLite del módulo de reforzamiento

function conectarDB() {
    $rutaDB = __DIR__ . '/data/reforzamiento.db';

    // Si la base de datos no existe todavía, se crea junto con las tablas
    $existe = file_exists($rutaDB);

    if (!is_dir(__DIR__ . '/data')) {
        mkdir(__DIR__ . '/data', 0755, true);
    }

    try {
        $pdo = new PDO('sqlite:' . $rutaDB);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if (!$existe) {
            $sql = file_get_contents(__DIR__ . '/schema.sql');
            $pdo->exec($sql);
        }

        return $pdo;
    } catch (PDOException $e) {
        die('Error al conectar con la base de datos: ' . $e->getMessage());
    }
}
