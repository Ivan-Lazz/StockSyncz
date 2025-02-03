<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/party.php';

$database = new Database();
$db = $database->getConnection();
$party = new Party($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $party->id = $data->id;

    if ($party->delete()) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Party was deleted."
        ));
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Unable to delete party."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to delete party. No ID provided."
    ));
}