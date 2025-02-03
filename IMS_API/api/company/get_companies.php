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
        http_response_code(200);
        echo json_encode($companies_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No companies found."));
    }
} catch(PDOException $e) {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to get companies."));
}