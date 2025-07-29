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

include("../config/db.php");

try {
    $sql = "SELECT 
                u.id AS id_usuario,
                s.id AS id_solicitud,
                s.fecha_solicitud,
                u.nom_usu AS nombre,
                u.DNI,
                u.correo AS email,
                u.telefono,
                u.Fecha_Nacimiento AS fecha_nacimiento,
                u.Estado_Civil AS estado_civil,
                u.Ocupacion AS ocupacion,
                u.Ingresos AS ingresos,
                s.Vivienda_Seleccionada,
                s.Monto_Inicial,
                s.Forma_Pago,
                s.Grupo_Familiar,
                s.Comentarios,
                s.Estado_Solicitud
            FROM usuario_pendiente u
            INNER JOIN solicitud_unidad_habitacional s ON u.id = s.id_usuario
            ORDER BY s.fecha_solicitud DESC";

    $resultado = $conn->query($sql);

    $solicitudes = [];

    while ($fila = $resultado->fetch_assoc()) {
        $solicitudes[] = [
            "id" => $fila["id_solicitud"],
            "fecha_solicitud" => $fila["fecha_solicitud"],
            "nombre" => $fila["nombre"],
            "dni" => $fila["DNI"],
            "email" => $fila["email"],
            "telefono" => $fila["telefono"],
            "fecha_nacimiento" => $fila["fecha_nacimiento"],
            "estado_civil" => $fila["estado_civil"],
            "ocupacion" => $fila["ocupacion"],
            "ingresos" => $fila["ingresos"],
            "vivienda_seleccionada" => $fila["Vivienda_Seleccionada"],
            "monto_inicial" => floatval($fila["Monto_Inicial"]),
            "forma_pago" => $fila["Forma_Pago"],
            "grupo_familiar" => $fila["Grupo_Familiar"],
            "comentarios" => $fila["Comentarios"],
            "estado_solicitud" => $fila["Estado_Solicitud"]
        ];
    }

    echo json_encode([
        "success" => true,
        "data" => $solicitudes
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener las solicitudes: " . $e->getMessage()
    ]);
}
?>