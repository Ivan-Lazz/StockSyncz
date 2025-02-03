<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/unit.php';

$database = new Database();
$db = $database->getConnection();
$unit = new Unit($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->unit)) {
    $unit->unit = $data->unit;

    if ($unit->create()) {
        $status_code = 201;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "message" => "Unit was created."
        ]);
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => false,
            "message" => "Unit already exists or unable to create unit."
        ]);
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to create unit. Data is incomplete."
    ]);
}