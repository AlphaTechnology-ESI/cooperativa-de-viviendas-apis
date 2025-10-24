<?php
include("../../../config/db.php");

$id_jornada = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id_jornada) {
    http_response_code(400);
    exit("ID de jornada no especificado");
}

// Preparar y ejecutar
$stmt = $conn->prepare("SELECT comprobante, comprobante_nombre FROM jornada_trabajo WHERE id_jornada = ?");
$stmt->bind_param("i", $id_jornada);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($blob, $nombre);
$stmt->fetch();
$stmt->close();

if (!$blob) {
    http_response_code(404);
    exit("No hay comprobante para esta jornada");
}

// Detectar tipo MIME por extensión
$extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
switch ($extension) {
    case "png": $mime = "image/png"; break;
    case "jpg":
    case "jpeg": $mime = "image/jpeg"; break;
    case "pdf": $mime = "application/pdf"; break;
    default: $mime = "application/octet-stream";
}

header("Content-Type: $mime");
header('Content-Disposition: attachment; filename="' . basename($nombre) . '"');
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");

echo $blob;

$conn->close();
exit;
?>
