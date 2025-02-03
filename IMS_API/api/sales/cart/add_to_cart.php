<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../config/database.php';
include_once '../../../models/sales.php';

session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $sales = new Sales($db);

    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
        throw new Exception("No data received");
    }

    // Validate required fields
    $required_fields = ['company_name', 'product_name', 'unit', 'packing_size', 'price', 'qty'];
    foreach ($required_fields as $field) {
        if (!isset($data->$field)) {
            throw new Exception("Missing required field: $field");
        }
    }

    $cart_item = array(
        'company_name' => $data->company_name,
        'product_name' => $data->product_name,
        'unit' => $data->unit,
        'packing_size' => $data->packing_size,
        'price' => floatval($data->price),
        'qty' => floatval($data->qty)
    );

    $result = $sales->addToCart($cart_item);

    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "message" => "Product added to cart successfully"
    ]);

} catch (Exception $e) {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to add product to cart",
        "error" => $e->getMessage()
    ]);
}