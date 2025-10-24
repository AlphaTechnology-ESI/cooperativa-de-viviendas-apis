<?php
header("Content-Type: application/json; charset=UTF-8");
include("../../../config/db.php");

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["estado" => "error", "mensaje" => "ID requerido"]);
    exit;
}

$sql = "SELECT 
            jt.id_jornada,
            jt.fecha,
            jt.horas_trabajadas,
            jt.motivo_inasistencia,
            jt.tipo_compensacion,
            jt.estado,
            jt.comprobante_nombre,
            u.nom_usu AS nombre_usuario,
            u.cedula AS cedula,
            u.correo
        FROM jornada_trabajo jt
        JOIN usuario u ON jt.id_usuario = u.id_usuario
        WHERE jt.id_jornada = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["estado" => "error", "mensaje" => "Registro no encontrado"]);
    exit;
}

$row = $res->fetch_assoc();

echo json_encode([
    "estado" => "ok",
    "hora" => [
        "id_jornada" => $row["id_jornada"],
        "fecha" => $row["fecha"],
        "horas_trabajadas" => $row["horas_trabajadas"],
        "motivo_inasistencia" => $row["motivo_inasistencia"],
        "tipo_compensacion" => $row["tipo_compensacion"],
        "estado" => $row["estado"],
        "comprobante_nombre" => $row["comprobante_nombre"],
        "nombre_usuario" => $row["nombre_usuario"],
        "cedula" => $row["cedula"],
        "correo" => $row["correo"]
    ]
]);
?>
