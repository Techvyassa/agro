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
    // Debug: Log request payload to a file
    $debugLog = fopen('freight_api_debug.log', 'a');
    fwrite($debugLog, "==== REQUEST " . date('Y-m-d H:i:s') . " ====\n");
    fwrite($debugLog, $inputJSON . "\n\n");
    
    // Create cURL session 
    //change api url
    $ch = curl_init('http://ec2-54-172-12-118.compute-1.amazonaws.com:8000/get-freight-estimates');
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($inputJSON)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Debug: Log response
    fwrite($debugLog, "==== RESPONSE (Status: $httpCode) ====\n");
    fwrite($debugLog, $response . "\n\n");
    fwrite($debugLog, "=====================================\n\n");
    fclose($debugLog);
    
    // Check for errors
    if (curl_errno($ch)) {
        throw new Exception(curl_error($ch));
    }
    
    // Close cURL session
    curl_close($ch);
    
    // Return the API response with the same status code
    http_response_code($httpCode);
    echo $response;
    
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Failed to connect to freight API',
        'message' => $e->getMessage()
    ]);
}
