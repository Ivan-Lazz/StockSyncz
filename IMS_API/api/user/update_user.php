<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/user.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Create database connection
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Check if we have the minimum required data
if (!empty($data->id)) {
    // Set user properties from request data
    $user->id = $data->id;
    $user->firstname = $data->firstname;
    $user->lastname = $data->lastname;
    $user->role = $data->role;
    $user->status = $data->status;
    
    // Only set password if it's provided
    if (!empty($data->password)) {
        $user->password = $data->password;
    }

    // Update the user
    if ($user->update()) {
        http_response_code(200);
        echo json_encode(array("message" => "User was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update user."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update user. Missing user ID."));
}
?>