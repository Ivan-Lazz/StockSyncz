<?php 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();

try {
    $cart_items = array();

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['company_name'] !== "") {
                $item['session_id'] = $index;
                $item['total'] = floatval($item['price']) * floatval($item['qty']);
                array_push($cart_items, $item);
            }
        }
    }

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "data" => $cart_items
    ]);

} catch (Exception $e) {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to get cart items",
        "error" => $e->getMessage()
    ]);
}