<?php
/**
 * Picking Status Proxy
 * 
 * This file serves as a direct proxy for updating picking status
 * without relying on Laravel's routing system.
 */

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Set up database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agro"; // Change this to your actual database name

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

// Get POST data
$postData = json_decode(file_get_contents('php://input'), true);

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

// Find and update all pickings with the same so_no
$updateQuery = "UPDATE pickings SET status = '$status', updated_at = NOW() WHERE so_no = '$so_no'";

if ($conn->query($updateQuery) === TRUE) {
    // Get the count of updated records
    $affectedRows = $conn->affected_rows;
    
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
            'message' => 'No pickings found with the given SO number: ' . $so_no
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update picking status',
        'error' => $conn->error
    ]);
}

$conn->close();
