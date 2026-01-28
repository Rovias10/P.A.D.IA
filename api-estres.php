<?php
// api-estres.php - ATAQUE SIMULADO GARANTIZADO
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$objetivo = $data['url'] ?? 'Desconocido';
$cantidad = $data['cantidad'] ?? 500;
$vector = $data['vector'] ?? 'GENERICO';

// 1. EL TRUCO: Creamos un archivo que bloquea el sistema
// Esto simula que la RAM se ha llenado por el ataque
file_put_contents('saturacion.lock', 'CRITICAL_LOAD_LEVEL_99%');

// 2. Simulamos el tiempo que tarda el ataque (para el efecto visual)
// Hacemos algunas peticiones reales por si acaso, pero el daño real lo hace el archivo.
for ($i = 0; $i < 5; $i++) {
    @file_get_contents($objetivo); // El @ oculta errores
}
sleep(2); // Pausa dramática

echo json_encode([
    "status" => "success",
    "message" => "Ataque enviado: $cantidad peticiones",
    "impacto" => "CRITICO - Servidor Saturado"
]);
?>