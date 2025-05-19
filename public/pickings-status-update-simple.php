<?php
/**
 * Pickings Status Update Simple Proxy
 * 
 * This file is a direct database connection implementation 
 * for updating picking status by so_no, avoiding Laravel routing.
 */

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Log directory setup
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
$logfile = $logDir . '/pickings_status_update_log.txt';
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);

// Database configuration (same as your Laravel .env)
$servername = "192.250.231.31"; // Usually localhost or 127.0.0.1
$username = "vyassa44_agro";        // Update with your DB username for live server
$password = "RoyalK1234";            // Update with your DB password for live server
$dbname = "vyassa44_agro";    // Update with your DB name for live server

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]);
    exit;
}

// Get data from multiple sources (more flexible than original proxy)
$so_no = null;
$status = null;

// Check JSON POST data
$inputJSON = file_get_contents('php://input');
$postData = json_decode($inputJSON, true);

if ($postData && isset($postData['so_no']) && isset($postData['status'])) {
    $so_no = $postData['so_no'];
    $status = $postData['status'];
} 
// Check form POST data
else if (isset($_POST['so_no']) && isset($_POST['status'])) {
    $so_no = $_POST['so_no'];
    $status = $_POST['status'];
} 
// Check GET parameters (for easy testing)
else if (isset($_GET['so_no']) && isset($_GET['status'])) {
    $so_no = $_GET['so_no'];
    $status = $_GET['status'];
}

// Log received data
$requestData = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'json_data' => $postData,
    'post_data' => $_POST,
    'get_data' => $_GET,
    'so_no' => $so_no,
    'status' => $status
];
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Received data: " . json_encode($requestData) . "\n", FILE_APPEND);

// Validate required fields
if (!$so_no || !$status) {
    $errorResponse = [
        'success' => false,
        'message' => 'Missing required fields: so_no and status are required',
        'received_data' => $requestData
    ];
    echo json_encode($errorResponse);
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Error: " . json_encode($errorResponse) . "\n", FILE_APPEND);
    exit;
}

// Escape inputs for SQL safety
$so_no = $conn->real_escape_string($so_no);
$status = $conn->real_escape_string($status);

file_put_contents($logfile, date('Y-m-d H:i:s') . " - Updating SO: $so_no to status: $status\n", FILE_APPEND);

// First check if records exist
$checkQuery = "SELECT COUNT(*) as count FROM pickings WHERE so_no = '$so_no'";
$checkResult = $conn->query($checkQuery);
$recordCount = 0;

if ($checkResult) {
    $row = $checkResult->fetch_assoc();
    $recordCount = $row['count'];
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Found $recordCount matching records\n", FILE_APPEND);
}

if ($recordCount === 0) {
    $notFoundResponse = [
        'success' => false,
        'message' => 'No pickings found with the given SO number: ' . $so_no
    ];
    echo json_encode($notFoundResponse);
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Error: " . json_encode($notFoundResponse) . "\n", FILE_APPEND);
    $conn->close();
    exit;
}

// Update all matching records
$updateQuery = "UPDATE pickings SET status = '$status', updated_at = NOW() WHERE so_no = '$so_no'";
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Running query: $updateQuery\n", FILE_APPEND);

if ($conn->query($updateQuery) === TRUE) {
    $affectedRows = $conn->affected_rows;
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Affected rows: $affectedRows\n", FILE_APPEND);
    
    // Get all updated pickings
    $updatedResult = $conn->query("SELECT * FROM pickings WHERE so_no = '$so_no'");
    $updatedPickings = [];
    
    while ($row = $updatedResult->fetch_assoc()) {
        $updatedPickings[] = $row;
    }
    
    $successResponse = [
        'success' => true,
        'message' => "Successfully updated $affectedRows picking records with SO number: $so_no",
        'data' => $updatedPickings
    ];
    echo json_encode($successResponse);
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Success: " . json_encode($successResponse) . "\n", FILE_APPEND);
} else {
    $errorMsg = $conn->error;
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Error: " . $errorMsg . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update picking status',
        'error' => $errorMsg
    ]);
}

// Close database connection
$conn->close();
