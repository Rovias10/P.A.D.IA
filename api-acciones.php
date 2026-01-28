<?php 
header('Content-Type: application/json');
require 'db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$alert_id = $data['alert_id'] ?? null;

$vector = $data['vector'] ?? $data['vector_detectado'] ?? 'Amenaza Desconocida';
$parche = $data['parche'] ?? $data['codigo_tecnico'] ?? '';
$explicacion = $data['explicacion'] ?? $data['explicacion_corta'] ?? 'Defensa aplicada.';

if (!$alert_id) {
    echo json_encode(["status" => "error", "message" => "Falta alert_id"]);
    exit;
}

try {
    
    if (file_exists('saturacion.lock')) {
        unlink('saturacion.lock'); // Elimina el bloqueo
    }

   
    if (!empty($parche)) {
        
        $sqlDef = "INSERT INTO active_defenses (alert_id, threat_vector, patch_code) 
                   VALUES (:aid, :vec, :code)";
        $stmtDef = $pdo->prepare($sqlDef);
        $stmtDef->execute([
            ':aid' => $alert_id,
            ':vec' => $vector,
            ':code' => $parche
        ]);
    }

    // ---------------------------------------------------------
    // 3. ACTUALIZACIÓN DEL MONITOR (Poner en VERDE)
    // ---------------------------------------------------------
    $sqlLog = "INSERT INTO alerts_logs (alert_id, status, message, response_time) 
               VALUES (:aid, 'RESOLVED', :msg, 15)";
    
    $mensaje_final = "🛡️ AUTO-DEFENSA: " . $explicacion;
    
    $stmtLog = $pdo->prepare($sqlLog);
    $stmtLog->execute([
        ':aid' => $alert_id, 
        ':msg' => $mensaje_final
    ]);

    echo json_encode(["status" => "success", "accion" => "Defensa completa ejecutada y servidor recuperado"]);

} catch (Exception $e) {
    
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>