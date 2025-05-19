<?php
/**
 * Picking Status Proxy (Simple Version)
 * 
 * This file serves as a direct proxy for updating picking status
 * without relying on Laravel's routing system.
 * Simplified version without Laravel bootstrapping for live server compatibility.
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
$logfile = $logDir . '/picking_status_log.txt';
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);

// Database configuration (same as your Laravel .env)
$servername = "192.250.231.31"; // Usually localhost or 127.0.0.1
$username = "vyassa44_agro";        // Update with your DB username for live server
$password = "RoyalK1234";            // Update with your DB password for live server
$dbname = "vyassa44_agro";        // Update with your DB name for live server

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

// Get POST data
$postData = json_decode(file_get_contents('php://input'), true);
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Data: " . json_encode($postData) . "\n", FILE_APPEND);

if (!$postData) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

// Validate required fields
if (!isset($postData['so_no']) || !isset($postData['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: so_no and status are required'
    ]);
    exit;
}

$so_no = $conn->real_escape_string($postData['so_no']);
$status = $conn->real_escape_string($postData['status']);

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
    echo json_encode([
        'success' => false,
        'message' => 'No pickings found with the given SO number: ' . $so_no
    ]);
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
    
    echo json_encode([
        'success' => true,
        'message' => "Successfully updated $affectedRows picking records with SO number: $so_no",
        'data' => $updatedPickings
    ]);
} else {
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Error: " . $conn->error . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update picking status',
        'error' => $conn->error
    ]);
}

$conn->close();
?>
