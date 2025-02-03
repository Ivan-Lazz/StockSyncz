<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->company_name) && !empty($data->product_name) && 
    !empty($data->unit) && !empty($data->packing_size)) {
    
    $product->company_name = $data->company_name;
    $product->product_name = $data->product_name;
    $product->unit = $data->unit;
    $product->packing_size = $data->packing_size;

    if ($product->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Product was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Product already exists or unable to create product."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
}