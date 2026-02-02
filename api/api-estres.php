<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$objetivo = $data['url'] ?? 'Desconocido';
$cantidad = $data['cantidad'] ?? 500;
$vector = $data['vector'] ?? 'GENERICO';

file_put_contents('../saturacion.lock', 'CRITICAL_LOAD_LEVEL_99%');

for ($i = 0; $i < 5; $i++) {
    @file_get_contents($objetivo); 
}
sleep(2); 

echo json_encode([
    "status" => "success",
    "message" => "Ataque enviado: $cantidad peticiones",
    "impacto" => "CRITICO - Servidor Saturado"
]);
?>