<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(30); // Prevent timeout

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get status, page parameters
$status = isset($_GET['status']) ? $_GET['status'] : null;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 20;

// Database connection
try {
    $host = '192.250.231.31';
    $dbname = 'vyassa44_agro';
    $user = 'vyassa44_agro';
    $pass = 'RoyalK1234';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build base query for sales_orders
    $baseQuery = "SELECT id, so_no, created_at, updated_at FROM sales_orders";
    $params = [];
    // If status is provided, filter SOs that have at least one picking with that status
    if ($status) {
        $baseQuery .= " WHERE so_no IN (SELECT so_no FROM pickings WHERE status = ?)";
        $params[] = $status;
    }
    $baseQuery .= " GROUP BY so_no ORDER BY so_no DESC";

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT so_no) FROM sales_orders";
    $countParams = [];
    if ($status) {
        $countQuery .= " WHERE so_no IN (SELECT so_no FROM pickings WHERE status = ?)";
        $countParams[] = $status;
    }
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;

    // Get paginated SOs
    $soQuery = $baseQuery . " LIMIT $perPage OFFSET $offset";
    $soStmt = $pdo->prepare($soQuery);
    $soStmt->execute($params);
    $soList = $soStmt->fetchAll(PDO::FETCH_ASSOC);

    // Return only the required fields
    $result = $soList;
    
    // Return the result
    echo json_encode([
        'success' => true,
        'filtered_by' => $status ? "status=$status" : 'none',
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
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
