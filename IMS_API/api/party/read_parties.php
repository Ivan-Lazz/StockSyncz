<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/party.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $database = new Database();
    $db = $database->getConnection();

    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Get total count first
    $count_query = "SELECT COUNT(*) as total FROM party_info";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute();
    $total_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = (int)$total_row['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Get paginated data
    $query = "SELECT * FROM party_info ORDER BY id LIMIT :offset, :limit";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    $parties = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parties[] = [
            "id" => $row['id'],
            "firstname" => $row['firstname'],
            "lastname" => $row['lastname'],
            "businessname" => $row['businessname'],
            "contact" => $row['contact'],
            "address" => $row['address'],
            "city" => $row['city']
        ];
    }

    // Send response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'records' => $parties,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'records_per_page' => $records_per_page,
            'total_records' => $total_records
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage(),
        'error_details' => [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'General Error: ' . $e->getMessage(),
        'error_details' => [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}