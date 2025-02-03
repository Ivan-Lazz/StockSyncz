<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/company.php';

$database = new Database();
$db = $database->getConnection();
$company = new Company($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $company->id = $data->id;

    if ($company->delete()) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Company was deleted."
        ));
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Unable to delete company."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to delete company. No ID provided."
    ));
}