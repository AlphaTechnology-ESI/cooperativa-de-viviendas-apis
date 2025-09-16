<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

include("../../../config/db.php");

// Leer datos del POST
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || empty($input["id_usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "id_usuario es obligatorio"]);
    exit;
}

$id_usuario = intval($input["id_usuario"]);

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos 
                            FROM usuario_pendiente 
                            WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("Usuario pendiente no encontrado");
    }
    $user = $result->fetch_assoc();

    $stmtInsert = $conn->prepare("INSERT INTO usuario (nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos, contrasena) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $contrasena = "1234";
    $stmtInsert->bind_param(
        "sssssssss",
        $user['nom_usu'],
        $user['correo'],
        $user['telefono'],
        $user['cedula'],
        $user['fecha_nacimiento'],
        $user['estado_civil'],
        $user['ocupacion'],
        $user['ingresos'],
        $contrasena
    );
    $stmtInsert->execute();

    $stmtUpdate = $conn->prepare("UPDATE solicitud_unidad_habitacional SET Estado_Solicitud='aprobada', Fecha_Evaluacion=NOW() WHERE id_usuario=?");
    $stmtUpdate->bind_param("i", $id_usuario);
    $stmtUpdate->execute();

    $stmtPendiente = $conn->prepare("UPDATE usuario_pendiente SET estado='aprobado' WHERE id_usuario=?");
    $stmtPendiente->bind_param("i", $id_usuario);
    $stmtPendiente->execute();

    $conn->commit();

    echo json_encode(["estado" => "ok"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
}
