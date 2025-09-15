<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

include("../../config/db.php");

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["estado" => "error", "mensaje" => "Datos inválidos"]);
    exit;
}

try {
    // Insertar en usuario_pendiente
$stmt1 = $conn->prepare("INSERT INTO usuario_pendiente
    (nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt1->bind_param("ssssssss",
    $input["nombre"],
    $input["email"],
    $input["telefono"],
    $input["dni"],
    $input["fecha_nacimiento"],
    $input["estado_civil"],
    $input["ocupacion"],
    $input["ingresos"]
);

    if (!$stmt1->execute()) {
        throw new Exception("Error al insertar usuario.");
    }

    $id_usuario = $conn->insert_id;

    // Insertar en solicitud_unidad_habitacional
    $stmt2 = $conn->prepare("INSERT INTO solicitud_unidad_habitacional 
        (id_usuario, Vivienda_Seleccionada, Monto_Inicial, Forma_Pago, Grupo_Familiar, Comentarios) 
        VALUES (?, ?, ?, ?, ?, ?)");

    $monto = floatval(str_replace(['$', '.', ','], '', $input["monto_inicial"] ?? "0"));

    $stmt2->bind_param("isdsss",
        $id_usuario,
        $input["vivienda_seleccionada"],
        $monto,
        $input["forma_pago"],
        $input["grupo_familiar"],
        $input["comentarios"]
    );

    if (!$stmt2->execute()) {
        throw new Exception("Error al insertar solicitud.");
    }

    echo json_encode(["estado" => "ok"]);

} catch (Exception $e) {
    echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
}
?>