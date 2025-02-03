<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->id) || !isset($data->product_selling_price)) {
        $status_code = 400;
        http_response_code($status_code);
        throw new Exception("Required data missing");
    }

    $query = "UPDATE stock_master 
            SET product_selling_price = :price 
            WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":price", $data->product_selling_price);
    $stmt->bindParam(":id", $data->id);

    if ($stmt->execute()) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            'status' => $status_code,
            'success' => true,
            'message' => "Stock price updated successfully"
        ]);
    } else {
        throw new Exception("Failed to update stock price");
    }

} catch (PDOException $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => false,
        'message' => "Database error occurred",
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    $status_code = isset($status_code) ? $status_code : 400;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>