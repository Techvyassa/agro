<?php
/**
 * Direct API to join sales_orders and pickings tables
 * Optimized for the specific data structure with many-to-many relationship
 */

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get status filter if provided
$status = isset($_GET['status']) ? $_GET['status'] : null;

try {
    // Database connection using .env or default credentials
    include_once '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
    
    // Get database credentials from .env
    $db_host = $_ENV['DB_HOST'] ?? '192.250.231.31';
    $db_name = $_ENV['DB_DATABASE'] ?? 'vyassa44_agro';
    $db_user = $_ENV['DB_USERNAME'] ?? 'vyassa44_agro';
    $db_pass = $_ENV['DB_PASSWORD'] ?? 'RoyalK1234';
    
    // Create PDO connection
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    
    // Build query - first get unique SO numbers
    $soQuery = "SELECT DISTINCT so_no FROM sales_orders";
    $soStmt = $pdo->query($soQuery);
    $soNumbers = $soStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $result = [];
    
    // For each SO number, gather all related items and pickings
    foreach ($soNumbers as $soNumber) {
        // Get all items for this SO
        $itemsQuery = "SELECT * FROM sales_orders WHERE so_no = :so_no";
        $itemsStmt = $pdo->prepare($itemsQuery);
        $itemsStmt->execute(['so_no' => $soNumber]);
        $items = $itemsStmt->fetchAll();
        
        // Get all pickings for this SO, with optional status filter
        $pickingsQuery = "SELECT * FROM pickings WHERE so_no = :so_no";
        if ($status) {
            $pickingsQuery .= " AND status = :status";
        }
        
        $pickingsStmt = $pdo->prepare($pickingsQuery);
        $params = ['so_no' => $soNumber];
        if ($status) {
            $params['status'] = $status;
        }
        
        $pickingsStmt->execute($params);
        $pickings = $pickingsStmt->fetchAll();
        
        // Only include in results if we have pickings (after filtering by status)
        if (!empty($pickings)) {
            $result[] = [
                'so_no' => $soNumber,
                'items' => $items,
                'pickings' => $pickings
            ];
        }
    }
    
    // Return the properly structured data
    echo json_encode([
        'success' => true,
        'filtered_by' => $status ? "status=$status" : null,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    // Return error details
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
