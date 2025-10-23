<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { http_response_code(204); exit; }
if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); echo json_encode(["estado"=>"error","mensaje"=>"Método no permitido"]); exit; }

include("../../../config/db.php");

$input = json_decode(file_get_contents("php://input"), true);
if (!$input || empty($input["id_usuario"])) {
    echo json_encode(["estado"=>"error","mensaje"=>"id_usuario es obligatorio"]);
    exit;
}

$id_usuario = intval($input["id_usuario"]);

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT * FROM usuario_pendiente WHERE id_usuario=?");
    $stmt->bind_param("i",$id_usuario);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows===0) throw new Exception("Usuario pendiente no encontrado");

    $stmtUpdateSol = $conn->prepare("UPDATE solicitud_unidad_habitacional SET Estado_Solicitud='rechazada', Fecha_Evaluacion=NOW() WHERE id_usuario=?");
    $stmtUpdateSol->bind_param("i",$id_usuario);
    $stmtUpdateSol->execute();

    $stmtUpdateUser = $conn->prepare("UPDATE usuario_pendiente SET estado='rechazado' WHERE id_usuario=?");
    $stmtUpdateUser->bind_param("i",$id_usuario);
    $stmtUpdateUser->execute();

    $conn->commit();
    echo json_encode(["estado"=>"ok"]);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode(["estado"=>"error","mensaje"=>$e->getMessage()]);
}
?>
