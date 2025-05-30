<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get status parameter
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Database connection - using your parameters directly for simplicity
$host = '192.250.231.31';
$dbname = 'vyassa44_agro';
$user = 'vyassa44_agro';
$pass = 'RoyalK1234';

try {
    // Connect to database
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
?>
