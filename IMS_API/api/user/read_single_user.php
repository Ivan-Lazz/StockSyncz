<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/user.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    if (!isset($_GET['id'])) {
        throw new Exception("No ID provided");
    }

    $stmt = $user->readSingle($_GET['id']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $status_code = 200;
        http_response_code($status_code);
        
        // Prepare response data
        $response = array(
            "status_code" => $status_code,
            "success" => true,
            "data" => array(
                "id" => $row['id'],
                "firstname" => $row['firstname'],
                "lastname" => $row['lastname'],
                "username" => $row['username'],
                "role" => $row['role'],
                "status" => $row['status']  // This is the user's status from database
            )
        );
        
        echo json_encode($response);
    } else {
        $status_code = 404;
        http_response_code($status_code);
        echo json_encode(array(
            "status_code" => $status_code,
            "success" => false,
            "message" => "User not found"
        ));
    }

} catch (Exception $e) {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status_code" => $status_code,
        "success" => false,
        "message" => $e->getMessage()
    ));
}