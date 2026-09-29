<?php
require_once __DIR__ . '/../config/db.php';

// Obtener las plantillas ya registradas
$stmt = $pdo->query("SELECT * FROM plantillas_certificados ORDER BY id DESC");
$plantillas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administrador de Plantillas - Certificados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 20px;
            color: #333;
        }

        .container {
            display: flex;
            gap: 20px;
        }

        .form-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 350px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .preview-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            flex-grow: 1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .field {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background: #0066cc;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }

        button:hover {
            background: #0052a3;
        }

        #canvas-wrapper {
            position: relative;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        #preview-img {
            max-width: 100%;
            display: block;
        }

        #nombre-preview {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            pointer-events: none;
            font-weight: bold;
            color: #000;
        }
    </style>
</head>

<body>

    <h2>Gestión de Plantillas de Certificados</h2>

    <div class="container">
        <!-- Formulario -->
        <div class="form-card">
            <form action="guardar_plantilla.php" method="POST" enctype="multipart/form-data">
                <div class="field">
                    <label>Nombre del Evento / Certificado:</label>
                    <input type="text" name="nombre_evento" placeholder="Ej. Muestra Nacional Epidemiológica" required>
                </div>

                <div class="field">
                    <label>Edición / Año:</label>
                    <input type="text" name="edicion" placeholder="Ej. 2026" required>
                </div>

                <div class="field">
                    <label>Rol Asociado:</label>
                    <select name="rol_asociado" required>
                        <option value="PARTICIPANTE">Participante / Asistente</option>
                        <option value="EXPOSITOR">Expositor / Disertante</option>
                        <option value="ORGANIZADOR">Comité Organizador</option>
                    </select>
                </div>

                <div class="field">
                    <label>Imagen PNG (Certificado sin nombre):</label>
                    <input type="file" name="imagen_png" id="input-png" accept="image/png" required>
                </div>

                <div class="field">
                    <label>Fuente TTF:</label>
                    <input type="file" name="fuente_ttf" accept=".ttf" required>
                </div>

                <div class="field">
                    <label>Posición Vertical Y (%):</label>
                    <input type="range" id="input-y" name="posicion_y" min="10" max="90" value="45" oninput="actualizarPreview()">
                </div>

                <div class="field">
                    <label>Tamaño de Fuente (pt):</label>
                    <input type="number" id="input-size" name="tamano_fuente" value="32" onchange="actualizarPreview()">
                </div>

                <div class="field">
                    <label>Color del Texto:</label>
                    <input type="color" id="input-color" name="color_hex" value="#000000" onchange="actualizarPreview()">
                </div>

                <button type="submit">Guardar Plantilla</button>
            </form>
        </div>

        <!-- Previsualización dinámica -->
        <div class="preview-card">
            <h3>Vista Previa Dinámica</h3>
            <p><small>Ajustá la barra de Posición Y hasta que el nombre quede en la línea correspondiente.</small></p>

            <div id="canvas-wrapper">
                <img id="preview-img" src="" alt="Cargá un PNG para ver la vista previa" style="display:none;">
                <div id="nombre-preview">JUAN PÉREZ GONZÁLEZ</div>
            </div>
        </div>
    </div>

    <script>
        // Cargar imagen local en la vista previa antes de subirla
        document.getElementById('input-png').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.getElementById('preview-img');
                    img.src = event.target.result;
                    img.style.display = 'block';
                    actualizarPreview();
                }
                reader.readAsDataURL(file);
            }
        });

        function actualizarPreview() {
            const yVal = document.getElementById('input-y').value;
            const sizeVal = document.getElementById('input-size').value;
            const colorVal = document.getElementById('input-color').value;

            const previewText = document.getElementById('nombre-preview');
            previewText.style.top = yVal + '%';
            previewText.style.fontSize = (sizeVal / 2) + 'px';
            previewText.style.color = colorVal;
        }
    </script>

</body>

</html>