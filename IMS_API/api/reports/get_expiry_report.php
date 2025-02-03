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
    $records_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Date filters
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    // Base query for counting total records
    $count_query = "SELECT COUNT(*) as total FROM purchase_master";
    $where_clause = '';

    if ($start_date && $end_date) {
        $where_clause = " WHERE expiry_date BETWEEN :start_date AND :end_date";
        $count_query .= $where_clause;
    }

    $count_stmt = $db->prepare($count_query);
    if ($start_date && $end_date) {
        $count_stmt->bindParam(":start_date", $start_date);
        $count_stmt->bindParam(":end_date", $end_date);
    }
    $count_stmt->execute();
    $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $records_per_page);

    // Main query for fetching data
    $query = "SELECT *, 
              (price * quantity) as total_amount,
            CASE 
                WHEN expiry_date < CURDATE() THEN 'Expired'
                WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expiring Soon'
                ELSE 'Valid'
            END as expiry_status
            FROM purchase_master";

    if ($where_clause) {
        $query .= $where_clause;
    }
    
    $query .= " ORDER BY expiry_date ASC LIMIT :offset, :limit";

    $stmt = $db->prepare($query);
    if ($start_date && $end_date) {
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
    }
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindParam(":limit", $records_per_page, PDO::PARAM_INT);

    $stmt->execute();
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get summary statistics
    $summary_query = "SELECT 
                    COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) as expired_count,
                    COUNT(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as expiring_soon_count,
                    COUNT(CASE WHEN expiry_date > DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as valid_count
                    FROM purchase_master";
    
    if ($where_clause) {
        $summary_query .= $where_clause;
    }

    $summary_stmt = $db->prepare($summary_query);
    if ($start_date && $end_date) {
        $summary_stmt->bindParam(":start_date", $start_date);
        $summary_stmt->bindParam(":end_date", $end_date);
    }
    $summary_stmt->execute();
    $summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "data" => $purchases,
        "pagination" => [
            "page" => $page,
            "total_pages" => $total_pages,
            "records_per_page" => $records_per_page,
            "total_records" => $total_rows
        ],
        "summary" => $summary
    ]);

} catch (PDOException $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Database error: Unable to fetch expiry report.",
        "error" => $e->getMessage()
    ]);
} catch (Exception $e) {
    $status_code = 400;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Unable to fetch expiry report.",
        "error" => $e->getMessage()
    ]);
}