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

if (!isset($input['id_usuario'])) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de usuario no proporcionado"]);
    exit;
}

$id_usuario = intval($input['id_usuario']);

try {
    // Actualizar el estado de la solicitud a 'en_revision'
    $stmt = $conn->prepare("UPDATE solicitud_unidad_habitacional 
                           SET estado_solicitud = 'en_revision', 
                               fecha_evaluacion = NOW() 
                           WHERE id_usuario = ?");
    
    $stmt->bind_param("i", $id_usuario);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el estado de la solicitud");
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se encontró la solicitud o ya estaba en ese estado");
    }
    
    echo json_encode([
        "estado" => "ok",
        "mensaje" => "Solicitud puesta en revisión correctamente"
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => $e->getMessage()
    ]);
}

$conn->close();
?>