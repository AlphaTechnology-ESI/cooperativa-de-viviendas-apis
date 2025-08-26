<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$pass = getenv('DB_PASS');
$user = getenv('DB_USER');
$port = intval(getenv('DB_PORT'));

$conn = new mysqli($host, $user, $pass, $db, $port);

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

echo "db.sql ejecutado correctamente.";
?>
