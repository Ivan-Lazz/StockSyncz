<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->id) || !isset($data->product_selling_price)) {
        throw new Exception("Required data missing");
    }

    $query = "UPDATE stock_master 
            SET product_selling_price = :price 
            WHERE id = :id";

    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":price", $data->product_selling_price);
    $stmt->bindParam(":id", $data->id);

    if ($stmt->execute()) {
        echo json_encode(array(
            "success" => true,
            "message" => "Stock price updated successfully"
        ));
    } else {
        throw new Exception("Failed to update stock price");
    }

} catch (Exception $e) {
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}
?>