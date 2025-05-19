<?php
/**
 * Direct proxy for sales orders with pickings status
 * Uses the improved API endpoint for reliable results
 */

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get status filter if provided
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Build the API URL
$apiUrl = '/api/sales-pickings-join';
if ($status) {
    $apiUrl .= '?status=' . urlencode($status);
}

// Get hostname from current request
$host = $_SERVER['HTTP_HOST'];

try {
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "http://{$host}{$apiUrl}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout to prevent long loading
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Check for errors
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);
    
    // Handle response based on HTTP code
    if ($httpCode >= 200 && $httpCode < 300) {
        echo $response;
    } else {
        // Something went wrong with the API
        echo json_encode([
            'success' => false,
            'message' => 'API returned error status',
            'http_code' => $httpCode,
            'response' => json_decode($response, true) ?: $response
        ]);
    }
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch data',
        'error' => $e->getMessage()
    ]);
}
?>
