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

    // Search parameter
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Count total records
    $count_query = "SELECT COUNT(*) as total FROM stock_master";
    if ($search) {
        $search_term = "%{$search}%";
        $count_query .= " WHERE product_company LIKE :search 
                        OR product_name LIKE :search 
                        OR product_unit LIKE :search";
    }
    
    $count_stmt = $db->prepare($count_query);
    if ($search) {
        $count_stmt->bindParam(":search", $search_term);
    }
    $count_stmt->execute();
    $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $records_per_page);

    // Get paginated data
    $query = "SELECT id, product_company, product_name, product_unit, 
            packing_size, product_qty, product_selling_price 
            FROM stock_master";
    
    if ($search) {
        $query .= " WHERE product_company LIKE :search 
                OR product_name LIKE :search 
                OR product_unit LIKE :search";
    }
    
    $query .= " ORDER BY id ASC LIMIT :offset, :limit";

    $stmt = $db->prepare($query);
    if ($search) {
        $stmt->bindParam(":search", $search_term);
    }
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindParam(":limit", $records_per_page, PDO::PARAM_INT);
    
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format stock data
    $formatted_stocks = array_map(function($stock) {
        return [
            'id' => $stock['id'],
            'product_company' => htmlspecialchars($stock['product_company']),
            'product_name' => htmlspecialchars($stock['product_name']),
            'product_unit' => htmlspecialchars($stock['product_unit']),
            'packing_size' => htmlspecialchars($stock['packing_size']),
            'product_qty' => (float)$stock['product_qty'],
            'product_selling_price' => number_format((float)$stock['product_selling_price'], 2)
        ];
    }, $stocks);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $formatted_stocks,
        'pagination' => [
            'page' => $page,
            'total_pages' => $total_pages,
            'records_per_page' => $records_per_page,
            'total_records' => $total_rows
        ]
    ]);

} catch (Exception $e) {
    error_log('Stock Master Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching stock data',
        'error' => $e->getMessage()
    ]);
}