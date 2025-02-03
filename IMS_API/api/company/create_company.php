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
        $status_code = 201;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Company was created."
        ));
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Company already exists or unable to create company."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to create company. Data is incomplete."
    ));
}