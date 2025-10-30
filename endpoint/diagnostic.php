<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

function fail($msg) {
    http_response_code(500);
    exit("Error: $msg");
}

// 1. Probar extensiones críticas
$requiredExtensions = ['mysqli', 'json', 'curl', 'session'];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        fail("Extensión PHP '$ext' no está cargada");
    }
}

// 2. Probar conexión a la base de datos
include("../config/db.php");
if (!isset($conn) || !$conn instanceof mysqli) {
    fail("No se creó el objeto mysqli desde config/db.php");
}
if ($conn->connect_error) {
    fail("Error conectando a la BD: " . $conn->connect_error);
}

// 3. Probar que hay al menos una tabla
$tablesResult = $conn->query("SHOW TABLES");
if (!$tablesResult) {
    fail("No se pudieron obtener tablas: " . $conn->error);
}
if ($tablesResult->num_rows === 0) {
    fail("La base de datos no tiene tablas");
}

// 4. Probar sesiones
session_start();
$_SESSION['test_key'] = 'ok';
if ($_SESSION['test_key'] !== 'ok') {
    fail("No se pudo escribir en la sesión");
}

// 5. Probar consulta simple sin especificar columnas
// Tomar la primera tabla y hacer un SELECT COUNT(*)
$tablesResult->data_seek(0);
$firstTableRow = $tablesResult->fetch_array();
$firstTableName = $firstTableRow[0];
$countRes = $conn->query("SELECT COUNT(*) AS c FROM `$firstTableName`");
if (!$countRes) {
    fail("No se pudo hacer COUNT(*) en la tabla '$firstTableName': " . $conn->error);
}
$countRow = $countRes->fetch_assoc();
if (!isset($countRow['c'])) {
    fail("COUNT(*) no devolvió resultado en '$firstTableName'");
}

echo "ok";