<?php 
require_once "../config/db.php"; 
$usuario = "vendedor"; 
$password = "1234567"; 
$nombre = "Vendedor"; 
$id_rol = 2; 
/* ========================================== 
GENERAR HASH DE LA CONTRASEÑA 
   ========================================== */ 
 
$password_hash = password_hash( 
    $password, 
    PASSWORD_DEFAULT 
); 
 
/* ========================================== 
   INSERTAR USUARIO 
   ========================================== */ 
 
$sql = "INSERT INTO usuarios 
        (usuario, password, nombre, id_rol) 
        VALUES (:usuario, :password, :nombre, :id_rol)"; 
 
$stmt = $pdo->prepare($sql); 
 
$stmt->execute([ 
    ':usuario' => $usuario, 
    ':password' => $password_hash, 
    ':nombre' => $nombre, 
    ':id_rol' => $id_rol 
]); 
 
echo "Usuario creado correctamente."; 
 
?>