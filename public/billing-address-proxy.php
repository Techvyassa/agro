<?php
/**
 * Billing Address Proxy - Handles API requests to billing address endpoints
 * 
 * This script serves as a proxy to bypass CORS restrictions when fetching
 * billing address data from the Delhivery API endpoints.
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
error_log('Billing Address Proxy Request: ' . print_r($requestData, true));

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

// Handle POST requests with data
if (isset($requestData['method']) && strtoupper($requestData['method']) === 'POST' && isset($requestData['data'])) {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData['data']));
}

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

// Check if we got a valid response
if ($httpCode >= 200 && $httpCode < 300) {
    // Try to decode JSON response
    $decodedResponse = json_decode($response, true);
    
    // If it's valid JSON, return it as is
    if ($decodedResponse !== null) {
        echo $response;
    } else {
        // If not valid JSON, wrap it in a success response
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
    }
} else {
    // Handle error responses
    echo json_encode([
        'success' => false,
        'message' => 'API returned error code: ' . $httpCode,
        'data' => $response
    ]);
}
?>
