<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/unit.php';

$database = new Database();
$db = $database->getConnection();
$unit = new Unit($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $unit->id = $data->id;

    if ($unit->delete()) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "message" => "Unit was deleted."
        ]);
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => false,
            "message" => "Unable to delete unit."
        ]);
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to delete unit. No ID provided."
    ]);
}