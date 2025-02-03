<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/user.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->firstname) && !empty($data->lastname) && 
    !empty($data->username) && !empty($data->password)) {
    
    $user->firstname = $data->firstname;
    $user->lastname = $data->lastname;
    $user->username = $data->username;
    $user->password = $data->password;
    $user->role = $data->role ?? 'user';
    $user->status = $data->status ?? 'active';

    if ($user->create()) {
        $status_code = 201;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "User was created."
        ));
    } else {
        $status_code = 503;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Unable to create user."
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to create user. Data is incomplete."
    ));
}

