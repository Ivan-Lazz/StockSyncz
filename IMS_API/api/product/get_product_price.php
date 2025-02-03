<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';
include_once '../../models/sales.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $sales = new Sales($db);

    if (!isset($_GET['company_name']) || !isset($_GET['product_name']) || 
        !isset($_GET['unit']) || !isset($_GET['packing_size'])) {
        throw new Exception("Missing required parameters");
    }

    $product_data = array(
        'company_name' => $_GET['company_name'],
        'product_name' => $_GET['product_name'],
        'unit' => $_GET['unit'],
        'packing_size' => $_GET['packing_size']
    );

    $price = $sales->getProductPrice($product_data);

    http_response_code(200);
    echo json_encode(array("price" => $price));

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Unable to get product price",
        "error" => $e->getMessage()
    ));
}