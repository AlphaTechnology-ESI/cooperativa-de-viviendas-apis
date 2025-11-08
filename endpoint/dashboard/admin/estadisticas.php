<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

include("../../../config/db.php");

try {
    // Total de viviendas (puedes ajustar esto según tu tabla de viviendas)
    $total_viviendas = 6; // Valor fijo o consultar de tabla
    
    // Total de socios (usuarios en la tabla usuario)
    $stmt_socios = $conn->prepare("SELECT COUNT(*) as total FROM usuario");
    $stmt_socios->execute();
    $result_socios = $stmt_socios->get_result();
    $total_socios = $result_socios->fetch_assoc()['total'];
    $stmt_socios->close();
    
    // Viviendas en construcción (puedes ajustar según tu lógica)
    $viviendas_construccion = 4; // Valor fijo o consultar de tabla
    
    // Solicitudes pendientes
    $stmt_pendientes = $conn->prepare("SELECT COUNT(*) as total FROM solicitud_unidad_habitacional WHERE estado_solicitud = 'pendiente'");
    $stmt_pendientes->execute();
    $result_pendientes = $stmt_pendientes->get_result();
    $solicitudes_pendientes = $result_pendientes->fetch_assoc()['total'];
    $stmt_pendientes->close();
    
    echo json_encode([
        "estado" => "ok",
        "total_viviendas" => $total_viviendas,
        "total_socios" => $total_socios,
        "viviendas_construccion" => $viviendas_construccion,
        "solicitudes_pendientes" => $solicitudes_pendientes
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "estado" => "error",
        "mensaje" => $e->getMessage()
    ]);
}

$conn->close();
?>