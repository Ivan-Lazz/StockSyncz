<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!isset($_GET['id'])) {
        $status_code = 400;
        http_response_code($status_code);
        throw new Exception("Stock ID is required");
    }

    $id = $_GET['id'];
    error_log("Fetching stock details for ID: " . $id);

    $query = "SELECT * FROM stock_master WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);

    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        $status_code = 404;
        http_response_code($status_code);
        throw new Exception("Stock not found");
    }

    error_log("Stock data found: " . print_r($stock, true));

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => true,
        'data' => [
            'product_company' => $stock['product_company'],
            'product_name' => $stock['product_name'],
            'product_unit' => $stock['product_unit'],
            'packing_size' => $stock['packing_size'],
            'product_qty' => $stock['product_qty'],
            'product_selling_price' => $stock['product_selling_price']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in get_stock_detail: " . $e->getMessage());
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => false,
        'message' => "Database error occurred",
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in get_stock_detail: " . $e->getMessage());
    $status_code = isset($status_code) ? $status_code : 400;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>