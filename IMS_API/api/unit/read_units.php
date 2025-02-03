<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/unit.php';

$database = new Database();
$db = $database->getConnection();
$unit = new Unit($db);

try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Get total records count first
    $count_query = "SELECT COUNT(*) as total FROM units";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute();
    $total_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = (int)$total_row['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Get paginated data
    $query = "SELECT * FROM units ORDER BY id LIMIT :offset, :records_per_page";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    $num = $stmt->rowCount();

    if ($num > 0) {
        $units_arr = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unit_item = array(
                "id" => $row['id'],
                "unit" => $row['unit']
            );
            array_push($units_arr, $unit_item);
        }

        // Success response with data and pagination
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "data" => [
                "records" => $units_arr,
                "pagination" => [
                    "current_page" => $page,
                    "total_pages" => $total_pages,
                    "records_per_page" => $records_per_page,
                    "total_records" => $total_records
                ]
            ]
        ]);
    } else {
        // No records found - still return 200 with empty data
        $status_code = 200;
        http_response_code($status_code);
        echo json_encode([
            "status" => $status_code,
            "success" => true,
            "data" => [
                "records" => [],
                "pagination" => [
                    "current_page" => $page,
                    "total_pages" => 0,
                    "records_per_page" => $records_per_page,
                    "total_records" => 0
                ]
            ],
            "message" => "No units found."
        ]);
    }
} catch(PDOException $e) {
    // Database error
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Database Error: " . $e->getMessage()
    ]);
} catch(Exception $e) {
    // General error
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}