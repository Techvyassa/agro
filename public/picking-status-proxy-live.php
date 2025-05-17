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

// Bootstrap the Laravel application
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Use Laravel's Facades
use Illuminate\Support\Facades\DB;
use App\Models\Picking;

// Create a log directory if it doesn't exist
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Log file path
$logfile = $logDir . '/picking_status_log.txt';

// Log the request
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

// Get clean values
$so_no = trim($postData['so_no']);
$status = trim($postData['status']);

// Log the values being used
file_put_contents($logfile, date('Y-m-d H:i:s') . " - Updating SO: $so_no to status: $status\n", FILE_APPEND);

try {
    // Check if records exist
    $recordCount = Picking::where('so_no', $so_no)->count();
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Found $recordCount matching records\n", FILE_APPEND);
    
    if ($recordCount === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No pickings found with the given SO number: ' . $so_no
        ]);
        exit;
    }
    
    // Update all matching records using Eloquent
    $affectedRows = Picking::where('so_no', $so_no)
        ->update([
            'status' => $status,
            'updated_at' => now()
        ]);
    
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Affected rows: $affectedRows\n", FILE_APPEND);
    
    if ($affectedRows > 0) {
        // Get all updated pickings
        $updatedPickings = Picking::where('so_no', $so_no)->get();
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully updated $affectedRows picking records with SO number: $so_no",
            'data' => $updatedPickings
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No records were updated',
            'check_count' => $recordCount
        ]);
    }
} catch (\Exception $e) {
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update picking status',
        'error' => $e->getMessage()
    ]);
}
?>
