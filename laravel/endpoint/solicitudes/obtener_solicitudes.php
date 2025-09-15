<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

include("../../config/db.php");

try {
    $sql = "SELECT 
    u.id_usuario,
    u.nom_usu,
    u.correo,
    u.telefono,
    u.cedula,
    u.fecha_nacimiento,
    u.estado_civil,
    u.ocupacion,
    u.ingresos,
    s.Vivienda_Seleccionada,
    s.Monto_Inicial,
    s.Forma_Pago,
    s.Grupo_Familiar,
    s.Comentarios,
    s.estado_solicitud,
    s.id_solicitud ,
    s.fecha_solicitud
FROM usuario_pendiente u
JOIN solicitud_unidad_habitacional s ON u.id_usuario = s.id_usuario
            ORDER BY s.fecha_solicitud DESC";

    $resultado = $conn->query($sql);

    $solicitudes = [];

    while ($fila = $resultado->fetch_assoc()) {
        $solicitudes[] = [
            "id" => $fila["id_solicitud"],            // no ID_Solicitud
            "id_usuario" => $fila["id_usuario"],
            "fecha_solicitud" => $fila["fecha_solicitud"],  // no Fecha_Solicitud
            "nom_usu" => $fila["nom_usu"],
            "cedula" => $fila["cedula"],
            "correo" => $fila["correo"],
            "telefono" => $fila["telefono"],
            "fecha_nacimiento" => $fila["fecha_nacimiento"], // unifica el nombre
            "estado_civil" => $fila["estado_civil"],
            "ocupacion" => $fila["ocupacion"],
            "ingresos" => $fila["ingresos"],
            "Vivienda_Seleccionada" => $fila["Vivienda_Seleccionada"],
            "Monto_Inicial" => floatval($fila["Monto_Inicial"]),
            "Forma_Pago" => $fila["Forma_Pago"],
            "Grupo_Familiar" => $fila["Grupo_Familiar"],
            "Comentarios" => $fila["Comentarios"],
            "estado_solicitud" => $fila["estado_solicitud"]  // no Estado_Solicitud
        ];


    }

    echo json_encode([
        "estado" => "ok",
        "solicitudes" => $solicitudes
    ]);
} catch (Exception $e) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Error al obtener las solicitudes: " . $e->getMessage()
    ]);
}
?>