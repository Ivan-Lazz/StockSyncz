<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/party.php';

$database = new Database();
$db = $database->getConnection();
$party = new Party($db);

if (isset($_GET['id'])) {
    $stmt = $party->readSingle($_GET['id']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "data" => $row
        ));
    } else {
        $status_code = 404;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Party not found."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "No ID provided."
    ));
}