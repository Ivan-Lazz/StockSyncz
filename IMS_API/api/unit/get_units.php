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
        http_response_code(200);
        echo json_encode($units_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No units found."));
    }
} catch(PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to get units."));
}