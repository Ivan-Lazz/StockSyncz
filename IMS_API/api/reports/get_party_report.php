<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // If no party_name parameter, return list of parties
    if (!isset($_GET['party_name'])) {
        $query = "SELECT * FROM party_info ORDER BY businessname";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $parties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $parties
        ]);
        exit;
    }

    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $records_per_page;

    $party_name = $_GET['party_name'];

    // Count total records
    $count_query = "SELECT COUNT(*) as total FROM purchase_master WHERE party_name = :party_name";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bindParam(":party_name", $party_name);
    $count_stmt->execute();
    $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $records_per_page);

    // Get paginated purchase data
    $query = "SELECT 
                pm.*,
                (pm.price * pm.quantity) as total 
            FROM purchase_master pm 
            WHERE pm.party_name = :party_name 
            ORDER BY pm.purchase_date DESC 
            LIMIT :offset, :limit";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":party_name", $party_name);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindParam(":limit", $records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total amount for all purchases
    $total_query = "SELECT SUM(price * quantity) as total_amount 
                FROM purchase_master 
                WHERE party_name = :party_name";
    $total_stmt = $db->prepare($total_query);
    $total_stmt->bindParam(":party_name", $party_name);
    $total_stmt->execute();
    $total_amount = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_amount'];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $purchases,
        'pagination' => [
            'page' => $page,
            'total_pages' => $total_pages,
            'records_per_page' => $records_per_page,
            'total_records' => $total_rows
        ],
        'summary' => [
            'total_amount' => $total_amount
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Error fetching report: " . $e->getMessage()
    ]);
}