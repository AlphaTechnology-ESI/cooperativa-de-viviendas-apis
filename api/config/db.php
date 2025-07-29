<?php
$host = 'sql10.freesqldatabase.com';
$db   = 'sql10791490';
$user = 'sql10791490';
$pass = 'BetwvYBKnN';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
