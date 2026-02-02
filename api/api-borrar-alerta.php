<?php
header('Content-Type: application/json');
require "../config/db.php";

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['id'])) {
    echo json_encode(["status" => "error", "message" => "Falta ID"]);
    exit;
}

try {    
    $sql = "DELETE FROM alerts WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $data['id']]);
    $sqlLogs = "DELETE FROM alerts_logs WHERE alert_id = :id";
    $stmtLogs = $pdo->prepare($sqlLogs);
    $stmtLogs->execute([':id' => $data['id']]);

    echo json_encode(["status" => "success"]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>