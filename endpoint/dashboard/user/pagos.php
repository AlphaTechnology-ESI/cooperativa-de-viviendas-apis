<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

include("../../../config/db.php");

// LOG DE DEBUG
error_log("=== INICIO REQUEST ===");
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'NO DEFINIDO'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);

// Leer parámetros de diferentes fuentes
$accion = "";
$id_usuario = 0;

// Guardar el input raw para no perderlo
$rawInput = file_get_contents("php://input");
error_log("Raw Input Length: " . strlen($rawInput));
error_log("Raw Input: " . substr($rawInput, 0, 200));

// Determinar si es JSON o FormData revisando el Content-Type
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
error_log("POST vars: " . print_r($_POST, true));
error_log("GET vars: " . print_r($_GET, true));

if (strpos($contentType, 'application/json') !== false && $rawInput) {
    // Es JSON
    error_log("Detectado como JSON");
    $input = json_decode($rawInput, true);
    if ($input) {
        $accion = $input['accion'] ?? "";
        $id_usuario = intval($input['id_usuario'] ?? 0);
        error_log("Accion desde JSON: " . $accion);
        error_log("ID Usuario desde JSON: " . $id_usuario);
    }
} else {
    // Es FormData o parámetros URL
    error_log("Detectado como FormData/URL");
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? "";
    $id_usuario = intval($_POST['id_usuario'] ?? $_GET['id_usuario'] ?? 0);
    error_log("Accion desde POST/GET: " . $accion);
    error_log("ID Usuario desde POST/GET: " . $id_usuario);
}

// Si no hay acción pero hay id_usuario, asumir que es listar
if ($accion === "" && $id_usuario > 0) {
    $accion = "listar";
    error_log("Accion ajustada a: listar");
}

error_log("Accion final: " . $accion);
error_log("ID Usuario final: " . $id_usuario);

if (!$id_usuario) {
    error_log("ERROR: ID de usuario no proporcionado");
    echo json_encode(["estado" => "error", "mensaje" => "ID de usuario no proporcionado"]);
    exit;
}

// Listar pagos del usuario
if ($accion === "listar") {
    // Primero, verificar y crear el pago mensual del mes actual si no existe
    $mes_actual = date('Y-m-01');
    
    $sql_check = "SELECT id_pago FROM pago_mensual 
                  WHERE id_usuario = ? AND fecha = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $id_usuario, $mes_actual);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    // Si no existe pago para este mes, crearlo
    if ($result_check->num_rows === 0) {
        $sql_insert = "INSERT INTO pago_mensual (id_usuario, estado_pago, fecha) 
                       VALUES (?, 'pendiente', ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("is", $id_usuario, $mes_actual);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close();
    
    // Obtener todos los pagos mensuales
    $sql = "SELECT id_pago, fecha, fecha_envio, estado_pago, comprobante_pago 
            FROM pago_mensual 
            WHERE id_usuario = ? 
            ORDER BY fecha DESC, id_pago DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $pagos = [];
    while ($row = $result->fetch_assoc()) {
        $row['tipo'] = 'mensual';
        $row['monto'] = 150000; // $150.000
        // Convertir BLOB a booleano para indicar si tiene comprobante
        $row['tiene_comprobante'] = !empty($row['comprobante_pago']);
        unset($row['comprobante_pago']); // Remover el BLOB
        $pagos[] = $row;
    }
    $stmt->close();
    
    // Obtener el aporte inicial
    $sql_aporte = "SELECT id_aporte, fecha, estado_validacion, comprobante_pago 
                   FROM aporte_inicial 
                   WHERE id_usuario = ?";
    $stmt_aporte = $conn->prepare($sql_aporte);
    $stmt_aporte->bind_param("i", $id_usuario);
    $stmt_aporte->execute();
    $result_aporte = $stmt_aporte->get_result();
    
    if ($result_aporte->num_rows === 0) {
        // Si no existe aporte inicial, crearlo
        $sql_insert_aporte = "INSERT INTO aporte_inicial (id_usuario, estado_validacion) 
                              VALUES (?, 'pendiente')";
        $stmt_insert_aporte = $conn->prepare($sql_insert_aporte);
        $stmt_insert_aporte->bind_param("i", $id_usuario);
        $stmt_insert_aporte->execute();
        $stmt_insert_aporte->close();
        
        // Volver a consultar
        $stmt_aporte = $conn->prepare($sql_aporte);
        $stmt_aporte->bind_param("i", $id_usuario);
        $stmt_aporte->execute();
        $result_aporte = $stmt_aporte->get_result();
    }
    
    while ($row = $result_aporte->fetch_assoc()) {
        $aporte = [
            'id_pago' => 'aporte_' . $row['id_aporte'],
            'id_aporte' => $row['id_aporte'],
            'fecha' => $row['fecha'],
            'fecha_envio' => null,
            'estado_pago' => $row['estado_validacion'],
            'tiene_comprobante' => !empty($row['comprobante_pago']),
            'tipo' => 'aporte_inicial',
            'monto' => 500000 
        ];
        // Agregar al inicio del array
        array_unshift($pagos, $aporte);
    }
    $stmt_aporte->close();

    error_log("Preparando respuesta JSON con " . count($pagos) . " pagos");
    $response = [
        "estado" => "ok",
        "pagos" => $pagos
    ];
    $jsonResponse = json_encode($response);
    error_log("JSON generado, longitud: " . strlen($jsonResponse));
    echo $jsonResponse;
    $conn->close();
    error_log("Respuesta enviada y conexión cerrada");
    exit;
}

// Registrar pago
if ($accion === "registrar_pago") {
    $id_pago = $_POST['id_pago'] ?? "";
    $tipo_pago = $_POST['tipo_pago'] ?? "mensual";
    
    if (!$id_pago) {
        echo json_encode(["estado" => "error", "mensaje" => "ID de pago no proporcionado"]);
        exit;
    }

    if (!isset($_FILES['comprobante']) || $_FILES['comprobante']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["estado" => "error", "mensaje" => "Error al subir el comprobante"]);
        exit;
    }

    $file = $_FILES['comprobante'];
    $fileData = file_get_contents($file['tmp_name']);
    $fecha_envio = date('Y-m-d');

    if ($tipo_pago === "aporte_inicial") {
        $id_aporte = intval(str_replace('aporte_', '', $id_pago));
        
        $sql_verify = "SELECT * FROM aporte_inicial WHERE id_aporte = ? AND id_usuario = ?";
        $stmt_verify = $conn->prepare($sql_verify);
        $stmt_verify->bind_param("ii", $id_aporte, $id_usuario);
        $stmt_verify->execute();
        $result_verify = $stmt_verify->get_result();

        if ($result_verify->num_rows === 0) {
            $stmt_verify->close();
            echo json_encode(["estado" => "error", "mensaje" => "Aporte no encontrado"]);
            $conn->close();
            exit;
        }
        $stmt_verify->close();

        $sql_update = "UPDATE aporte_inicial 
                       SET comprobante_pago = ?, 
                           fecha = ?,
                           estado_validacion = 'pendiente'
                       WHERE id_aporte = ? AND id_usuario = ?";
        
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            echo json_encode(["estado" => "error", "mensaje" => "Error al preparar consulta: " . $conn->error]);
            $conn->close();
            exit;
        }
        
        $null = NULL;
        $stmt_update->bind_param("bsii", $null, $fecha_envio, $id_aporte, $id_usuario);
        $stmt_update->send_long_data(0, $fileData);

        if ($stmt_update->execute()) {
            echo json_encode([
                "estado" => "ok",
                "mensaje" => "Aporte inicial registrado correctamente. Pendiente de aprobación."
            ]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el aporte: " . $stmt_update->error]);
        }
        $stmt_update->close();
    } else {
        $id_pago_num = intval($id_pago);
        
        $sql_verify = "SELECT * FROM pago_mensual WHERE id_pago = ? AND id_usuario = ?";
        $stmt_verify = $conn->prepare($sql_verify);
        $stmt_verify->bind_param("ii", $id_pago_num, $id_usuario);
        $stmt_verify->execute();
        $result_verify = $stmt_verify->get_result();

        if ($result_verify->num_rows === 0) {
            $stmt_verify->close();
            echo json_encode(["estado" => "error", "mensaje" => "Pago no encontrado"]);
            $conn->close();
            exit;
        }
        $stmt_verify->close();

        $sql_update = "UPDATE pago_mensual 
                       SET comprobante_pago = ?, 
                           fecha_envio = ?,
                           estado_pago = 'pendiente'
                       WHERE id_pago = ? AND id_usuario = ?";
        
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            echo json_encode(["estado" => "error", "mensaje" => "Error al preparar consulta: " . $conn->error]);
            $conn->close();
            exit;
        }
        
        $null = NULL;
        $stmt_update->bind_param("bsii", $null, $fecha_envio, $id_pago_num, $id_usuario);
        $stmt_update->send_long_data(0, $fileData);

        if ($stmt_update->execute()) {
            echo json_encode([
                "estado" => "ok",
                "mensaje" => "Pago registrado correctamente. Pendiente de aprobación."
            ]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "Error al registrar el pago: " . $stmt_update->error]);
        }
        $stmt_update->close();
    }
    $conn->close();
    exit;
}

// Ver comprobante
if ($accion === "ver_comprobante") {
    // Reutilizar rawInput guardado
    $input = json_decode($rawInput, true);
    $id_pago = intval($input['id_pago'] ?? $_POST['id_pago'] ?? $_GET['id_pago'] ?? 0);
    $id_aporte = intval($input['id_aporte'] ?? $_POST['id_aporte'] ?? $_GET['id_aporte'] ?? 0);
    $tipo = $input['tipo'] ?? $_POST['tipo'] ?? $_GET['tipo'] ?? 'mensual';

    error_log("Ver comprobante - tipo: $tipo, id_pago: $id_pago, id_aporte: $id_aporte");

    if ($tipo === 'aporte_inicial' && $id_aporte > 0) {
        $sql = "SELECT comprobante_pago FROM aporte_inicial WHERE id_aporte = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_aporte, $id_usuario);
    } else if ($id_pago > 0) {
        $sql = "SELECT comprobante_pago FROM pago_mensual WHERE id_pago = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_pago, $id_usuario);
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "ID de pago o aporte no proporcionado"]);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $comprobante = $row['comprobante_pago'];
        
        if ($comprobante) {
            // Convertir el BLOB a base64
            $comprobante_base64 = base64_encode($comprobante);
            
            echo json_encode([
                "estado" => "ok",
                "mensaje" => "Comprobante encontrado",
                "comprobante" => $comprobante_base64
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

echo json_encode(["estado" => "error", "mensaje" => "Acción no válida"]);
$conn->close();
?>