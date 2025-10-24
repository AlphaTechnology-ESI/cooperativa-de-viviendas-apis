<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include("../../../config/db.php");

$input = json_decode(file_get_contents("php://input"), true);
$id = $input["id_jornada"] ?? null;
$estado = $input["estado"] ?? null;

if (!$id || !$estado) {
    echo json_encode(["estado" => "error", "mensaje" => "Datos incompletos"]);
    exit;
}

$sql = "UPDATE jornada_trabajo SET estado = ? WHERE id_jornada = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la preparación: " . $conn->error]);
    exit;
}

$stmt->bind_param("si", $estado, $id);

if ($stmt->execute()) {
    echo json_encode(["estado" => "ok", "mensaje" => "Estado actualizado"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar: " . $stmt->error]);
}
?>
