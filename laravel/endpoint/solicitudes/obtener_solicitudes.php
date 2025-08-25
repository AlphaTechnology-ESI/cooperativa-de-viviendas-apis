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
    u.DNI,
    u.Fecha_Nacimiento,
    u.Estado_Civil,
    u.Ocupacion,
    u.Ingresos,
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
            "id" => $fila["id_solicitud"],
            "fecha_solicitud" => $fila["fecha_solicitud"],
            "nom_usu" => $fila["nom_usu"],
            "DNI" => $fila["DNI"],
            "correo" => $fila["correo"],
            "telefono" => $fila["telefono"],
            "Fecha_Nacimiento" => $fila["Fecha_Nacimiento"],
            "Estado_Civil" => $fila["Estado_Civil"],
            "Ocupacion" => $fila["Ocupacion"],
            "Ingresos" => $fila["Ingresos"],
            "Vivienda_Seleccionada" => $fila["Vivienda_Seleccionada"],
            "Monto_Inicial" => floatval($fila["Monto_Inicial"]),
            "Forma_Pago" => $fila["Forma_Pago"],
            "Grupo_Familiar" => $fila["Grupo_Familiar"],
            "Comentarios" => $fila["Comentarios"],
            "estado_solicitud" => $fila["estado_solicitud"]
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