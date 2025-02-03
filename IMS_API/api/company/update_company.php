<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/company.php';

$database = new Database();
$db = $database->getConnection();
$company = new Company($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id) && !empty($data->companyname)) {
    $company->id = $data->id;
    $company->companyname = $data->companyname;

    if ($company->update()) {
        http_response_code(200);
        echo json_encode(array("message" => "Company was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Company already exists or unable to update company."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update company. Data is incomplete."));
}