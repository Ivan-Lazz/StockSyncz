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

    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM user_registration";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute();
    $total_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = (int)$total_row['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Get paginated data
    $query = "SELECT * FROM user_registration ORDER BY id LIMIT :offset, :records_per_page";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    $users_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_item = array(
            "id" => $row['id'],
            "firstname" => $row['firstname'],
            "lastname" => $row['lastname'],
            "username" => $row['username'],
            "role" => $row['role'],
            "status" => $row['status']
        );
        array_push($users_arr, $user_item);
    }

    // Prepare response
    $response = array(
        "success" => true,
        "records" => $users_arr,
        "pagination" => array(
            "current_page" => $page,
            "total_pages" => $total_pages,
            "records_per_page" => $records_per_page,
            "total_records" => $total_records
        )
    );

    http_response_code(200);
    echo json_encode($response);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Database Error: " . $e->getMessage()
    ));
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ));
}