<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

session_start();

try {
    $total = 0;

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            if ($item['company_name'] !== "") {
                $total += floatval($item['price']) * floatval($item['qty']);
            }
        }
    }

    http_response_code(200);
    echo json_encode(array("total" => $total));

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Unable to calculate total",
        "error" => $e->getMessage()
    ));
}