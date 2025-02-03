<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/purchase.php';

$database = new Database();
$db = $database->getConnection();
$purchase = new Purchase($db);

if (isset($_GET['unit']) && isset($_GET['product_name']) && isset($_GET['company_name'])) {
    $stmt = $purchase->getPackingSizes($_GET['unit'], $_GET['product_name'], $_GET['company_name']);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $sizes_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($sizes_arr, array(
                "packing_size" => $row['packing_size']
            ));
        }
        http_response_code(200);
        echo json_encode($sizes_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No packing sizes found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required parameters."));
}

