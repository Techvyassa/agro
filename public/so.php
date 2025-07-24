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
    $baseQuery = "SELECT id, so_no, created_at, updated_at FROM sales_orders";
    $params = [];
    $where = [];
    if ($so_no) {
        $where[] = "so_no LIKE ?";
        $params[] = "%$so_no%";
    }
    if (!empty($where)) {
        $baseQuery .= " WHERE " . implode(" AND ", $where);
    }
    $baseQuery .= " ORDER BY so_no DESC";

    // If searching by so_no, ignore pagination and return all matches
    if ($so_no) {
        $soStmt = $pdo->prepare($baseQuery);
        $soStmt->execute($params);
        $soList = $soStmt->fetchAll(PDO::FETCH_ASSOC);
        $total = count($soList);
        $totalPages = 1;
        $page = 1;
    } else {
        // Get total count for pagination
        $countQuery = "SELECT COUNT(DISTINCT so_no) FROM sales_orders";
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute();
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $soQuery = $baseQuery . " GROUP BY so_no LIMIT $perPage OFFSET $offset";
        $soStmt = $pdo->prepare($soQuery);
        $soStmt->execute($params);
        $soList = $soStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $result = $soList;
    
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
