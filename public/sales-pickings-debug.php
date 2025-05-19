<?php
/**
 * Sales Pickings API with Laravel DB integration and debugging
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set maximum execution time to prevent timeouts
ini_set('max_execution_time', 30);

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Load Laravel environment
require __DIR__ . '/../vendor/autoload.php';

try {
    // Initialize Laravel app
    echo json_encode(['debug' => 'Starting Laravel bootstrap...']);
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    // Get status parameter
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    
    // Basic validation
    if (!empty($status)) {
        if (!in_array($status, ['pending', 'complete', 'processing', 'cancelled', 'shipped'])) {
            throw new Exception("Invalid status value: {$status}");
        }
    }
    
    // Execute the query with error handling
    try {
        // Use Laravel's DB facade for the query
        $query = Illuminate\Support\Facades\DB::table('sales_orders')
            ->select(
                'sales_orders.*',
                'pickings.status as picking_status',
                'pickings.box',
                'pickings.dimension',
                'pickings.weight'
            )
            ->leftJoin('pickings', 'sales_orders.so_no', '=', 'pickings.so_no');
        
        // Apply status filter if provided
        if (!empty($status)) {
            $query->where('pickings.status', $status);
        }
        
        // Limit results and add timeout protection
        $query->limit(100); // Prevent excessive data retrieval
        $results = $query->get();
        
        // Return the data
        echo json_encode([
            'success' => true,
            'count' => count($results),
            'filtered_by' => $status ? "status=$status" : 'none',
            'data' => $results
        ]);
    } catch (Exception $dbException) {
        // Database error
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error',
            'error' => $dbException->getMessage(),
            'trace' => $dbException->getTraceAsString()
        ]);
    }
    
    // Terminate Laravel application
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    // General error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Application error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
