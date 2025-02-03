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
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "data" => $units_arr
        ]);
    } else {
        $status_code = 404;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => false,
            "message" => "No units found."
        ]);
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Missing required parameters."
    ]);
}
