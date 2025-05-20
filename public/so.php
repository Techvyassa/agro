<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(30); // Prevent timeout

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get status parameter
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Database connection
try {
    $host = '192.250.231.31';
    $dbname = 'vyassa44_agro';
    $user = 'vyassa44_agro';
    $pass = 'RoyalK1234';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all distinct SO numbers from sales_orders
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
        
        // Check if this SO exists in pickings table
        $pickingCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM pickings WHERE so_no = ?");
        $pickingCheckStmt->execute([$soNumber]);
        $pickingExists = $pickingCheckStmt->fetchColumn() > 0;
        
        // Prepare pickings query with optional status filter
        if ($status && $status !== 'pending') {
            $pickingsStmt = $pdo->prepare("SELECT * FROM pickings WHERE so_no = ? AND status = ?");
            $pickingsStmt->execute([$soNumber, $status]);
            $pickings = $pickingsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Only include SOs with matching pickings
            if (!empty($pickings)) {
                $result[] = [
                    'so_no' => $soNumber,
                    'items' => $items,
                    'pickings' => $pickings,
                    'picking_status' => $status
                ];
            }
        } 
        // Special case for 'pending' filter or no pickings
        else if (!$pickingExists || $status === 'pending') {
            // If SO doesn't exist in pickings table or we're filtering for pending
            if (!$pickingExists && ($status === 'pending' || !$status)) {
                $result[] = [
                    'so_no' => $soNumber,
                    'items' => $items,
                    'pickings' => [],
                    'picking_status' => 'pending'
                ];
            }
            // If no status filter, include all
            else if (!$status) {
                $pickingsStmt = $pdo->prepare("SELECT * FROM pickings WHERE so_no = ?");
                $pickingsStmt->execute([$soNumber]);
                $pickings = $pickingsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                $result[] = [
                    'so_no' => $soNumber,
                    'items' => $items,
                    'pickings' => $pickings,
                    'picking_status' => $pickingExists ? $pickings[0]['status'] : 'pending'
                ];
            }
        }
    }
    
    // Return the result
    echo json_encode([
        'success' => true,
        'filtered_by' => $status ? "status=$status" : 'none',
        'count' => count($result),
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
?>
