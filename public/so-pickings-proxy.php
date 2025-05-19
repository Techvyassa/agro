<?php
/**
 * SO Pickings Proxy
 * 
 * Handles the many-to-many relationship between sales orders and pickings
 * Optimized for the specific data structure with proper grouping
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get status parameter if provided
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Build API URL
$apiUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . 
          $_SERVER['HTTP_HOST'];

// Determine the relative path based on server configuration
$port = $_SERVER['SERVER_PORT'] ?? '80';
if ($port == '8000') {
    // Laravel development server
    $apiUrl .= '/api/so-pickings';
} else {
    // XAMPP
    $apiUrl .= '/agro/api/so-pickings';
}

// Add status parameter if provided
if ($status) {
    $apiUrl .= '?status=' . urlencode($status);
}

try {
    // Initialize cURL
    $ch = curl_init();
    
    // Set options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a reasonable timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Error handling
    if (curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch) . ' URL: ' . $apiUrl);
    }
    
    // Close cURL
    curl_close($ch);
    
    // Output response
    if ($httpCode >= 200 && $httpCode < 300) {
        echo $response;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'API returned error status ' . $httpCode,
            'url' => $apiUrl,
            'response' => json_decode($response, true) ?: $response
        ]);
    }
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'url' => $apiUrl
    ]);
}
?>
