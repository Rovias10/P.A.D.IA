<?php
// target_api.php
// Objetivo Inteligente: Se "cae" si detecta saturación

// 1. Chequeo de Salud (Simulación de carga)
if (file_exists('../saturacion.lock')) {
    
    http_response_code(500); // Código de Error Fatal
    header('HTTP/1.1 500 Internal Server Error');
    die("FATAL ERROR: Memory Exhausted due to DDoS Attack.");
}

header('Content-Type: application/json');
header('X-Powered-By: Express/4.17'); 

$data = [
    "status" => "active",
    "users_connected" => rand(100, 999),
    "system_load" => "Normal"
];

echo json_encode($data, JSON_PRETTY_PRINT);
?>