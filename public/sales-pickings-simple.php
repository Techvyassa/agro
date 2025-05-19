<?php
/**
 * Simple Sales Pickings API without Laravel dependencies
 */

// Set strict error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get database credentials from Laravel .env file
function getEnvValue($key) {
    $path = __DIR__ . '/../.env';
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) continue;
            
            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($envKey, $envValue) = explode('=', $line, 2);
                if (trim($envKey) === $key) {
                    return trim($envValue);
                }
            }
        }
    }
    return null;
}

try {
    // Get database credentials from .env
    $dbHost = getEnvValue('DB_HOST') ?: '192.250.231.31';
    $dbName = getEnvValue('DB_DATABASE') ?: 'vyassa44_agro';
    $dbUser = getEnvValue('DB_USERNAME') ?: 'vyassa44_agro';
    $dbPass = getEnvValue('DB_PASSWORD') ?: 'RoyalK1234';
    
    // Connect to database
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", 
        $dbUser, 
        $dbPass, 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Get status filter if provided
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    // Build SQL query with join
    $sql = "SELECT 
                sales_orders.*, 
                pickings.status as picking_status,
                pickings.box,
                pickings.dimension,
                pickings.weight
            FROM sales_orders
            LEFT JOIN pickings ON sales_orders.so_no = pickings.so_no";
    
    // Add WHERE clause if status filter is provided
    $params = [];
    if (!empty($status)) {
        $sql .= " WHERE pickings.status = :status";
        $params[':status'] = $status;
    }
    
    // Prepare and execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Fetch all matching records
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $results,
        'count' => count($results),
        'filtered_by' => $status ? "status=$status" : 'none'
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
