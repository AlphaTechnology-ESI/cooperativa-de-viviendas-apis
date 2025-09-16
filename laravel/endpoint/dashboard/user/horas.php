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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

// Leer JSON
$input = json_decode(file_get_contents("php://input"), true);

$idUsuario = $input["id_usuario"] ?? null;
$fecha = $input["fecha"] ?? null;
$horas = $input["horas_trabajadas"] ?? null;

if (!$idUsuario || !$fecha || !$horas) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Insertar en jornada_trabajo
$stmt = $conn->prepare("INSERT INTO jornada_trabajo (tipo_compensacion, motivo_inasistencia, horas_trabajadas, fecha, id_usuario) VALUES (?, ?, ?, ?, ?)");
$tipo_compensacion = ""; // Opcional, vacío
$motivo_inasistencia = ""; // Opcional, vacío
$stmt->bind_param("ssisi", $tipo_compensacion, $motivo_inasistencia, $horas, $fecha, $idUsuario);

if ($stmt->execute()) {
    echo json_encode(["estado" => "ok", "mensaje" => "Horas registradas correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al guardar en la base de datos"]);
}
