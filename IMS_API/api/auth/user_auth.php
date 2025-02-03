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

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->username) && !empty($data->password)) {
    $user->username = htmlspecialchars(strip_tags($data->username));
    $password = htmlspecialchars(strip_tags($data->password));
    
    // Query to check user
    $query = "SELECT * FROM user_registration WHERE username = :username AND role = 'user' AND status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $user->username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $row['password'])) {
            // Generate session token
            session_start();
            $_SESSION['user'] = $user->username;
            
            // Return success response with user data
            $status_code = 200;
            http_response_code($status_code);
            echo json_encode(array(
                "status" => $status_code,
                "message" => "Login successful",
                "user" => array(
                    "username" => $row['username'],
                    "firstname" => $row['firstname'],
                    "lastname" => $row['lastname'],
                    "role" => $row['role']
                )
            ));
        } else {
            $status_code = 401;
            http_response_code($status_code);
            echo json_encode(array(
                "status" => $status_code,
                "message" => "Invalid credentials"
            ));
        }
    } else {
        $status_code = 401;
        http_response_code($status_code);
        echo json_encode(array(
            "status" => $status_code,
            "message" => "Invalid credentials"
        ));
    }
} else {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode(array(
        "status" => $status_code,
        "message" => "Unable to login. Data is incomplete."
    ));
}