<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/company.php';

$database = new Database();
$db = $database->getConnection();
$company = new Company($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->companyname)) {
    $company->companyname = $data->companyname;

    if ($company->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Company was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Company already exists or unable to create company."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create company. Data is incomplete."));
}