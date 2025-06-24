<?php
// Set headers to allow CORS and specify JSON response
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Get the raw POST data
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Validate the input
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

try {
    // Clear previous logs to avoid confusion
    $logFile = 'freight_api_debug.log';
    // Only clear if it's getting too large
    if (file_exists($logFile) && filesize($logFile) > 1024 * 1024) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "=== Log file cleared ===\n");
    }
    
    // Log the input payload for debugging with clear separation
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "\n=== NEW REQUEST STARTED ===\n", FILE_APPEND);
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "REQUEST PAYLOAD: " . $inputJSON . PHP_EOL, FILE_APPEND);
    
    // Extract key information for verification
    $sourcePincode = $input['common']['pincode']['source'] ?? 'unknown';
    $destPincode = $input['common']['pincode']['destination'] ?? 'unknown';
    $weightG = $input['shipment_details']['weight_g'] ?? 0;
    $boxCount = count($input['shipment_details']['dimensions'] ?? []);
    $dimensions = [];
    
    // Extract dimension details for logging
    if (isset($input['shipment_details']['dimensions']) && is_array($input['shipment_details']['dimensions'])) {
        foreach ($input['shipment_details']['dimensions'] as $dim) {
            $dimensions[] = "{$dim['length_cm']}x{$dim['width_cm']}x{$dim['height_cm']}";
        }
    }
    
    $dimensionsStr = implode(',', $dimensions);
    
    // Add request identifier based on real parameters to detect duplicate responses
    $requestId = md5($sourcePincode . $destPincode . $dimensionsStr . $weightG . time());
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "REQUEST DETAILS:\n" .
       "  - ID: {$requestId}\n" .
       "  - Source: {$sourcePincode}\n" .
       "  - Destination: {$destPincode}\n" .
       "  - Weight: {$weightG}g\n" .
       "  - Boxes: {$boxCount}\n" .
       "  - Dimensions: {$dimensionsStr}\n", FILE_APPEND);
    
    // Create cURL session with the correct API endpoint
     $apiEndpoint = 'http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/get-freight-estimates';
    //$apiEndpoint = 'http://ec2-52-205-180-161.compute-1.amazonaws.com:8000/get-freight-estimates';

    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "CONNECTING TO API: {$apiEndpoint}\n", FILE_APPEND);
    
    // Initialize cURL with proper options to prevent caching
    $ch = curl_init($apiEndpoint);
    
    // Set comprehensive cURL options to ensure real requests
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $inputJSON);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Cache-Control: no-cache, no-store',
        'Pragma: no-cache',
        'X-Request-ID: ' . $requestId,
        'X-Source-Pincode: ' . $sourcePincode,
        'X-Dest-Pincode: ' . $destPincode,
        'Content-Length: ' . strlen($inputJSON)
    ]);
    
    // Set other important cURL options
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);  // Force fresh connection
    curl_setopt($ch, CURLOPT_FORBID_REUSE, true);   // Don't reuse connection
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // Timeout after 30 seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
    
    // Execute the request and log detailed info
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "EXECUTING API REQUEST...\n", FILE_APPEND);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlInfo = curl_getinfo($ch);
    
    // Log the complete response info
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
    
    // Validate the response is proper JSON
    $responseData = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR: Invalid JSON response" . PHP_EOL, FILE_APPEND);
        throw new Exception('Invalid JSON response from API. Please check API endpoint.');
    }
    
    // Analyze the response to detect hardcoded/canned responses
    $carrierCount = count($responseData);
    $estimateCount = 0;
    $priceSum = 0;
    $responseFingerprint = "";
    
    // Extract key details for verification
    foreach ($responseData as $carrier => $estimates) {
        if (is_array($estimates)) {
            $estimateCount += count($estimates);
            foreach ($estimates as $estimate) {
                if (isset($estimate['total_charges'])) {
                    $priceSum += $estimate['total_charges'];
                    // Build a fingerprint of the response to detect duplicates
                    $responseFingerprint .= "{$carrier}:{$estimate['total_charges']},";
                }
            }
        }
    }
    
    // Save the response fingerprint to detect duplicate responses
    $fingerprintFile = 'response_fingerprints.log';
    $fingerprintData = "[{$sourcePincode}-{$destPincode}] {$responseFingerprint}\n";
    file_put_contents($fingerprintFile, $fingerprintData, FILE_APPEND);
    
    // Enhanced logging of response details
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "RESPONSE ANALYSIS:\n" .
       "  - Carriers: {$carrierCount}\n" .
       "  - Estimates: {$estimateCount}\n" .
       "  - Price Sum: {$priceSum}\n" .
       "  - Fingerprint: {$responseFingerprint}\n", FILE_APPEND);
    
    // Log warning if response seems suspicious (e.g., empty or very small)
    if ($carrierCount === 0 || $estimateCount === 0) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "WARNING: Empty response received" . PHP_EOL, FILE_APPEND);
    }
    
    // IMPORTANT: Instead of modifying the response, we should pass it through directly
    // Log the fact that we're returning the original API response
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "RETURNING ORIGINAL API RESPONSE WITHOUT MODIFICATION" . PHP_EOL, FILE_APPEND);
    
    // Return the exact API response with the same status code
    http_response_code($httpCode);
    echo $response; // Use the original API response directly
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "REQUEST COMPLETED SUCCESSFULLY" . PHP_EOL, FILE_APPEND);
    
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Failed to connect to freight API',
        'message' => $e->getMessage()
    ]);
}
