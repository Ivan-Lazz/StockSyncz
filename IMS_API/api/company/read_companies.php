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
    // Initialize database and company object
    $database = new Database();
    $db = $database->getConnection();
    $company = new Company($db);

    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $records_per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
    $offset = ($page - 1) * $records_per_page;

    // Ensure valid pagination parameters
    if ($page < 1) $page = 1;
    if ($records_per_page < 1) $records_per_page = 10;

    // Get all records first using the model's read method
    $stmt = $company->read();
    $total_records = $stmt->rowCount();
    $total_pages = ceil($total_records / $records_per_page);

    // Adjust page if it exceeds total pages
    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
        $offset = ($page - 1) * $records_per_page;
    }

    // Prepare pagination query
    $query = "SELECT * FROM company_name ORDER BY id LIMIT :offset, :limit";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch the records
    $companies = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $companies[] = [
            'id' => $row['id'],
            'companyname' => $row['companyname']
        ];
    }

    // Check if any companies were found
    if (count($companies) > 0) {
        // Prepare successful response
        $response = [
            'success' => true,
            'records' => $companies,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'records_per_page' => $records_per_page,
                'total_records' => $total_records
            ]
        ];

        http_response_code(200);
    } else {
        // No companies found
        $response = [
            'success' => true,
            'records' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 0,
                'records_per_page' => $records_per_page,
                'total_records' => 0
            ],
            'message' => 'No companies found.'
        ];

        http_response_code(200); // Still return 200 as this is not an error
    }

    echo json_encode($response);

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