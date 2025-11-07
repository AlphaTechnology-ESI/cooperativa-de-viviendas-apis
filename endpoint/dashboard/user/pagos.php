<?php
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

// Determinar si es multipart/form-data o JSON
$accion = "";
$id_usuario = 0;

if (isset($_POST['accion'])) {
    // Multipart form data (subida de archivo)
    $accion = $_POST['accion'];
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
} else {
    // JSON
    $input = json_decode(file_get_contents("php://input"), true);
    $accion = $input['accion'] ?? "";
    $id_usuario = intval($input['id_usuario'] ?? 0);
}

if (!$id_usuario) {
    echo json_encode(["estado" => "error", "mensaje" => "ID de usuario no proporcionado"]);
    exit;
}

// Listar pagos del usuario
if ($accion === "listar") {
    // Primero, verificar y crear el pago mensual del mes actual si no existe
    $mes_actual = date('Y-m-01'); // Primer día del mes actual
    
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
            'comprobante_pago' => $row['comprobante_pago'],
            'tipo' => 'aporte_inicial',
            'monto' => 500000 // $500.000
        ];
        // Agregar al inicio del array
        array_unshift($pagos, $aporte);
    }
    $stmt_aporte->close();

    echo json_encode([
        "estado" => "ok",
        "pagos" => $pagos
    ]);
    $conn->close();
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
    $input = json_decode(file_get_contents("php://input"), true);
    $id_pago = intval($input['id_pago'] ?? 0);

    if (!$id_pago) {
        echo json_encode(["estado" => "error", "mensaje" => "ID de pago no proporcionado"]);
        exit;
    }

    $sql = "SELECT comprobante_pago FROM pago_mensual WHERE id_pago = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_pago, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $comprobante = $row['comprobante_pago'];
        
        if ($comprobante) {
            // Determinar el tipo de archivo
            $extension = strtolower(pathinfo($comprobante, PATHINFO_EXTENSION));
            $tipo = ($extension === 'pdf') ? 'pdf' : 'image';
            
            // En este caso, como guardamos solo el nombre, necesitarías la ruta completa
            // Por ahora retornamos un mensaje indicando que existe
            echo json_encode([
                "estado" => "ok",
                "mensaje" => "Comprobante encontrado",
                "comprobante_nombre" => $comprobante,
                "tipo" => $tipo
            ]);
        } else {
            echo json_encode(["estado" => "error", "mensaje" => "No hay comprobante disponible"]);
        }
    } else {
        echo json_encode(["estado" => "error", "mensaje" => "Pago no encontrado"]);
    }
    exit;
}

echo json_encode(["estado" => "error", "mensaje" => "Acción no válida"]);
$conn->close();
?>