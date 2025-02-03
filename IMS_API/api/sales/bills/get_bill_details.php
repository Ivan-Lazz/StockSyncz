<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!isset($_GET['id'])) {
        throw new Exception("Bill ID is required");
    }

    $bill_id = $_GET['id'];

    // Get bill header with exact column names
    $header_query = "SELECT id, bill_no, full_name, bill_type, date, username 
                    FROM billing_header WHERE id = :id";
    $header_stmt = $db->prepare($header_query);
    $header_stmt->bindParam(":id", $bill_id);
    $header_stmt->execute();
    $header = $header_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$header) {
        throw new Exception("Bill not found");
    }

    // Get bill details with exact column names
    $details_query = "SELECT id, product_company, product_name, product_unit, 
                    packaging_size, price, qty 
                    FROM billing_details WHERE bill_id = :bill_id";
    $details_stmt = $db->prepare($details_query);
    $details_stmt->bindParam(":bill_id", $bill_id);
    $details_stmt->execute();
    $details = $details_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = 0;
    foreach ($details as $detail) {
        $total += (floatval($detail['price']) * floatval($detail['qty']));
    }

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "data" => [
            "header" => $header,
            "details" => $details,
            "total" => $total
        ]
    ]);

} catch (Exception $e) {
    $status_code = 503;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to fetch bill details.",
        "error" => $e->getMessage()
    ]);
}
?>