<?php
/**
 * Direct handler for Pickings API - supports both GET and POST methods
 * This bypasses Laravel's routing system entirely
 */

// Including Laravel bootstrap to access models and database
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Process request based on method
$method = $_SERVER['REQUEST_METHOD'];

// Common validation function
function validateInput($data) {
    $errors = [];
    
    if (empty($data['box'])) {
        $errors[] = 'Box is required';
    }
    
    if (empty($data['so_no'])) {
        $errors[] = 'Sales order number is required';
    }
    
    if (empty($data['items']) || !is_array($data['items'])) {
        $errors[] = 'Items must be a non-empty array';
    }
    
    return $errors;
}

// Set JSON content type for all responses
header('Content-Type: application/json');

try {
    // Handle GET request
    if ($method === 'GET') {
        // Extract parameters from query string
        $params = [
            'box' => $_GET['box'] ?? null,
            'so_no' => $_GET['so_no'] ?? null,
            'items' => isset($_GET['items']) ? (is_array($_GET['items']) ? $_GET['items'] : [$_GET['items']]) : [],
            'dimension' => $_GET['dimension'] ?? null,
            'weight' => $_GET['weight'] ?? null,
        ];
        
        // Validate input
        $errors = validateInput($params);
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $errors
            ]);
            exit;
        }
        
        // Create picking in database
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
        exit;
    }
    
    // Handle POST request
    if ($method === 'POST') {
        // Get JSON content from the request body
        $json = file_get_contents('php://input');
        $params = json_decode($json, true) ?: [];
        
        // If no JSON was provided, check form data
        if (empty($params)) {
            $params = [
                'box' => $_POST['box'] ?? null,
                'so_no' => $_POST['so_no'] ?? null,
                'items' => $_POST['items'] ?? [],
                'dimension' => $_POST['dimension'] ?? null,
                'weight' => $_POST['weight'] ?? null,
            ];
        }
        
        // Validate input
        $errors = validateInput($params);
        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $errors
            ]);
            exit;
        }
        
        // Create picking in database
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
        exit;
    }
    
    // If we get here, it's an unsupported method
    echo json_encode([
        'success' => false,
        'message' => 'Unsupported HTTP method',
        'allowed_methods' => ['GET', 'POST']
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create picking',
        'error' => $e->getMessage()
    ]);
}
