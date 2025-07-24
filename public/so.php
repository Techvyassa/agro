<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(30); // Prevent timeout

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get status, page, and so_no parameters
$status = isset($_GET['status']) ? $_GET['status'] : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$so_no = isset($_GET['so_no']) ? $_GET['so_no'] : null;
$perPage = 20;

// Database connection
try {
    $host = '192.250.231.31';
    $dbname = 'vyassa44_agro';
    $user = 'vyassa44_agro';
    $pass = 'RoyalK1234';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build base query for sales_orders with optional so_no filter
    $params = [];
    $where = [];
    if ($so_no) {
        $baseQuery = "SELECT id, so_no, created_at, updated_at FROM sales_orders WHERE so_no LIKE ? ORDER BY so_no DESC";
        $params[] = "%$so_no%";
        $soStmt = $pdo->prepare($baseQuery);
        $soStmt->execute($params);
        $soList = $soStmt->fetchAll(PDO::FETCH_ASSOC);
        $total = count($soList);
        $totalPages = 1;
        $page = 1;
    } else {
        // Use aggregation to get one row per so_no
        $countQuery = "SELECT COUNT(DISTINCT so_no) FROM sales_orders";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $soQuery = "SELECT MIN(id) as id, so_no, MIN(created_at) as created_at, MIN(updated_at) as updated_at FROM sales_orders GROUP BY so_no ORDER BY so_no DESC LIMIT $perPage OFFSET $offset";
        $soStmt = $pdo->prepare($soQuery);
        $soStmt->execute();
        $soList = $soStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $result = [];
    foreach ($soList as $row) {
        // Fetch all items for this so_no
        $itemsStmt = $pdo->prepare("SELECT * FROM sales_orders WHERE so_no = ?");
        $itemsStmt->execute([$row['so_no']]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        $row['items'] = $items;
        $result[] = $row;
    }
    
    // Return the result
    $response = [
        'success' => true,
        'filtered_by' => $status ? "status=$status" : 'none',
        'so_no_filter' => $so_no ?? null,
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'count' => count($result),
        'data' => $result
    ];
    echo json_encode($response);
    
} catch (PDOException $e) {
    // Return error details
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
?>
