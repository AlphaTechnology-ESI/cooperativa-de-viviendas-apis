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
$estado = $input['estado'] ?? '';

$sql = "SELECT jt.id_jornada, jt.fecha, jt.horas_trabajadas, jt.estado, u.nom_usu AS nombre_usuario
        FROM jornada_trabajo jt
        JOIN usuario u ON jt.id_usuario = u.id_usuario";

if ($estado) {
    $sql .= " WHERE jt.estado = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $estado);
} else {
    $stmt = $conn->prepare($sql);
}

if (!$stmt) {
    echo json_encode(["estado" => "error", "mensaje" => "Error en la preparación: " . $conn->error]);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();

$horas = [];
while ($row = $res->fetch_assoc()) {
    $horas[] = $row;
}

echo json_encode(["estado" => "ok", "horas" => $horas]);
?>
