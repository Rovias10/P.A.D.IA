<?php
header('Content-Type: application/json');
require '../config/db.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$alert_id = $data['alert_id'] ?? 0;
$vector = $data['vector'] ?? 'UNKNOWN';
$codigo_parche = $data['parche'] ?? '';
$explicacion = $data['explicacion'] ?? '';

try {
    $sql = "INSERT INTO active_defenses (alert_id, threat_vector, patch_code) 
            VALUES (:aid, :vec, :code)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':aid' => $alert_id,
        ':vec' => $vector,
        ':code' => $codigo_parche
    ]);

    $sqlLog = "INSERT INTO alerts_logs (alert_id, status, message, response_time) 
               VALUES (:aid, 'PATCHED', :msg, 10)";
    $stmtLog = $pdo->prepare($sqlLog);
    $stmtLog->execute([
        ':aid' => $alert_id,
        ':msg' => "DEFENSA ACTIVA: " . $explicacion
    ]);

    echo json_encode(["status" => "success", "action" => "Patch Applied"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>