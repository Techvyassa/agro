<?php
/**
 * Proxy file for pickings-status-update API
 * This file handles the request to the API endpoint and properly formats the data
 */

// Allow access from any origin (for testing purposes)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get input data
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// For GET requests, use query parameters
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $so_no = isset($_GET['so_no']) ? $_GET['so_no'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
} else {
    // For POST requests, use JSON body or form data
    $so_no = isset($input['so_no']) ? $input['so_no'] : (isset($_POST['so_no']) ? $_POST['so_no'] : null);
    $status = isset($input['status']) ? $input['status'] : (isset($_POST['status']) ? $_POST['status'] : null);
}

// Validate input
if (!$so_no || !$status) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters',
        'error' => 'Both so_no and status are required',
        'received' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'so_no' => $so_no,
            'status' => $status,
            'input' => $input,
            'post' => $_POST,
            'get' => $_GET
        ]
    ]);
    exit();
}

// Prepare data for API request
$apiData = [
    'so_no' => $so_no,
    'status' => $status,
];

// API URL - using the relative path to the Laravel API endpoint
$apiUrl = '/api/pickings-status-update';

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Execute cURL request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Error handling
if (curl_errno($ch)) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL error',
        'error' => curl_error($ch)
    ]);
} else {
    // Return the API response
    http_response_code($httpCode);
    echo $response;
}

// Close cURL
curl_close($ch);
