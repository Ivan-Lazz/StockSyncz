<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

session_start();

try {
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->session_id)) {
        throw new Exception("Session ID required");
    }

    if (isset($_SESSION['cart'][$data->session_id])) {
        $_SESSION['cart'][$data->session_id] = array(
            "company_name" => "",
            "product_name" => "",
            "unit" => "",
            "packing_size" => "",
            "price" => "",
            "qty" => ""
        );

        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "message" => "Item removed from cart"
        ]);
    } else {
        throw new Exception("Item not found in cart");
    }

} catch (Exception $e) {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to delete item",
        "error" => $e->getMessage()
    ]);
}