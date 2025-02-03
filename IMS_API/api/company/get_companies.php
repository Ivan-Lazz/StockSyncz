<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';
include_once '../../models/product.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

try {
    $stmt = $product->getCompanies();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $companies_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($companies_arr, array(
                "id" => $row['id'],
                "companyname" => $row['companyname']
            ));
        }
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "data" => $companies_arr
        ));
    } else {
        $status_code = 404;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "No companies found."
        ));
    }
} catch(PDOException $e) {
    $status_code = 503;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to get companies."
    ));
}