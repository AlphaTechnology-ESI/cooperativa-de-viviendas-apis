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
$contrasena_actual = $input["contrasena_actual"] ?? "";
$contrasena_nueva = $input["contrasena_nueva"] ?? "";

if (!$id_usuario || !$contrasena_actual || !$contrasena_nueva) {
    echo json_encode(["estado" => "error", "mensaje" => "Todos los campos son obligatorios"]);
    exit;
}

// Verificar la contraseña actual
$sql = "SELECT contrasena FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
    exit;
}

$row = $result->fetch_assoc();

// Comparar contraseña actual
if ($row["contrasena"] !== $contrasena_actual) {
    echo json_encode(["estado" => "error", "mensaje" => "La contraseña actual es incorrecta"]);
    exit;
}

// Actualizar la contraseña
$sql_update = "UPDATE usuario SET contrasena = ? WHERE id_usuario = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $contrasena_nueva, $id_usuario);

if ($stmt_update->execute()) {
    echo json_encode(["estado" => "ok", "mensaje" => "Contraseña actualizada correctamente"]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar la contraseña"]);
}

$stmt->close();
$stmt_update->close();
$conn->close();
?>