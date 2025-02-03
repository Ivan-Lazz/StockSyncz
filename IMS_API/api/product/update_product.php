<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if we have the required data
if (!empty($data->id) && !empty($data->company_name) && 
    !empty($data->product_name) && !empty($data->unit) && 
    !empty($data->packing_size)) {
    
    $product->id = $data->id;
    $product->company_name = $data->company_name;
    $product->product_name = $data->product_name;
    $product->unit = $data->unit;
    $product->packing_size = $data->packing_size;

    if ($product->update()) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "message" => "Product was updated."
        ]);
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => false,
            "message" => "Product already exists or unable to update product."
        ]);
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to update product. Data is incomplete."
    ]);
}
?>