<?php
$correo = "user@gmail.com";
$contrasena = "4321";

include("../config/db.php");

$result = $conn->query("SELECT correo FROM usuario");
echo "Correos en la tabla usuario:";
while ($row = $result->fetch_assoc()) {
    echo "'" .  $row['correo'] . "' ";}
    