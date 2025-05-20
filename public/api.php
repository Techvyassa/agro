<?php
/**
 * Simple API router for short URLs
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Get request path and parameters
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));
$endpoint = end($pathParts);

// Get query parameters
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Route requests
switch ($endpoint) {
    case 'api.php':
    case 'api':
        // Default handler shows available endpoints
        echo json_encode([
            'success' => true,
            'message' => 'API is working',
            'endpoints' => [
                'so' => 'Get sales orders with pickings',
                'so?status=completed' => 'Get sales orders with completed pickings'
            ]
        ]);
        break;
        
    case 'so':
        // Get sales orders with pickings
        try {
            // Database connection
            $host = '192.250.231.31';
            $dbname = 'vyassa44_agro';
            $user = 'vyassa44_agro';
            $pass = 'RoyalK1234';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get all distinct SO numbers
            $soQuery = "SELECT DISTINCT so_no FROM sales_orders";
            $soStmt = $pdo->query($soQuery);
            $soNumbers = $soStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $result = [];
            
            // For each SO number, get items and pickings
            foreach ($soNumbers as $soNumber) {
                // Get items for this SO
                $itemsStmt = $pdo->prepare("SELECT * FROM sales_orders WHERE so_no = ?");
                $itemsStmt->execute([$soNumber]);
                $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Prepare pickings query with optional status filter
                if ($status) {
                    $pickingsStmt = $pdo->prepare("SELECT * FROM pickings WHERE so_no = ? AND status = ?");
                    $pickingsStmt->execute([$soNumber, $status]);
                } else {
                    $pickingsStmt = $pdo->prepare("SELECT * FROM pickings WHERE so_no = ?");
                    $pickingsStmt->execute([$soNumber]);
                }
                
                $pickings = $pickingsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Only include if pickings exist (after filtering)
                if (!empty($pickings)) {
                    $result[] = [
                        'so_no' => $soNumber,
                        'items' => $items,
                        'pickings' => $pickings
                    ];
                }
            }
            
            // Return the result
            echo json_encode([
                'success' => true,
                'filtered_by' => $status ? "status=$status" : 'none',
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            // Return error details
            echo json_encode([
                'success' => false,
                'message' => 'Database error',
                'error' => $e->getMessage()
            ]);
        }
        break;
        
    default:
        // Unknown endpoint
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Unknown endpoint: ' . $endpoint
        ]);
        break;
}
?>
