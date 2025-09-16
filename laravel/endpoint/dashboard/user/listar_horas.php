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

$input = json_decode(file_get_contents("php://input"), true);
$idUsuario = $input["id_usuario"] ?? null;

if (!$idUsuario) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de usuario obligatorio"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM jornada_trabajo WHERE id_usuario = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

$horas = [];
while ($row = $result->fetch_assoc()) {
    $horas[] = $row;
}

echo json_encode(["estado" => "ok", "horas" => $horas]);
