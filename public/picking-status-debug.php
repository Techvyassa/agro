<?php
/**
 * Picking Status Debug
 * 
 * This file logs and displays request information for debugging purposes
 */

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Create a log file
$logFile = __DIR__ . '/picking-status-debug.log';
$logData = "======== New Request: " . date('Y-m-d H:i:s') . " ========\n";
$logData .= "Request Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$logData .= "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
$logData .= "HTTP Host: " . $_SERVER['HTTP_HOST'] . "\n";

// Log headers
$logData .= "Request Headers:\n";
foreach (getallheaders() as $name => $value) {
    $logData .= "$name: $value\n";
}

// Log POST data
$postData = file_get_contents('php://input');
$logData .= "Request Body:\n$postData\n\n";

// Write to log file
file_put_contents($logFile, $logData, FILE_APPEND);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Decode the JSON payload
$jsonData = json_decode($postData, true);

// Now process the request and send back what was received
echo json_encode([
    'success' => true,
    'message' => 'Debug information captured successfully',
    'received_data' => $jsonData,
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'http_host' => $_SERVER['HTTP_HOST']
]);
