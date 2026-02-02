<?php 
header('Content-Type: application/json');
require "../config/db.php";

$json = file_get_contents('php://input');
$data = json_decode($json , true);

if(!isset($data['alert_id']) || !isset($data['status'])){
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos (alert_id o status)"]);
    exit;
}

$alert_id= $data['alert_id'];
$status = $data['status'];
$message =isset($data['message']) ? $data['message'] : '';
$response_time = isset($data['response_time']) ? (int)$data['response_time'] : 0;

try{
    $sql= "Insert into alerts_logs(alert_id,status,message,response_time) VALUES (:alert, :status, :message, :response)";
    $smrt = $pdo->prepare($sql);

    $smrt->execute(
        [
            ":alert" => $alert_id, 
            ":status"=> $status, 
            ':message'=>$message, 
            ":response"=>  $response_time
        ]
    );
    echo json_encode(["status" => "success", "id_log" => $pdo->lastInsertId()]);
}catch(PDOException $e){
    http_response_code(500); 
    echo json_encode(["status" => "error", "message" => "Error SQL: " . $e->getMessage()]);
}



