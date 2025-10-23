<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include("../../config/db.php");

$idPendiente = intval($_GET['id'] ?? 0);

if ($idPendiente <= 0) {
    echo json_encode(["estado"=>"error","mensaje"=>"ID inválido"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM usuario_pendiente WHERE id_usuario=? AND estado='pendiente'");
$stmt->bind_param("i", $idPendiente);
$stmt->execute();
$result = $stmt->get_result();
$pendiente = $result->fetch_assoc();

if (!$pendiente) {
    echo json_encode(["estado"=>"error","mensaje"=>"Usuario pendiente no encontrado"]);
    exit;
}

$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$contrasena = substr(str_shuffle($chars), 0, 8);

$stmtIns = $conn->prepare("INSERT INTO usuario
    (id_persona, nom_usu, correo, telefono, cedula, fecha_nacimiento, estado_civil, ocupacion, ingresos, contrasena)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmtIns->bind_param("isssssssss",
    $pendiente['id_usuario'],
    $pendiente['nom_usu'],
    $pendiente['correo'],
    $pendiente['telefono'],
    $pendiente['cedula'],
    $pendiente['fecha_nacimiento'],
    $pendiente['estado_civil'],
    $pendiente['ocupacion'],
    $pendiente['ingresos'],
    $contrasena
);

if (!$stmtIns->execute()) {
    echo json_encode(["estado"=>"error","mensaje"=>"No se pudo insertar en usuario"]);
    exit;
}

$stmtDel = $conn->prepare("UPDATE usuario_pendiente SET estado='aprobado' WHERE id_usuario=?");
$stmtDel->bind_param("i", $idPendiente);
$stmtDel->execute();

echo json_encode(["estado"=>"ok","mensaje"=>"Usuario aprobado","contrasena"=>$contrasena]);
