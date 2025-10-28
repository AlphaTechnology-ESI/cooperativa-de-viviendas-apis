<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include("../../../config/db.php");

// Obtener parámetros de filtro
$buscar = $_GET['buscar'] ?? '';
$estado_civil = $_GET['estado_civil'] ?? '';
$ingresos = $_GET['ingresos'] ?? '';

// Construir la consulta SQL
$sql = "SELECT id_usuario, nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos
        FROM usuario";

$conditions = [];
$params = [];
$types = "";

// Aplicar filtros
if (!empty($buscar)) {
    $conditions[] = "(nom_usu LIKE ? OR cedula LIKE ? OR correo LIKE ?)";
    $searchTerm = "%" . $buscar . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if (!empty($estado_civil)) {
    $conditions[] = "estado_civil = ?";
    $params[] = $estado_civil;
    $types .= "s";
}

if (!empty($ingresos)) {
    $conditions[] = "ingresos = ?";
    $params[] = $ingresos;
    $types .= "s";
}

// Agregar condiciones WHERE si existen
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY nom_usu ASC";

try {
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la preparación: " . $conn->error);
    }
    
    // Vincular parámetros si existen
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $socios = [];
    while ($row = $result->fetch_assoc()) {
        $socios[] = $row;
    }
    
    echo json_encode([
        "estado" => "ok", 
        "socios" => $socios,
        "total" => count($socios)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "estado" => "error", 
        "mensaje" => "Error al obtener socios: " . $e->getMessage()
    ]);
}

$conn->close();
?>
