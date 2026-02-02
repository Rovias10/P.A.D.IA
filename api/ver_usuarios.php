<?php
header('Content-type:application/json');
require "../config/db.php";

$sql= "SELECT id,name,email from users";
$stmt = $pdo->prepare($sql);

$stmt->execute();

$usuarios=$stmt->fetchAll();
echo json_encode($usuarios,JSON_PRETTY_PRINT);
