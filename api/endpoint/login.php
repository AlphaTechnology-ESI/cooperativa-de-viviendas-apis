<?php  
session_start();  
include("../config/db.php");  
  
if ($_SERVER["REQUEST_METHOD"] === "POST") {  
    $correo = $_POST['correo'];  
    $contrasena = $_POST['contrasena'];  
  
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");  
    $stmt->bind_param("s", $correo);  
    $stmt->execute();  
    $resultado = $stmt->get_result();  
  
    if ($resultado->num_rows === 1) {  
        $fila = $resultado->fetch_assoc();  
        if ($contrasena === $fila['contraseña']) {  
            $_SESSION['correo'] = $correo;  
            echo "Login correcto";  
        } else {  
            echo "Contraseña incorrecta";  
        }  
    } else {  
        echo "Correo no encontrado";  
    }  
}  
?>
