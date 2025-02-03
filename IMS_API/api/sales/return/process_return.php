<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../config/database.php';

session_start();
if(!isset($_SESSION['admin'])) {
    $status_code = 401;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->id)) {
        $status_code = 400;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => false,
            "message" => "Missing billing detail ID"
        ]);
        exit;
    }

    // Start transaction
    $db->beginTransaction();

    // Get billing details
    $query = "SELECT bd.*, bh.bill_no, bh.id as bill_header_id 
            FROM billing_details bd 
            JOIN billing_header bh ON bd.bill_id = bh.id 
            WHERE bd.id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);
    $stmt->execute();
    
    $billing_detail = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$billing_detail) {
        throw new Exception("Billing detail not found");
    }

    $today_date = date('Y-m-d');

    // Insert into return_products
    $insert_query = "INSERT INTO return_products (
                        return_by, bill_no, return_date, 
                        product_company, product_name, product_unit, 
                        packing_size, product_price, product_qty, total
                    ) VALUES (
                        :return_by, :bill_no, :return_date,
                        :product_company, :product_name, :product_unit,
                        :packing_size, :price, :qty, :total
                    )";

    $total = $billing_detail['price'] * $billing_detail['qty'];
    
    $stmt = $db->prepare($insert_query);
    $stmt->bindParam(':return_by', $_SESSION['admin']);
    $stmt->bindParam(':bill_no', $billing_detail['bill_no']);
    $stmt->bindParam(':return_date', $today_date);
    $stmt->bindParam(':product_company', $billing_detail['product_company']);
    $stmt->bindParam(':product_name', $billing_detail['product_name']);
    $stmt->bindParam(':product_unit', $billing_detail['product_unit']);
    $stmt->bindParam(':packing_size', $billing_detail['packaging_size']);
    $stmt->bindParam(':price', $billing_detail['price']);
    $stmt->bindParam(':qty', $billing_detail['qty']);
    $stmt->bindParam(':total', $total);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert return record");
    }

    // Update stock
    $update_query = "UPDATE stock_master 
                    SET product_qty = product_qty + :qty 
                    WHERE product_company = :company 
                    AND product_name = :product 
                    AND product_unit = :unit 
                    AND packing_size = :packing_size";

    $stmt = $db->prepare($update_query);
    $stmt->bindParam(':qty', $billing_detail['qty']);
    $stmt->bindParam(':company', $billing_detail['product_company']);
    $stmt->bindParam(':product', $billing_detail['product_name']);
    $stmt->bindParam(':unit', $billing_detail['product_unit']);
    $stmt->bindParam(':packing_size', $billing_detail['packaging_size']);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update stock");
    }

    // Delete billing detail
    $delete_query = "DELETE FROM billing_details WHERE id = :id";
    $stmt = $db->prepare($delete_query);
    $stmt->bindParam(':id', $data->id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to delete billing detail");
    }

    // Commit transaction
    $db->commit();

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "message" => "Product return processed successfully",
        "data" => [
            "bill_id" => $billing_detail['bill_header_id']
        ]
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => $e->getMessage()
    ]);
}