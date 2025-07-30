<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

session_start();
include("../../config/db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

// Leer datos del cuerpo del POST
$input = json_decode(file_get_contents("php://input"), true);

// Validar si los datos llegaron correctamente
if (!$input || empty($input["correo"]) || empty($input["contrasena"])) {
    echo json_encode(["estado" => "error", "mensaje" => "Correo y contraseña son obligatorios"]);
    exit;
}

$correo = $input["correo"];
$contrasena = $input["contrasena"];

// Función para verificar usuario en la base
function verificarUsuario($conn, $tabla, $correo, $contrasena) {
    $stmt = $conn->prepare("SELECT * FROM $tabla WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();
        if ($fila["contrasena"] === $contrasena) {
            return $tabla;
        }
    }
    return false;
}

// Intentar login como usuario normal o admin
$rol = verificarUsuario($conn, "usuario", $correo, $contrasena);
if (!$rol) {
    $rol = verificarUsuario($conn, "admins", $correo, $contrasena);
}

if ($rol) {
    $_SESSION["correo"] = $correo;
    $_SESSION["rol"] = $rol;

    echo json_encode(["estado" => "ok", "rol" => $rol]);
} else {
    echo json_encode(["estado" => "error", "mensaje" => "Credenciales inválidas"]);
}