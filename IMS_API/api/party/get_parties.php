<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/purchase.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $purchase = new Purchase($db);

    $stmt = $purchase->getParties();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $parties_arr = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($parties_arr, array(
                "businessname" => $row['businessname']
            ));
        }
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "data" => $parties_arr
        ));
    } else {
        $status_code = 404;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "No parties found."
        ));
    }
} catch(PDOException $e) {
    $status_code = 503;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to get parties."
    ));
}
?>