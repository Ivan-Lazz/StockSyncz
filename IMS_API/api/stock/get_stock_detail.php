<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check if ID exists
    if (!isset($_GET['id'])) {
        throw new Exception("Stock ID is required");
    }

    $id = $_GET['id'];
    
    // Debug log
    error_log("Fetching stock details for ID: " . $id);

    $query = "SELECT * FROM stock_master WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);

    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        throw new Exception("Stock not found");
    }

    // Debug log
    error_log("Stock data found: " . print_r($stock, true));

    http_response_code(200);
    echo json_encode([
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

} catch (Exception $e) {
    error_log("Error in get_stock_detail: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>