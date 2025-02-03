<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/database.php';
include_once '../../models/product.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $product = new Product($db);

    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM products";
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute();
    $total_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = (int)$total_row['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Get paginated data
    $query = "SELECT * FROM products ORDER BY id LIMIT :offset, :records_per_page";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    $products_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $product_item = array(
            "id" => $row['id'],
            "company_name" => $row['company_name'],
            "product_name" => $row['product_name'],
            "unit" => $row['unit'],
            "packing_size" => $row['packing_size']
        );
        array_push($products_arr, $product_item);
    }

    $status_code = 200;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => true,
        "data" => [
            "records" => $products_arr,
            "pagination" => [
                "current_page" => $page,
                "total_pages" => $total_pages,
                "records_per_page" => $records_per_page,
                "total_records" => $total_records
            ]
        ]
    ]);

} catch(PDOException $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Database Error: " . $e->getMessage()
    ]);
} catch(Exception $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        "status" => $status_code,
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}