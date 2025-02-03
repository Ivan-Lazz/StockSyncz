<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/party.php';

$database = new Database();
$db = $database->getConnection();
$party = new Party($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->firstname) && !empty($data->lastname) &&
    !empty($data->businessname) && !empty($data->contact) && !empty($data->city)) {
    
    $party->id = $data->id;
    $party->firstname = $data->firstname;
    $party->lastname = $data->lastname;
    $party->businessname = $data->businessname;
    $party->contact = $data->contact;
    $party->address = $data->address;
    $party->city = $data->city;

    if ($party->update()) {
        http_response_code(200);
        echo json_encode(array("message" => "Party was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update party."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update party. Data is incomplete."));
}