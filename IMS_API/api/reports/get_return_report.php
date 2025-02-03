<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Date filter parameters
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    // Count total records
    $count_query = "SELECT COUNT(*) as total FROM return_products";
    if ($start_date && $end_date) {
        $count_query .= " WHERE return_date BETWEEN :start_date AND :end_date";
    }
    
    $count_stmt = $db->prepare($count_query);
    if ($start_date && $end_date) {
        $count_stmt->bindParam(":start_date", $start_date);
        $count_stmt->bindParam(":end_date", $end_date);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Get paginated data
    $query = "SELECT * FROM return_products";
    if ($start_date && $end_date) {
        $query .= " WHERE return_date BETWEEN :start_date AND :end_date";
    }
    $query .= " ORDER BY return_date DESC LIMIT :offset, :records_per_page";

    $stmt = $db->prepare($query);
    if ($start_date && $end_date) {
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
    }
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate page info
    $page_info = [
        "current_page" => $page,
        "total_pages" => $total_pages,
        "records_per_page" => $records_per_page,
        "total_records" => $total_records
    ];

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "data" => $returns,
        "pagination" => $page_info
    ]);

} catch (PDOException $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Database error: Unable to fetch return report.",
        "error" => $e->getMessage()
    ]);
} catch (Exception $e) {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to fetch return report.",
        "error" => $e->getMessage()
    ]);
}