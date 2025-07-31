<?php
$host = "localhost";
$user = "root";
$pass = "";
$db= "cooperativa_cooptrack";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = file_get_contents("http://localhost/cooperativa-de-viviendas-apis/api/config/db.sql");

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} 
?>
