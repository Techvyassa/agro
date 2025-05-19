<?php
/**
 * Sales Pickings API
 * 
 * Direct database connection to join sales_orders and pickings tables
 * without relying on Laravel routing
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Allow requests from any origin (adjust as needed for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load Laravel's autoloader to access Laravel's functionality
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel's minimal environment for database connections
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    // Get status parameter if provided
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    // Build query using Laravel's DB facade
    $query = Illuminate\Support\Facades\DB::table('sales_orders')
        ->select(
            'sales_orders.*',
            'pickings.status as picking_status',
            'pickings.box',
            'pickings.dimension',
            'pickings.weight'
        )
        ->leftJoin('pickings', 'sales_orders.so_no', '=', 'pickings.so_no');
    
    // Filter by status if provided
    if (!empty($status)) {
        $query->where('pickings.status', $status);
    }
    
    // Execute the query
    $salesOrdersWithPickings = $query->get();
    
    // Return the response
    echo json_encode([
        'success' => true,
        'data' => $salesOrdersWithPickings
    ]);
    
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch sales orders with pickings data',
        'error' => $e->getMessage()
    ]);
}
?>
