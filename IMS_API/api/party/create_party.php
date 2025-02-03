<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/party.php';

$database = new Database();
$db = $database->getConnection();
$party = new Party($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->firstname) && !empty($data->lastname) && 
    !empty($data->businessname) && !empty($data->contact) &&
    !empty($data->city)) {
    
    $party->firstname = $data->firstname;
    $party->lastname = $data->lastname;
    $party->businessname = $data->businessname;
    $party->contact = $data->contact;
    $party->address = $data->address;
    $party->city = $data->city;

    if ($party->create()) {
        $status_code = 201;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Party was created."
        ));
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Business already exists or unable to create party."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to create party. Data is incomplete."
    ));
}