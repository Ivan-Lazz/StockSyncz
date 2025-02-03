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
    $user->id = $data->id;
    $user->firstname = $data->firstname;
    $user->lastname = $data->lastname;
    $user->role = $data->role;
    $user->status = $data->status;
    
    if (!empty($data->password)) {
        $user->password = $data->password;
    }

    if ($user->update()) {
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "User was updated."
        ));
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Unable to update user."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to update user. Missing user ID."
    ));
}
?>