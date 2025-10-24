<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

include("../../../config/db.php"); 

$idUsuario = $_POST['id_usuario'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$horas = $_POST['horas_trabajadas'] ?? null;

if (!$idUsuario || !$fecha || !$horas) {
    echo json_encode(["estado" => "error", "mensaje" => "Fecha, horas y usuario son obligatorios"]);
    exit;
}

$tipo_compensacion = "";
$motivo_inasistencia = "";
$comprobante_blob = null;
$comprobante_nombre = null;

if (isset($_FILES['comprobantePago']) && $_FILES['comprobantePago']['error'] === UPLOAD_ERR_OK) {
    $comprobante_nombre = $_FILES['comprobantePago']['name'];
    $comprobante_blob = file_get_contents($_FILES['comprobantePago']['tmp_name']);
}

$stmt = $conn->prepare("INSERT INTO jornada_trabajo (tipo_compensacion, motivo_inasistencia, horas_trabajadas, fecha, id_usuario, comprobante, comprobante_nombre) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al preparar statement: " . $conn->error]);
    exit;
}

$comprobante_param = $comprobante_blob;
$stmt->bind_param("ssisisb", $tipo_compensacion, $motivo_inasistencia, $horas, $fecha, $idUsuario, $comprobante_param, $comprobante_nombre);

if ($comprobante_blob !== null) {
    $stmt->send_long_data(5, $comprobante_blob);
}

$exec = $stmt->execute();

if ($exec) {
    $insert_id = $stmt->insert_id;
    echo json_encode(["estado" => "ok", "id_jornada" => $insert_id, "comprobante_nombre" => $comprobante_nombre]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al guardar en la base de datos: " . $stmt->error]);
}

$stmt->close();
$conn->close();
