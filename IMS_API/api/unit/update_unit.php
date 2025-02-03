<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/unit.php';

$database = new Database();
$db = $database->getConnection();
$unit = new Unit($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->unit)) {
    $unit->id = $data->id;
    $unit->unit = $data->unit;

    if ($unit->update()) {
        http_response_code(200);
        echo json_encode(array("message" => "Unit was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unit already exists or unable to update unit."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update unit. Data is incomplete."));
}