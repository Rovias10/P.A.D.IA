<?php
header('Content-Type:application/json');
require "db.php";
$input= file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null || !isset($data['url']) || empty($data['url'])) {
    echo json_encode(["status" => "error", "message" => "Falta la URL"]);
    exit;
}

$user_id=1;
$type="WEB_MONITOR";
$config_array=[
    'url' => $data['url'],
    'method' => $data['method'] ?? 'GET',
    'timeout' =>30,
];

$config_mysql = json_encode($config_array, JSON_PRETTY_PRINT);

try{
    $sql="INSERT INTO alerts (user_id, type, config) values (:userid,:type,:config)";

    $smrt= $pdo->prepare($sql);
    $smrt->execute([
        ":userid" => $user_id,
        ":type" => $type,
        ":config" => $config_mysql,
    ]);
    echo json_encode(["status" => "success", "message" => $pdo->lastInsertId()]);

}catch(PDOException $e){

    echo json_encode(["status" => "error", "message" => $e->getMessage()]);

}