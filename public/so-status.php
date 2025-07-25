<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get status and page parameters
$status = isset($_GET['status']) ? $_GET['status'] : null;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 20;
// Add support for optional so_no parameter
$so_no = isset($_GET['so_no']) ? $_GET['so_no'] : null;

// Database connection - using your parameters directly for simplicity
$host = '192.250.231.31';
$dbname = 'vyassa44_agro';
$user = 'vyassa44_agro';
$pass = 'RoyalK1234';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total SO count for pagination
    $countQuery = "SELECT COUNT(DISTINCT so_no) FROM sales_orders";
    $countStmt = $pdo->query($countQuery);
    $totalSoCount = (int)$countStmt->fetchColumn();
    $totalPages = (int)ceil($totalSoCount / $perPage);
    $offset = ($page - 1) * $perPage;

    // Get paginated SO numbers
    $soQuery = "SELECT DISTINCT so_no FROM sales_orders LIMIT :limit OFFSET :offset";
    $soStmt = $pdo->prepare($soQuery);
    $soStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $soStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $soStmt->execute();
    $soNumbers = $soStmt->fetchAll(PDO::FETCH_COLUMN);
    
    $result = [];
    
    if ($so_no) {
        // If so_no is provided, fetch only that SO (ignore pagination)
        // Get items for this SO
        $itemsStmt = $pdo->prepare("SELECT * FROM sales_orders WHERE so_no = ?");
        $itemsStmt->execute([$so_no]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        // Prepare pickings query with optional status filter
        if ($status) {
            $pickingsStmt = $pdo->prepare("SELECT * FROM pickings WHERE so_no = ? AND status = ?");
            $pickingsStmt->execute([$so_no, $status]);
        } else {
            $pickingsStmt = $pdo->prepare("SELECT * FROM pickings WHERE so_no = ?");
            $pickingsStmt->execute([$so_no]);
        }
        $pickings = $pickingsStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($pickings)) {
            $result[] = [
                'so_no' => $so_no,
                'items' => $items,
                'pickings' => $pickings
            ];
        }
        // Return the result (no pagination info needed)
        echo json_encode([
            'success' => true,
            'filtered_by' => ($status ? "status=$status" : 'none') . ", so_no=$so_no",
            'data' => $result
        ]);
        exit;
    }

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
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'total_so_count' => $totalSoCount
        ],
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
