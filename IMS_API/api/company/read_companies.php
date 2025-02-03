<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include required files
include_once '../../config/database.php';
include_once '../../models/company.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $database = new Database();
    $db = $database->getConnection();
    $company = new Company($db);

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    if ($page < 1) $page = 1;
    if ($records_per_page < 1) $records_per_page = 10;

    $stmt = $company->read();
    $total_records = $stmt->rowCount();
    $total_pages = ceil($total_records / $records_per_page);

    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $records_per_page;
    }

    $query = "SELECT * FROM company_name ORDER BY id LIMIT :offset, :limit";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    $companies = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $companies[] = [
            'id' => $row['id'],
            'companyname' => $row['companyname']
        ];
    }

    $status_code = 200;
    http_response_code($status_code);

    if (count($companies) > 0) {
        echo json_encode([
            'status' => $status_code,
            'success' => true,
            'records' => $companies,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'records_per_page' => $records_per_page,
                'total_records' => $total_records
            ]
        ]);
    } else {
        echo json_encode([
            'status' => $status_code,
            'success' => true,
            'records' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 0,
                'records_per_page' => $records_per_page,
                'total_records' => 0
            ],
            'message' => 'No companies found.'
        ]);
    }

} catch (PDOException $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => false,
        'message' => 'Database Error: ' . $e->getMessage(),
        'error_details' => [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} catch (Exception $e) {
    $status_code = 500;
    http_response_code($status_code);
    echo json_encode([
        'status' => $status_code,
        'success' => false,
        'message' => 'General Error: ' . $e->getMessage(),
        'error_details' => [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}