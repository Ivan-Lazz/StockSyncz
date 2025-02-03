<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../../config/database.php';
include_once '../../../models/sales.php';

try {
    // Get raw input and decode
    $input = file_get_contents("php://input");
    if (!$input) {
        throw new Exception("No input data received");
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format: " . json_last_error_msg());
    }

    // Validate session
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        throw new Exception("No items in cart");
    }

    if (empty($_SESSION['cart'])) {
        throw new Exception("Cart is empty");
    }

    // Validate required fields
    $required_fields = ['full_name', 'bill_type', 'bill_no', 'username'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            throw new Exception("Missing required field: {$field}");
        }
    }

    $database = new Database();
    $db = $database->getConnection();
    $sales = new Sales($db);

    // Prepare header data
    $header_data = array(
        'full_name' => htmlspecialchars(strip_tags(trim($data['full_name']))),
        'bill_type' => htmlspecialchars(strip_tags(trim($data['bill_type']))),
        'bill_no' => htmlspecialchars(strip_tags(trim($data['bill_no']))),
        'username' => htmlspecialchars(strip_tags(trim($data['username'])))
    );

    // Create bill
    $bill_id = $sales->createBill($header_data, $_SESSION['cart']);

    if (!$bill_id) {
        throw new Exception("Failed to create bill");
    }

    // Clear cart after successful creation
    unset($_SESSION['cart']);
    session_write_close();

    echo json_encode(array(
        "success" => true,
        "message" => "Bill generated successfully",
        "bill_id" => $bill_id
    ));

} catch (Exception $e) {
    error_log('Bill creation error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}