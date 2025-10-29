<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (file_exists(__DIR__ . '/db_externa.php')) {
    include __DIR__ . '/db_externa.php';
} else {
    $host = 'host.docker.internal';
    $db = 'cooperativa_cooptrack';
    $user = 'root';
    $pass = '';
    $port = 3306;
}

$conn = new mysqli($host, $user, $pass, "", $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Leer archivo SQL
$sql_file = __DIR__ . '/db.sql';
if (!file_exists($sql_file)) {
    die("db.sql no se encuentra en: $sql_file");
}

$sql = file_get_contents($sql_file);
if (!$sql) {
    die("No se pudo leer db.sql");
}

// Ejecutar cada sentencia por separado
$statements = explode(";", $sql);
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if ($stmt) {
        if (!$conn->query($stmt)) {
            die("Error ejecutando query: " . $conn->error . "\nQuery: $stmt");
        }
    }
}

$conn->select_db($db);