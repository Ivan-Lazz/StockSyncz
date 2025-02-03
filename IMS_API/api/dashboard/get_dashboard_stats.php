<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    if(!isset($_SESSION['admin'])) {
        $status_code = 401;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "success" => false,
            "message" => "Unauthorized access"
        ));
        exit;
    }

    $database = new Database();
    $conn = $database->getConnection();

    // Get products count
    $products_query = "SELECT COUNT(*) as count FROM products";
    $products_stmt = $conn->prepare($products_query);
    $products_stmt->execute();
    $products_result = $products_stmt->fetch(PDO::FETCH_ASSOC);
    $products_count = $products_result['count'];

    // Get orders count
    $orders_query = "SELECT COUNT(*) as count FROM billing_header";
    $orders_stmt = $conn->prepare($orders_query);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->fetch(PDO::FETCH_ASSOC);
    $orders_count = $orders_result['count'];

    // Get companies count
    $companies_query = "SELECT COUNT(*) as count FROM company_name";
    $companies_stmt = $conn->prepare($companies_query);
    $companies_stmt->execute();
    $companies_result = $companies_stmt->fetch(PDO::FETCH_ASSOC);
    $companies_count = $companies_result['count'];

    // Return the data with success status code
    $status_code = 200;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "success" => true,
        "data" => array(
            "products_count" => $products_count,
            "orders_count" => $orders_count,
            "companies_count" => $companies_count
        )
    ));

} catch (PDOException $e) {
    // Database error
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ));
} catch (Exception $e) {
    // General error
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "success" => false,
        "message" => $e->getMessage()
    ));
}
?>