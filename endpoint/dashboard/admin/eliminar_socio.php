<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "estado" => "error",
        "mensaje" => "Método no permitido. Solo POST."
    ]);
    exit;
}

// Incluir configuración de base de datos
include("../../../config/db.php");

try {
    // Obtener datos del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id_usuario']) || empty($input['id_usuario'])) {
        throw new Exception("ID de usuario requerido");
    }
    
    $id_usuario = (int)$input['id_usuario'];
    
    // Verificar que el usuario existe - usar la tabla correcta "usuario"
    $stmt = $conn->prepare("SELECT id_usuario, nom_usu, correo FROM usuario WHERE id_usuario = ?");
    if (!$stmt) {
        throw new Exception("Error en la preparación: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    if (!$usuario) {
        throw new Exception("Usuario no encontrado");
    }
    
    // Iniciar transacción
    $conn->autocommit(FALSE);
    
    try {
        // Eliminar jornadas de trabajo del usuario (si existen)
        $stmt = $conn->prepare("DELETE FROM jornada_trabajo WHERE id_usuario = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();
        }
        
        // Eliminar solicitudes de unidad habitacional del usuario (si existen)
        $stmt = $conn->prepare("DELETE FROM solicitud_unidad_habitacional WHERE id_usuario = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->close();
        }
        
        // Finalmente eliminar el usuario
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        if (!$stmt) {
            throw new Exception("Error preparando eliminación: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("No se pudo eliminar el usuario");
        }
        
        $stmt->close();
        
        // Confirmar transacción
        $conn->commit();
        $conn->autocommit(TRUE);
        
        echo json_encode([
            "estado" => "ok",
            "mensaje" => "Usuario eliminado exitosamente",
            "usuario_eliminado" => [
                "id" => $usuario['id_usuario'],
                "nombre" => $usuario['nom_usu'],
                "correo" => $usuario['correo']
            ]
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $conn->autocommit(TRUE);
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "estado" => "error",
        "mensaje" => $e->getMessage()
    ]);
}

$conn->close();
?>
