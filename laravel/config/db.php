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

$sql = file_get_contents(__DIR__ . '/db.sql');

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} 
?>
