<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include("../../../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

// Leer JSON
$input = json_decode(file_get_contents("php://input"), true);
$id_usuario = intval($input["id_usuario"] ?? 0);

if (!$id_usuario) {
    echo json_encode(["estado" => "error", "mensaje" => "id_usuario es obligatorio"]);
    exit;
}

$stmt = $conn->prepare("SELECT nom_usu, correo, telefono, cedula, ingresos FROM usuario WHERE id_usuario=?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
    exit;
}

$usuario = $result->fetch_assoc();
echo json_encode(["estado" => "ok", "usuario" => $usuario]);
