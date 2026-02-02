<?php 
header('Content-Type: application/json');
require "../config/db.php";

$sql= "SELECT * from alerts";

$smrt = $pdo->prepare($sql);

$smrt->execute();

$alertas = $smrt->fetchAll(PDO::FETCH_ASSOC);
$alertas_decodificadas=[];

foreach($alertas as $alerta){
    $fila_procesada = $alerta;
    $fila_procesada["config"]=json_decode($alerta["config"]);
    $alertas_decodificadas[]=$fila_procesada;
}
echo json_encode($alertas_decodificadas, JSON_PRETTY_PRINT);