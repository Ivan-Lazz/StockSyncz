<?php

session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/purchase.php';

try {
    // Initialize database
    $database = new Database();
    $db = $database->getConnection();
    $purchase = new Purchase($db);

    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Debug log
    error_log("Received data: " . print_r($data, true));

    // Validate data
    if (!$data) {
        throw new Exception("No data received");
    }

    // Check required fields
    $required_fields = [
        'company_name' => 'Company Name',
        'product_name' => 'Product Name',
        'unit' => 'Unit',
        'packing_size' => 'Packing Size',
        'qty' => 'Quantity',
        'price' => 'Price',
        'party_name' => 'Party Name',
        'purchase_type' => 'Purchase Type',
        'expiry_date' => 'Expiry Date'
    ];

    foreach ($required_fields as $field => $label) {
        if (!isset($data->$field) || empty($data->$field)) {
            throw new Exception("$label is required");
        }
    }

    if(isset($_SESSION['admin'])){
        $bought_by = $_SESSION['admin'];
    } elseif (isset($_SESSION['user'])){
        $bought_by = $_SESSION['user'];
    } else {
        $bought_by = '';
    }
    // Set purchase data
    $purchase->company_name = strip_tags($data->company_name);
    $purchase->product_name = strip_tags($data->product_name);
    $purchase->unit = strip_tags($data->unit);
    $purchase->packing_size = strip_tags($data->packing_size);
    $purchase->qty = floatval($data->qty);
    $purchase->price = floatval($data->price);
    $purchase->party_name = strip_tags($data->party_name);
    $purchase->purchase_type = strip_tags($data->purchase_type);
    $purchase->expiry_date = strip_tags($data->expiry_date);
    $purchase->purchase_date = date("Y-m-d");
    $purchase->purchased_by = $bought_by;

    if ($purchase->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Purchase was created."));
    } else {
        throw new Exception("Unable to create purchase");
    }

} catch (Exception $e) {
    error_log("Purchase creation error: " . $e->getMessage());
    http_response_code(503);
    echo json_encode(array(
        "message" => "Unable to create purchase.",
        "error" => $e->getMessage(),
        "details" => "Please try again or contact support if the issue persists."
    ));
}
?>