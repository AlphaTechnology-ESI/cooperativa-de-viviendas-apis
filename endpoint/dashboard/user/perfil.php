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

// Consulta para obtener datos del usuario
$sql = "SELECT nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos FROM usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Consulta adicional para obtener la fecha de ingreso desde la tabla correspondiente
    // Primero intentar desde usuario_pendiente que tiene la relación con solicitud_unidad_habitacional
    $sql_fecha = "SELECT s.Fecha_Evaluacion 
                  FROM solicitud_unidad_habitacional s 
                  INNER JOIN usuario_pendiente up ON s.id_usuario = up.id_usuario
                  INNER JOIN usuario u ON up.cedula = u.cedula
                  WHERE u.id_usuario = ? AND s.Estado_Solicitud = 'aprobada'
                  ORDER BY s.Fecha_Evaluacion DESC LIMIT 1";
    
    $stmt_fecha = $conn->prepare($sql_fecha);
    $stmt_fecha->bind_param("i", $id_usuario);
    $stmt_fecha->execute();
    $result_fecha = $stmt_fecha->get_result();
    
    $fecha_ingreso = null;
    if ($result_fecha->num_rows > 0) {
        $row_fecha = $result_fecha->fetch_assoc();
        $fecha_ingreso = $row_fecha['Fecha_Evaluacion'];
    }
    
    echo json_encode([
        "estado" => "ok",
        "usuario" => [
            "nom_usu" => $row["nom_usu"],
            "correo" => $row["correo"],
            "telefono" => $row["telefono"],
            "cedula" => $row["cedula"],
            "fecha_nacimiento" => $row["fecha_nacimiento"],
            "estado_civil" => $row["estado_civil"],
            "ocupacion" => $row["ocupacion"],
            "ingresos" => $row["ingresos"],
            "fecha_ingreso" => $fecha_ingreso
        ]
    ]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Usuario no encontrado"]);
}
