<?php
/**
 * Standalone handler for updating pickings by so_no and box
 * Supports both GET and POST requests
 */

// Initialize Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Set JSON response header
header('Content-Type: application/json');

try {
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Get parameters based on request method
    if ($method === 'GET') {
        $box = $_GET['box'] ?? null;
        $so_no = $_GET['so_no'] ?? null;
        $dimension = $_GET['dimension'] ?? null;
        $weight = $_GET['weight'] ?? null;
        $items = isset($_GET['items']) ? (is_array($_GET['items']) ? $_GET['items'] : [$_GET['items']]) : null;
    } else {
        // For POST, get data from request body
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }
        
        $box = $data['box'] ?? null;
        $so_no = $data['so_no'] ?? null;
        $dimension = $data['dimension'] ?? null;
        $weight = $data['weight'] ?? null;
        $items = $data['items'] ?? null;
    }
    
    // Validate required parameters
    if (empty($box) || empty($so_no)) {
        echo json_encode([
            'success' => false,
            'message' => 'Both box and so_no parameters are required'
        ]);
        exit;
    }
    
    // Find existing picking
    $picking = App\Models\Picking::where('so_no', $so_no)
        ->where('box', $box)
        ->first();
    
    if (!$picking) {
        echo json_encode([
            'success' => false,
            'message' => 'Picking not found for the specified so_no and box'
        ]);
        exit;
    }
    
    // Update fields if provided
    $updated = false;
    
    if (!is_null($items)) {
        $picking->items = $items;
        $updated = true;
    }
    
    if (!is_null($dimension)) {
        $picking->dimension = $dimension;
        $updated = true;
    }
    
    if (!is_null($weight)) {
        $picking->weight = $weight;
        $updated = true;
    }
    
    if ($updated) {
        $picking->save();
        echo json_encode([
            'success' => true,
            'message' => 'Picking updated successfully',
            'data' => $picking
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No updates were provided',
            'data' => $picking
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update picking',
        'error' => $e->getMessage()
    ]);
}
