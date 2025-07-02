<?php
/**
 * Location Proxy - Handles API requests to location endpoints
 * 
 * This script serves as a proxy to bypass CORS restrictions when fetching
 * location data from the warehouse/location API endpoints.
 */

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// If it's an OPTIONS request, stop here (pre-flight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get request data
$requestData = json_decode(file_get_contents('php://input'), true);

// Validate the request
if (!isset($requestData['url']) || empty($requestData['url'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No URL provided'
    ]);
    exit;
}

// For debugging
error_log('Location Proxy Request: ' . print_r($requestData, true));

// Special handling for Delhivery warehouse API calls
if (strpos($requestData['url'], 'delhivery-warehouses') !== false) {
    error_log('Detected Delhivery warehouses API call');
}

$url = $requestData['url'];

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Include additional headers if they were provided
if (isset($requestData['headers']) && is_array($requestData['headers'])) {
    $headers = [];
    foreach ($requestData['headers'] as $key => $value) {
        $headers[] = "$key: $value";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
}

// Handle any HTTP method
$method = isset($requestData['method']) ? strtoupper($requestData['method']) : 'GET';
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

// Handle POST/PUT/PATCH requests with raw body
if (in_array($method, ['POST', 'PUT', 'PATCH']) && isset($requestData['body'])) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData['body']);
}

// Add debug logging for outgoing request
error_log('Proxy outgoing: ' . print_r([
    'url' => $url,
    'method' => $method,
    'headers' => isset($headers) ? $headers : [],
    'body' => isset($requestData['body']) ? $requestData['body'] : null
], true));

// Execute cURL request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL error: ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

// Get HTTP status code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL session
curl_close($ch);

// Always return the raw API response and status code
http_response_code($httpCode);
header('Content-Type: application/json');
echo $response;
?>
