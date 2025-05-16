<?php
// Set headers to allow CORS and specify JSON response
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Get the raw POST data
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Validate the input
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

try {
    // Log file for debugging
    $logFile = 'order_api_debug.log';
    // Only clear if it's getting too large
    if (file_exists($logFile) && filesize($logFile) > 1024 * 1024) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "=== Log file cleared ===\n");
    }
    
    // Log the request
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "\n=== NEW ORDER REQUEST STARTED ===\n", FILE_APPEND);
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "REQUEST PAYLOAD: " . $inputJSON . PHP_EOL, FILE_APPEND);
    
    // Get user ID from query parameter
    $userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "USER ID: " . $userId . PHP_EOL, FILE_APPEND);
    
    // API endpoint
    //$apiEndpoint = 'https://4906-106-222-209-31.ngrok-free.app/create-order';
    $apiEndpoint = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/create-order';
    
    // Build the URL with query parameter
    $url = $apiEndpoint . '?user_id=' . urlencode($userId);
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "CONNECTING TO API: {$url}\n", FILE_APPEND);
    
    // Initialize cURL
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Cache-Control: no-cache, no-store',
        'Pragma: no-cache',
        'Content-Length: ' . strlen($inputJSON)
    ]);
    
    // Set other important cURL options
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);  // Force fresh connection
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);   // Don't reuse connection
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // Timeout after 30 seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
    
    // Execute the request
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "EXECUTING API REQUEST...\n", FILE_APPEND);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlInfo = curl_getinfo($ch);
    
    // Log the response info
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "RESPONSE RECEIVED:\n" .
       "  - Status Code: {$httpCode}\n" .
       "  - Time: {$curlInfo['total_time']}s\n" .
       "  - Size: {$curlInfo['size_download']} bytes\n", FILE_APPEND);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "CURL ERROR: {$error}" . PHP_EOL, FILE_APPEND);
        throw new Exception("Connection error: {$error}");
    }
    
    // Log the raw response for debugging (truncated if too large)
    $logResponse = (strlen($response) > 1000) ? substr($response, 0, 1000) . '...(truncated)' : $response;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "RAW RESPONSE: {$logResponse}" . PHP_EOL, FILE_APPEND);
    
    // Close cURL session
    curl_close($ch);
    
    // Return the response
    http_response_code($httpCode);
    echo $response;
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "REQUEST COMPLETED SUCCESSFULLY" . PHP_EOL, FILE_APPEND);
    
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Failed to connect to order API',
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    if (isset($logFile)) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}
