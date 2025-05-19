<?php
/**
 * Proxy file for sales orders with pickings status API
 * Joins sales_orders and pickings tables by so_no and includes picking status
 */

// Allow access from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get status filter if provided
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Create API URL
$apiUrl = '/api/sales-orders-with-pickings';
if ($status) {
    $apiUrl .= '?status=' . urlencode($status);
}

// Get the base URL from current request
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'];

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $baseUrl . $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
// Important: Prevent following redirects
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
// Disable SSL verification for local testing
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// Set timeout to prevent long loading
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

// Execute cURL request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Error handling
if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL error',
        'error' => curl_error($ch),
        'url' => $baseUrl . $apiUrl
    ]);
} else {
    // Return the API response
    http_response_code($httpCode);
    echo $response ?: json_encode([
        'success' => false,
        'message' => 'No response received from API',
        'http_code' => $httpCode,
        'url' => $baseUrl . $apiUrl
    ]);
}

// Close cURL
curl_close($ch);
?>
