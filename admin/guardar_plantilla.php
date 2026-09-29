<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_evento = $_POST['nombre_evento'];
    $edicion       = $_POST['edicion'] ?? '';
    $rol_asociado  = $_POST['rol_asociado'];
    $posicion_y    = $_POST['posicion_y'];
    $tamano_fuente = $_POST['tamano_fuente'];
    $color_hex    = $_POST['color_hex'];

    // Mover PNG a /uploads/plantillas/
    $png_nombre = time() . '_' . $_FILES['imagen_png']['name'];
    $png_destino = '../uploads/plantillas/' . $png_nombre;
    move_uploaded_file($_FILES['imagen_png']['tmp_name'], $png_destino);

    // Mover TTF a /uploads/fuentes/
    $ttf_nombre = time() . '_' . $_FILES['fuente_ttf']['name'];
    $ttf_destino = '../uploads/fuentes/' . $ttf_nombre;
    move_uploaded_file($_FILES['fuente_ttf']['tmp_name'], $ttf_destino);

    // Insertar en BD
    $sql = "INSERT INTO plantillas_certificados 
            (nombre_evento, edicion, rol_asociado, imagen_png, fuente_ttf, posicion_y, tamano_fuente, color_hex) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre_evento, $edicion, $rol_asociado, $png_nombre, $ttf_nombre, $posicion_y, $tamano_fuente, $color_hex]);

    header('Location: index.php?status=success');
    exit;
}