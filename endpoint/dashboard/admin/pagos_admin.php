<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

include("../../../config/db.php");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["estado" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$accion = $input['accion'] ?? "";

if (!$accion) {
    echo json_encode(["estado" => "error", "mensaje" => "No se proporcionó acción"]);
    exit;
}

// Listar todos los pagos
if ($accion === "listar") {
    $filtro_estado = $input['filtro_estado'] ?? "";
    $filtro_tipo = $input['filtro_tipo'] ?? "";
    
    $pagos = [];
    
    // Obtener pagos mensuales
    if ($filtro_tipo === "" || $filtro_tipo === "mensual") {
        $sql_mensual = "SELECT pm.id_pago, pm.fecha, pm.fecha_envio, pm.estado_pago as estado, 
                        pm.id_usuario, u.nom_usu, 'mensual' as tipo,
                        CASE WHEN pm.comprobante_pago IS NOT NULL THEN 1 ELSE 0 END as tiene_comprobante
                        FROM pago_mensual pm
                        INNER JOIN usuario u ON pm.id_usuario = u.id_usuario";
        
        if ($filtro_estado !== "") {
            $sql_mensual .= " WHERE pm.estado_pago = ?";
        }
        
        $sql_mensual .= " ORDER BY pm.fecha_envio DESC, pm.fecha DESC";
        
        $stmt_mensual = $conn->prepare($sql_mensual);
        
        if (!$stmt_mensual) {
            echo json_encode(["estado" => "error", "mensaje" => "Error en prepare mensual: " . $conn->error]);
            $conn->close();
            exit;
        }
        
        if ($filtro_estado !== "") {
            $stmt_mensual->bind_param("s", $filtro_estado);
        }
        
        if (!$stmt_mensual->execute()) {
            echo json_encode(["estado" => "error", "mensaje" => "Error execute mensual: " . $stmt_mensual->error]);
            $stmt_mensual->close();
            $conn->close();
            exit;
        }
        
        $result_mensual = $stmt_mensual->get_result();
        
        while ($row = $result_mensual->fetch_assoc()) {
            $pagos[] = $row;
        }
        $stmt_mensual->close();
    }
    
    // Obtener aportes iniciales
    if ($filtro_tipo === "" || $filtro_tipo === "aporte_inicial") {
        $sql_aporte = "SELECT ai.id_aporte, ai.fecha, ai.estado_validacion as estado, 
                       ai.id_usuario, u.nom_usu, 'aporte_inicial' as tipo,
                       ai.fecha as fecha_envio,
                       CASE WHEN ai.comprobante_pago IS NOT NULL THEN 1 ELSE 0 END as tiene_comprobante
                       FROM aporte_inicial ai
                       INNER JOIN usuario u ON ai.id_usuario = u.id_usuario";
        
        if ($filtro_estado !== "") {
            $sql_aporte .= " WHERE ai.estado_validacion = ?";
        }
        
        $sql_aporte .= " ORDER BY ai.fecha DESC";
        
        $stmt_aporte = $conn->prepare($sql_aporte);
        
        if (!$stmt_aporte) {
            echo json_encode(["estado" => "error", "mensaje" => "Error en prepare aporte: " . $conn->error]);
            $conn->close();
            exit;
        }
        
        if ($filtro_estado !== "") {
            $stmt_aporte->bind_param("s", $filtro_estado);
        }
        
        if (!$stmt_aporte->execute()) {
            echo json_encode(["estado" => "error", "mensaje" => "Error execute aporte: " . $stmt_aporte->error]);
            $stmt_aporte->close();
            $conn->close();
            exit;
        }
        
        $result_aporte = $stmt_aporte->get_result();
        
        while ($row = $result_aporte->fetch_assoc()) {
            $pagos[] = $row;
        }
        $stmt_aporte->close();
    }
    
    // Ordenar todos los pagos por fecha
    usort($pagos, function($a, $b) {
        $fecha_a = $a['fecha_envio'] ?? $a['fecha'] ?? '';
        $fecha_b = $b['fecha_envio'] ?? $b['fecha'] ?? '';
        return strcmp($fecha_b, $fecha_a);
    });
    
    echo json_encode([
        "estado" => "ok",
        "pagos" => $pagos
    ]);
    $conn->close();
    exit;
}

// Obtener comprobante
if ($accion === "obtener_comprobante") {
    $tipo = $input['tipo'] ?? "";
    
    if ($tipo === "mensual") {
        $id_pago = intval($input['id_pago'] ?? 0);
        
        $sql = "SELECT comprobante_pago FROM pago_mensual WHERE id_pago = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_pago);
        
    } else if ($tipo === "aporte_inicial") {
        $id_aporte = intval($input['id_aporte'] ?? 0);
        
        $sql = "SELECT comprobante_pago FROM aporte_inicial WHERE id_aporte = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_aporte);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Tipo no válido"]);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $comprobante = $row['comprobante_pago'];
        
        if ($comprobante) {
            $comprobanteBase64 = base64_encode($comprobante);
            echo json_encode([
                "estado" => "ok",
                "comprobante" => $comprobanteBase64
            ]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "No hay comprobante disponible"]);
        }
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Pago no encontrado"]);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Actualizar estado del pago
if ($accion === "actualizar_estado") {
    $tipo = $input['tipo'] ?? "";
    $estado = $input['estado'] ?? "";
    
    if ($tipo === "mensual") {
        $id_pago = intval($input['id_pago'] ?? 0);
        
        if (!$id_pago || !$estado) {
            echo json_encode(["estado" => "error", "mensaje" => "Datos incompletos"]);
            exit;
        }
        
        $sql_update = "UPDATE pago_mensual SET estado_pago = ? WHERE id_pago = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $estado, $id_pago);
        
        if ($stmt_update->execute()) {
            echo json_encode([
                "estado" => "ok",
                "mensaje" => "Pago actualizado correctamente"
            ]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar el pago"]);
        }
        $stmt_update->close();
        
    } else if ($tipo === "aporte_inicial") {
        $id_aporte = intval($input['id_aporte'] ?? 0);
        
        if (!$id_aporte || !$estado) {
            echo json_encode(["estado" => "error", "mensaje" => "Datos incompletos"]);
            exit;
        }
        
        $sql_update = "UPDATE aporte_inicial SET estado_validacion = ? WHERE id_aporte = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $estado, $id_aporte);
        
        if ($stmt_update->execute()) {
            echo json_encode([
                "estado" => "ok",
                "mensaje" => "Aporte inicial actualizado correctamente"
            ]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al actualizar el aporte"]);
        }
        $stmt_update->close();
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Tipo de pago no válido"]);
    }
    
    $conn->close();
    exit;
}

echo json_encode(["estado" => "error", "mensaje" => "Acción no válida"]);
$conn->close();
?>