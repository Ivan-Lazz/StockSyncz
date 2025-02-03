<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../config/database.php';
include_once '../../../models/sales.php';

session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $sales = new Sales($db);

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->session_id) || !isset($data->qty)) {
        throw new Exception("Missing required data");
    }

    if (!isset($_SESSION['cart'][$data->session_id])) {
        throw new Exception("Item not found in cart");
    }

    $cart_item = $_SESSION['cart'][$data->session_id];
    $available_qty = $sales->checkQuantityAvailable($cart_item);

    if ($available_qty >= $data->qty) {
        $_SESSION['cart'][$data->session_id]['qty'] = $data->qty;
        http_response_code(200);
        echo json_encode(array("message" => "Cart updated successfully"));
    } else {
        throw new Exception("Requested quantity not available");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Unable to update cart",
        "error" => $e->getMessage()
    ));
}