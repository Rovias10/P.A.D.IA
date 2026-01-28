<?php
require "../db.php";

$username="Rodrigo";
$email_usuario = "villanuevaasensiorodrigogmail.com";
$password_usuario="Rodrigo10";
$password_usuario_hash = password_hash($password_usuario, PASSWORD_BCRYPT);

$sentencia="INSERT INTO users (name,email,password) VALUES (:nombre, :email, :pass)";

$stmt= $pdo->prepare($sentencia);

$stmt->execute([
    ':nombre' => $username,
    ':email' => $email_usuario,
    ':pass' => $password_usuario_hash,
]);
