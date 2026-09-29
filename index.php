<?php
require_once __DIR__ . '/config/db.php';

$mensaje_error = '';
$certificados = [];

// Obtener todas las ediciones de eventos registradas en la BD para el selector
try {
    $stmt_ediciones = $pdo->query("
        SELECT edicion, nombre_evento 
        FROM plantillas_certificados 
        WHERE edicion IS NOT NULL AND edicion != '' 
        GROUP BY edicion, nombre_evento 
        ORDER BY MAX(id) DESC
    ");
    $lista_ediciones = $stmt_ediciones->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lista_ediciones = [];
    $mensaje_error = "Error DB: " . $e->getMessage();
}

// Obtener parámetros ya sea por POST o GET
$documento_raw = $_REQUEST['documento'] ?? '';
$edicion_seleccionada = $_REQUEST['edicion'] ?? '';

// Procesar la búsqueda si se envió un documento
if (!empty($documento_raw)) {
    // Sanitizar número de documento
    $documento = preg_replace('/[^a-zA-Z0-9]/', '', trim($documento_raw));

    if (empty($documento)) {
        $mensaje_error = "Ingresá un número de Cédula de Identidad válido.";
    } else {
        try {
            // Consulta ajustada a las columnas de la BD (cedula, nombre, edicion, rol)
            $sql = "
                SELECT 
                    p.nombre AS nombre_completo, 
                    c.id AS plantilla_id, 
                    c.nombre_evento, 
                    c.edicion, 
                    c.rol_asociado
                FROM participantes p
                INNER JOIN plantillas_certificados c 
                    ON p.edicion = c.edicion 
                   AND p.rol = c.rol_asociado
                WHERE p.cedula = ?
            ";
            $params = [$documento];

            if (!empty($edicion_seleccionada)) {
                $sql .= " AND c.edicion = ?";
                $params[] = $edicion_seleccionada;
            }

            $sql .= " ORDER BY c.id DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($certificados)) {
                $mensaje_error = "No encontramos certificados registrados para la C.I. ingresada en la edición seleccionada.";
            }
        } catch (PDOException $e) {
            $mensaje_error = "Ocurrió un error al realizar la consulta. Por favor, intente nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Certificados Digitales</title>

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>

<body class="d-flex flex-column min-vh-100 position-relative overflow-x-hidden">

    <!-- Efecto Nubes -->
    <div class="bg-blur-container">
        <div class="glow-orb orb-1"></div>
        <div class="glow-orb orb-2"></div>
        <div class="glow-orb orb-3"></div>
    </div>

    <main class="container py-5 my-auto position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">

                <!-- Encabezado -->
                <div class="text-center mb-4">
                    <div class="hero-badge mb-3">
                        <i class="bi bi-shield-check-fill"></i> Muestra Nacional de Epidemiología
                    </div>
                    <h1 class="hero-title mb-2">
                        Portal de <span class="title-accent">Certificados</span>
                    </h1>
                    <p class="hero-subtitle">Seleccioná la edición de la muestra e ingresá tu número de C.I.</p>
                </div>

                <!-- Card Principal -->
                <div class="main-card p-4 p-md-5 mb-4">

                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-custom p-3 mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-circle-fill fs-5"></i>
                            <span><?= htmlspecialchars($mensaje_error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php">
                        <div class="row g-3">
                            <!-- Selector de Edición / Muestra -->
                            <div class="col-md-5">
                                <label for="edicion" class="form-label fw-bold small text-uppercase mb-2 form-label-custom">
                                    <i class="bi bi-collection-fill me-1 text-accent"></i> Edición / Muestra
                                </label>
                                <select name="edicion" id="edicion" class="form-select form-control-modern">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($lista_ediciones as $item): ?>
                                        <?php
                                        $valor_edicion = htmlspecialchars($item['edicion']);
                                        $nombre_evento = htmlspecialchars($item['nombre_evento']);
                                        $selected = ($edicion_seleccionada === $item['edicion']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $valor_edicion ?>" <?= $selected ?>>
                                            <?= $nombre_evento ?> (<?= $valor_edicion ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Campo de C.I. y Botones -->
                            <div class="col-md-7">
                                <label for="documento" class="form-label fw-bold small text-uppercase mb-2 form-label-custom">
                                    <i class="bi bi-card-heading me-1 text-accent"></i> Número de C.I.
                                </label>
                                <div class="d-flex gap-2">
                                    <input type="text" id="documento" name="documento" class="form-control form-control-modern flex-grow-1" placeholder="Ej: 1234567" required value="<?= htmlspecialchars($documento_raw) ?>">

                                    <button type="submit" class="btn btn-modern d-flex align-items-center justify-content-center gap-2 btn-search">
                                        <i class="bi bi-search"></i>
                                    </button>

                                    <?php if (!empty($documento_raw)): ?>
                                        <a href="index.php" class="btn btn-clear d-flex align-items-center justify-content-center" title="Nueva consulta / Limpiar">
                                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Listado de Resultados -->
                    <?php if (!empty($certificados)): ?>
                        <hr class="my-4" style="opacity:0.1;">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">Titular encontrado</span>
                                <h4 class="fw-bold m-0"><?= htmlspecialchars($certificados[0]['nombre_completo']) ?></h4>
                            </div>
                            <span class="badge bg-dark text-white rounded-pill px-3 py-2"><?= count($certificados) ?> <?= count($certificados) === 1 ? 'disponible' : 'disponibles' ?></span>
                        </div>

                        <div class="d-flex flex-column gap-3 mt-4">
                            <?php foreach ($certificados as $cert): ?>
                                <div class="cert-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 p-3 rounded-3">
                                    <div>
                                        <div class="d-flex gap-2 mb-2">
                                            <span class="badge badge-edicion"><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($cert['edicion']) ?></span>
                                            <span class="badge badge-role"><?= htmlspecialchars($cert['rol_asociado']) ?></span>
                                        </div>
                                        <h6 class="fw-bold mb-0 fs-6"><?= htmlspecialchars($cert['nombre_evento']) ?></h6>
                                    </div>
                                    <div>
                                        <a href="generar_pdf.php?id=<?= urlencode($cert['plantilla_id']) ?>&nombre=<?= urlencode($cert['nombre_completo']) ?>" target="_blank" class="btn-download-pdf">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                            <span>Obtener PDF</span>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-4 text-center pt-2">
                            <a href="index.php" class="text-decoration-none text-muted small fw-semibold d-inline-flex align-items-center gap-1 link-hover">
                                <i class="bi bi-x-circle"></i> Limpiar datos y realizar otra búsqueda
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-institutional text-center w-100 mt-auto py-3" style="position: relative; z-index: 1;">
        <div class="container">
            <p class="footer-text mb-1 small">
                <i class="bi me-1"></i> Unidad de Tecnología de la Información — DGVS
            </p>
            <p class="footer-subtext mb-0 extra-small">
                © <?= date('Y') ?> Sistema de Emisión y Acreditación de Certificados Digitales
            </p>
        </div>
    </footer>
</body>

</html>