<?php
include("../../../config/db.php");

$id_jornada = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$id_jornada) {
    http_response_code(400);
    echo "ID de jornada no especificado";
    exit;
}

$stmt = $conn->prepare("SELECT comprobante, comprobante_nombre FROM jornada_trabajo WHERE id_jornada = ?");
if (!$stmt) {
    http_response_code(500);
    echo "Error en DB";
    exit;
}

$stmt->bind_param("i", $id_jornada);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo "Comprobante no encontrado";
    exit;
}
$stmt->bind_result($blob, $nombre);
$stmt->fetch();
$stmt->close();

if ($blob === null) {
    http_response_code(404);
    echo "No hay comprobante para esa jornada";
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($blob);
if (!$mime) $mime = 'application/octet-stream';

$filename = $nombre ? $nombre : ("comprobante_" . $id_jornada);

header("Content-Description: File Transfer");
header("Content-Type: " . $mime);
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Content-Length: " . strlen($blob));

echo $blob;
$conn->close();
exit;