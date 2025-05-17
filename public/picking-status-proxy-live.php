<?php
/**
 * Picking Status Proxy (Live Server Version)
 * 
 * This file serves as a direct proxy for updating picking status
 * without relying on Laravel's routing system.
 */

// Error reporting for debugging (remove in production)
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

// Live server database connection
// Update these values to match your live server configuration
$servername = "127.0.0.1";
$username = "vyassa44_agro";  // Update with your live DB username
$password = "RoyalK1234";    // Update with your live DB password
$dbname = "vyassa44_agro";   // Update with your live DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]);
    exit;
}

// Log request for debugging
$logfile = dirname(__FILE__) . '/picking_status_log.txt';
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Request received\n", FILE_APPEND);

// Get POST data
$postData = json_decode(file_get_contents('php://input'), true);

// Log received data
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

// Log the values being used
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

// Find and update all pickings with the same so_no
$updateQuery = "UPDATE pickings SET status = '$status', updated_at = NOW() WHERE so_no = '$so_no'";
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Running query: $updateQuery\n", FILE_APPEND);

if ($conn->query($updateQuery) === TRUE) {
    // Get the count of updated records
    $affectedRows = $conn->affected_rows;
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Affected rows: $affectedRows\n", FILE_APPEND);
    
    if ($affectedRows > 0) {
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
        echo json_encode([
            'success' => false,
            'message' => 'No pickings found with the given SO number: ' . $so_no,
            'check_count' => $recordCount
        ]);
    }
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
