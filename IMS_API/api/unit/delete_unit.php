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
        http_response_code(200);
        echo json_encode(array("message" => "Unit was deleted."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete unit."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to delete unit. No ID provided."));
}