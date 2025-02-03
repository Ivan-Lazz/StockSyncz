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
        http_response_code(200);
        echo json_encode($row);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Party not found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "No ID provided."));
}