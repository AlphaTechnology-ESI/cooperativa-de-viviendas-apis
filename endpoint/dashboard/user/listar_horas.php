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

$input = json_decode(file_get_contents("php://input"), true);
$idUsuario = $input['id_usuario'] ?? null;

if (!$idUsuario) {
    echo json_encode(["estado" => "error", "mensaje" => "id_usuario requerido"]);
    exit;
}

$stmt = $conn->prepare("SELECT id_jornada, fecha, horas_trabajadas, comprobante_nombre FROM jornada_trabajo WHERE id_usuario = ? ORDER BY fecha DESC, id_jornada DESC");
if (!$stmt) {
    echo json_encode(["estado" => "error", "mensaje" => "Error preparando consulta: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();

$horas = [];
while ($row = $res->fetch_assoc()) {
    $horas[] = [
        "id_jornada" => intval($row["id_jornada"]),
        "fecha" => $row["fecha"],
        "horas_trabajadas" => intval($row["horas_trabajadas"]),
        "comprobante_nombre" => $row["comprobante_nombre"]
    ];
}

echo json_encode(["estado" => "ok", "horas" => $horas]);

$stmt->close();
$conn->close();