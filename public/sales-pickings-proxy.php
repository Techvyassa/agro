<?php
/**
 * Sales Pickings Proxy
 * 
 * This file serves as a proxy to call the sales-orders-with-pickings API endpoint
 * which joins the sales_orders and pickings tables based on so_no field
 * and returns the combined data including the status from pickings.
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Allow requests from any origin (adjust as needed for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Determine base URL based on server port
$serverPort = $_SERVER['SERVER_PORT'] ?? 80;

// Define the API endpoint based on the server port
if ($serverPort == 8000) {
    // Laravel development server
    $apiEndpoint = 'http://127.0.0.1:8000/api/sales-orders-with-pickings';
} else {
    // XAMPP server
    $apiEndpoint = 'http://localhost/agro/api/sales-orders-with-pickings';
}

// Add status filter parameter if provided
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = urlencode($_GET['status']);
    $apiEndpoint .= "?status={$status}";
}

// Debug info - remove in production
echo "<!-- DEBUG: Using API endpoint: {$apiEndpoint} -->\n";

try {
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (not recommended for production)
    
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
    
    // Set the HTTP response code
    http_response_code($httpCode);
    
    // Output the response
    echo $response;
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch sales orders with pickings data',
        'error' => $e->getMessage()
    ]);
}
?>
