-- Esquema de la base de datos para el módulo de Reforzamiento
-- Biblioteca Virtual Comunitaria - Guna Yala

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS materias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS temas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    materia_id INTEGER NOT NULL,
    nombre TEXT NOT NULL,
    FOREIGN KEY (materia_id) REFERENCES materias(id)
);

CREATE TABLE IF NOT EXISTS preguntas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tema_id INTEGER NOT NULL,
    enunciado TEXT NOT NULL,
    opcion_a TEXT NOT NULL,
    opcion_b TEXT NOT NULL,
    opcion_c TEXT NOT NULL,
    opcion_d TEXT NOT NULL,
    respuesta_correcta CHAR(1) NOT NULL CHECK (respuesta_correcta IN ('a','b','c','d')),
    FOREIGN KEY (tema_id) REFERENCES temas(id)
);

CREATE TABLE IF NOT EXISTS recursos_refuerzo (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tema_id INTEGER NOT NULL,
    titulo TEXT NOT NULL,
    tipo TEXT NOT NULL,
    contenido TEXT NOT NULL,
    orden INTEGER DEFAULT 0,
    FOREIGN KEY (tema_id) REFERENCES temas(id)
);

CREATE TABLE IF NOT EXISTS intentos_test (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre_estudiante TEXT NOT NULL,
    materia_id INTEGER NOT NULL,
    fecha TEXT NOT NULL DEFAULT (datetime('now', 'localtime')),
    puntaje_total REAL NOT NULL,
    detalle_json TEXT NOT NULL,
    FOREIGN KEY (materia_id) REFERENCES materias(id)
);

-- ==========================================
-- DATOS DE EJEMPLO: Matemáticas
-- ==========================================
INSERT OR IGNORE INTO materias (id, nombre) VALUES (1, 'Matemáticas');

INSERT OR IGNORE INTO temas (id, materia_id, nombre) VALUES
    (1, 1, 'Fracciones'),
    (2, 1, 'Ecuaciones de primer grado'),
    (3, 1, 'Geometría básica');

INSERT OR IGNORE INTO preguntas (tema_id, enunciado, opcion_a, opcion_b, opcion_c, opcion_d, respuesta_correcta) VALUES
    (1, '¿Cuánto es 1/2 + 1/4?', '2/6', '3/4', '1/4', '2/4', 'b'),
    (1, '¿Cuál fracción es equivalente a 2/4?', '1/2', '1/3', '3/4', '1/4', 'a'),
    (1, '¿Cuánto es 3/5 en decimal?', '0.35', '0.6', '0.5', '0.65', 'b'),
    (2, 'Si x + 5 = 12, ¿cuánto vale x?', '5', '6', '7', '17', 'c'),
    (2, 'Resuelve: 2x = 10', 'x = 2', 'x = 5', 'x = 10', 'x = 20', 'b'),
    (2, 'Si 3x - 4 = 11, ¿cuánto vale x?', '5', '4', '3', '7', 'a'),
    (3, '¿Cuántos lados tiene un triángulo?', '2', '3', '4', '5', 'b'),
    (3, '¿Cuál es la fórmula del área de un rectángulo?', 'base x altura', 'lado x lado', 'base + altura', 'pi x radio', 'a'),
    (3, '¿Cuántos grados suman los ángulos internos de un triángulo?', '90', '180', '270', '360', 'b');

INSERT OR IGNORE INTO recursos_refuerzo (tema_id, titulo, tipo, contenido, orden) VALUES
    (1, 'Repaso: ¿Qué es una fracción?', 'explicacion', 'Una fracción representa una parte de un todo. El número de arriba (numerador) indica cuántas partes tomamos, y el de abajo (denominador) en cuántas partes se dividió el todo.', 1),
    (1, 'Ejercicio: Suma de fracciones', 'ejercicio', 'Calcula: 1/3 + 1/6. Recuerda: primero busca un denominador común.', 2),
    (2, 'Repaso: Resolver ecuaciones simples', 'explicacion', 'Para despejar x, hacemos la misma operación en ambos lados de la ecuación hasta dejar la x sola.', 1),
    (2, 'Ejercicio: Despeja x', 'ejercicio', 'Resuelve: 4x + 3 = 19', 2),
    (3, 'Repaso: Figuras geométricas básicas', 'explicacion', 'Las figuras geométricas más comunes son el triángulo, el cuadrado, el rectángulo y el círculo, cada una con fórmulas propias de área y perímetro.', 1),
    (3, 'Ejercicio: Calcula el área', 'ejercicio', 'Un rectángulo mide 8 cm de base y 5 cm de altura. ¿Cuál es su área?', 2);
