<?php
/**
 * Simple direct proxy for pickings status update
 * Minimalist approach to diagnose the issue
 */

// Set headers for cross-origin access and JSON response
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get parameters from either GET or POST
$so_no = isset($_GET['so_no']) ? $_GET['so_no'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

// If not in GET, try POST
if (empty($so_no) || empty($status)) {
    // Try to get from POST data
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    $so_no = isset($input['so_no']) ? $input['so_no'] : (isset($_POST['so_no']) ? $_POST['so_no'] : null);
    $status = isset($input['status']) ? $input['status'] : (isset($_POST['status']) ? $_POST['status'] : null);
}

// Debug information
$debug = [
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'so_no_received' => $so_no,
    'status_received' => $status,
    'get_params' => $_GET,
    'post_params' => $_POST
];

// Check if required parameters are present
if (empty($so_no) || empty($status)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters',
        'debug' => $debug
    ]);
    exit();
}

// This is where we would normally make the API call
// But for diagnostic purposes, let's directly update the picking
try {
    // Connect to database using Laravel connection if available
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Http\Kernel')->handle(Illuminate\Http\Request::capture());
    
    // Find the picking
    $picking = \App\Models\Picking::where('so_no', $so_no)->first();
    
    if (!$picking) {
        echo json_encode([
            'success' => false,
            'message' => 'Picking not found for SO number: ' . $so_no,
            'debug' => $debug
        ]);
        exit();
    }
    
    // Update the status
    $picking->status = $status;
    $picking->save();
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Picking status updated successfully',
        'data' => $picking->toArray(),
        'debug' => $debug
    ]);
    
} catch (\Exception $e) {
    // If Laravel bootstrapping fails, try direct DB connection
    echo json_encode([
        'success' => false,
        'message' => 'Error updating picking status',
        'error' => $e->getMessage(),
        'debug' => $debug
    ]);
}
