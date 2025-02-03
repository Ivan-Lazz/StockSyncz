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
        // Clear the item but maintain array structure
        $_SESSION['cart'][$data->session_id] = array(
            "company_name" => "",
            "product_name" => "",
            "unit" => "",
            "packing_size" => "",
            "price" => "",
            "qty" => ""
        );

        http_response_code(200);
        echo json_encode(array("message" => "Item removed from cart"));
    } else {
        throw new Exception("Item not found in cart");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Unable to delete item",
        "error" => $e->getMessage()
    ));
}