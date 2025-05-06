<?php
/**
 * Direct handler for GET requests to create pickings
 * This is a standalone file that bypasses the Laravel routing system
 */

// Initialize Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Set JSON content type for response
header('Content-Type: application/json');

// Only handle GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Only GET method is supported for this endpoint'
    ]);
    exit;
}

try {
    // Get parameters from query string
    $params = [
        'box' => $_GET['box'] ?? null,
        'so_no' => $_GET['so_no'] ?? null,
        'items' => isset($_GET['items']) ? (is_array($_GET['items']) ? $_GET['items'] : [$_GET['items']]) : [],
        'dimension' => $_GET['dimension'] ?? null,
        'weight' => $_GET['weight'] ?? null,
    ];
    
    // Validate input
    $errors = [];
    if (empty($params['box'])) {
        $errors[] = 'Box is required';
    }
    if (empty($params['so_no'])) {
        $errors[] = 'Sales order number is required';
    }
    if (empty($params['items'])) {
        $errors[] = 'Items are required';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Create picking in database using the Picking model
    $picking = new App\Models\Picking();
    $picking->box = $params['box'];
    $picking->so_no = $params['so_no'];
    $picking->items = $params['items'];
    $picking->dimension = $params['dimension'];
    $picking->weight = $params['weight'];
    $picking->save();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Picking created successfully',
        'data' => $picking
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create picking',
        'error' => $e->getMessage()
    ]);
}
