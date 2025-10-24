<?php
header("Content-Type: application/json; charset=UTF-8");
include("../../../config/db.php");

$id_usuario = $_POST['id_usuario'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$horas_trabajadas = $_POST['horas_trabajadas'] ?? null;

if (!$id_usuario || !$fecha || !$horas_trabajadas) {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan datos obligatorios"]);
    exit;
}

$comprobante_blob = null;
$comprobante_nombre = null;

if (isset($_FILES['comprobantePago']) && $_FILES['comprobantePago']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['comprobantePago']['tmp_name'];
    $comprobante_blob = file_get_contents($tmp_name);
    $comprobante_nombre = basename($_FILES['comprobantePago']['name']);
}

$stmt = $conn->prepare("
    INSERT INTO jornada_trabajo 
        (id_usuario, fecha, horas_trabajadas, comprobante, comprobante_nombre, estado) 
    VALUES (?, ?, ?, ?, ?, 'pendiente')
");

$stmt->bind_param(
    "isiss",
    $id_usuario,
    $fecha,
    $horas_trabajadas,
    $comprobante_blob,
    $comprobante_nombre
);

if ($stmt->execute()) {
    echo json_encode([
        "estado" => "ok",
        "mensaje" => "Registro insertado correctamente",
        "id_jornada" => $stmt->insert_id,
        "comprobante_nombre" => $comprobante_nombre
    ]);
} else {
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Error al guardar en base de datos: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>