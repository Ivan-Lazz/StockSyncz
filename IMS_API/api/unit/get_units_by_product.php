<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/purchase.php';

$database = new Database();
$db = $database->getConnection();
$purchase = new Purchase($db);

if (isset($_GET['product_name']) && isset($_GET['company_name'])) {
    $stmt = $purchase->getUnitsByProduct($_GET['product_name'], $_GET['company_name']);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $units_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($units_arr, array(
                "unit" => $row['unit']
            ));
        }
        http_response_code(200);
        echo json_encode($units_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No units found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Missing required parameters."));
}