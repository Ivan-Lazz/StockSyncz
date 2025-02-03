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

    http_response_code(200);
    echo json_encode($cart_items);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Unable to get cart items",
        "error" => $e->getMessage()
    ));
}