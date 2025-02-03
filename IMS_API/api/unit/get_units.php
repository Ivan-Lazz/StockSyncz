<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

try {
    $stmt = $product->getUnits();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $units_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($units_arr, array(
                "id" => $row['id'],
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
} catch(PDOException $e) {
    $status_code = 503;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to get units."
    ]);
}