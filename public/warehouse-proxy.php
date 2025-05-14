<?php
// Set headers to allow CORS and specify JSON response
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only GET requests are allowed']);
    exit;
}

// Get the user_id from the query string
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if (!$userId) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'user_id parameter is required']);
    exit;
}

// Sample warehouse data to use as fallback or default data
function getSampleWarehouseData($userId) {
    // Extract the domain from the email if it's an email
    $domain = '';
    if (filter_var($userId, FILTER_VALIDATE_EMAIL)) {
        $parts = explode('@', $userId);
        $domain = isset($parts[1]) ? $parts[1] : '';
    }
    
    // Customize warehouse names based on the domain or email
    $companyName = ucfirst(strtok($domain, '.')) ?: 'Company';
    if (empty($companyName) || $companyName == 'Company') {
        // If no domain found, use the first part of the email
        $companyName = ucfirst(strtok($userId, '@')) ?: 'Company';
    }
    
    return [
        [
            'id' => 'WH' . substr(md5($userId . '1'), 0, 6),
            'name' => $companyName . ' Mumbai Central Warehouse',
            'address' => '123 Industrial Area, Andheri East',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'zip' => '400069',
            'contact' => 'Warehouse Manager',
            'phone' => '+91 9876543210',
            'email' => $userId
        ],
        [
            'id' => 'WH' . substr(md5($userId . '2'), 0, 6),
            'name' => $companyName . ' Delhi Distribution Center',
            'address' => '456 Logistic Park, Okhla',
            'city' => 'New Delhi',
            'state' => 'Delhi',
            'zip' => '110020',
            'contact' => 'Operations Head',
            'phone' => '+91 9876543211',
            'email' => $userId
        ],
        [
            'id' => 'WH' . substr(md5($userId . '3'), 0, 6),
            'name' => $companyName . ' Bangalore Tech Hub',
            'address' => '789 Industrial Layout, Electronic City',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'zip' => '560100',
            'contact' => 'Facility Manager',
            'phone' => '+91 9876543212',
            'email' => $userId
        ]
    ];
}

try {
    // Log file for debugging
    $logFile = 'warehouse_api_debug.log';
    // Only clear if it's getting too large
    if (file_exists($logFile) && filesize($logFile) > 1024 * 1024) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "=== Log file cleared ===\n");
    }
    
    // Log the request
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "\n=== NEW WAREHOUSE REQUEST STARTED ===\n", FILE_APPEND);
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "USER ID: " . $userId . PHP_EOL, FILE_APPEND);
    
    // API endpoint
    $apiEndpoint = 'https://c7c7-2401-4900-8815-9ac5-f169-ae0d-40fd-b1e9.ngrok-free.app/warehouses';
    
    // Build the URL with query parameter
    $url = $apiEndpoint . '?user_id=' . urlencode($userId);
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "CONNECTING TO API: {$url}\n", FILE_APPEND);
    
    // Get sample data first (will be used if API fails)
    $sampleData = getSampleWarehouseData($userId);
    
    // Initialize cURL
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Cache-Control: no-cache, no-store',
        'Pragma: no-cache'
    ]);
    
    // Set other important cURL options
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);  // Force fresh connection
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);   // Don't reuse connection
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // Timeout after 30 seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
    
    // Execute the request
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "EXECUTING API REQUEST...\n", FILE_APPEND);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlInfo = curl_getinfo($ch);
    
    // Log the response info
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "RESPONSE RECEIVED:\n" .
       "  - Status Code: {$httpCode}\n" .
       "  - Time: {$curlInfo['total_time']}s\n" .
       "  - Size: {$curlInfo['size_download']} bytes\n", FILE_APPEND);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "CURL ERROR: {$error}" . PHP_EOL, FILE_APPEND);
        throw new Exception("Connection error: {$error}");
    }
    
    // Log the raw response for debugging (truncated if too large)
    $logResponse = (strlen($response) > 1000) ? substr($response, 0, 1000) . '...(truncated)' : $response;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "RAW RESPONSE: {$logResponse}" . PHP_EOL, FILE_APPEND);
    
    // Close cURL session
    curl_close($ch);
    
    // Check if the response is valid JSON
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If the response is not valid JSON or API returned error, provide sample data
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "WARNING: Invalid JSON response or API error, providing sample data" . PHP_EOL, FILE_APPEND);
        
        // Return the pre-generated sample data with a flag indicating it's fallback data
        $responseData = [
            'source' => 'fallback',
            'message' => 'Using fallback data because the API returned invalid JSON.',
            'warehouses' => $sampleData
        ];
        http_response_code(200);
        echo json_encode($responseData);
    } else if ($httpCode == 200) {
        // If the response is valid JSON AND the status is 200, we have real API data
        // Check if the data is in the expected format (success/data structure)
        if (isset($responseData['success']) && isset($responseData['data']) && is_array($responseData['data'])) {
            // The API returned data in the expected format
            $apiData = [
                'source' => 'api',
                'message' => 'Data successfully retrieved from API.',
                'warehouses' => $responseData['data'] // Extract just the 'data' array
            ];
        } else {
            // The API returned valid JSON but not in the expected format
            $apiData = [
                'source' => 'api',
                'message' => 'Data successfully retrieved from API.',
                'warehouses' => $responseData // Use the whole response
            ];
        }
        http_response_code(200);
        echo json_encode($apiData);
    } else {
        // If there was an error status code, use sample data
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "API returned status code: {$httpCode}, using sample data" . PHP_EOL, FILE_APPEND);
        
        // Return the pre-generated sample data with a flag indicating it's fallback data
        $responseData = [
            'source' => 'fallback',
            'message' => "Using fallback data because the API returned status code: {$httpCode}",
            'warehouses' => $sampleData
        ];
        http_response_code(200); // Force 200 OK
        echo json_encode($responseData);
    }
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "REQUEST COMPLETED SUCCESSFULLY" . PHP_EOL, FILE_APPEND);
    
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Failed to connect to warehouse API',
        'message' => $e->getMessage()
    ]);
    
    // Log the error
    if (isset($logFile)) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}
