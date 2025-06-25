<?php
/**
 * Manifest Creation Proxy - Handles API requests to create manifest endpoint
 * 
 * This script serves as a proxy to bypass CORS restrictions when sending
 * manifest creation requests to the Delhivery API endpoints.
 */

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// If it's an OPTIONS request, stop here (pre-flight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// API endpoint
$apiEndpoint = 'http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/create-manifest';

// Enhanced debugging
error_log('=========== MANIFEST PROXY REQUEST START ===========');
error_log('Manifest Proxy POST Data: ' . print_r($_POST, true));
error_log('Manifest Proxy FILES: ' . print_r($_FILES, true));

// Debug the manifest payload
if (isset($_POST['manifest_payload'])) {
    error_log('Manifest Payload: ' . $_POST['manifest_payload']);
}

error_log('Login Type: ' . (isset($_POST['login_type']) ? $_POST['login_type'] : 'Not provided'));
error_log('File Exists: ' . (isset($_FILES['file']) && file_exists($_FILES['file']['tmp_name']) ? 'Yes' : 'No'));
if (isset($_FILES['file'])) {
    error_log('File Info: Size=' . $_FILES['file']['size'] . ' Type=' . $_FILES['file']['type'] . ' Name=' . $_FILES['file']['name']);
}

// Check if we have the required parameters
if (!isset($_POST['manifest_payload']) || !isset($_POST['login_type']) || !isset($_FILES['file'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameters (manifest_payload, login_type, or file)'
    ]);
    exit;
}

// Initialize cURL session
$ch = curl_init();

// Prepare the POST data
$postData = [
    'login_type' => $_POST['login_type'],
    'manifest_payload' => $_POST['manifest_payload']
];

// Handle file upload
$fileTmpPath = $_FILES['file']['tmp_name'];
$fileName = $_FILES['file']['name'];

// Check if file exists
if (!file_exists($fileTmpPath)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'File upload failed or no file was provided'
    ]);
    exit;
}

// Add file to cURL
$cFile = new CURLFile($fileTmpPath, $_FILES['file']['type'], $fileName);
$postData['file'] = $cFile;

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increase timeout for large file uploads
curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output

// Create a temp file handle for the cURL verbose information
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Log the API endpoint
error_log('Sending to API Endpoint: ' . $apiEndpoint);

// Execute cURL request
$response = curl_exec($ch);

// Get verbose debugging information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
error_log("cURL Verbose Information:\n" . $verboseLog);

// Check for errors
if (curl_errno($ch)) {
    $errorMessage = 'cURL error: ' . curl_error($ch);
    error_log('cURL Error: ' . $errorMessage);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $errorMessage,
        'curl_error_code' => curl_errno($ch)
    ]);
    curl_close($ch);
    exit;
}

// Get HTTP status code and other info
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$requestInfo = curl_getinfo($ch);

// Log detailed response information
error_log('HTTP Status Code: ' . $httpCode);
error_log('Response Info: ' . print_r($requestInfo, true));
error_log('Raw Response: ' . $response);

// Close cURL session
curl_close($ch);

// Set content type header
header('Content-Type: application/json');

// Check if we got a valid response
if ($httpCode >= 200 && $httpCode < 300) {
    // Try to decode JSON response
    $decodedResponse = json_decode($response, true);
    
    // If it's valid JSON, return it as is
    if ($decodedResponse !== null) {
        echo $response;
    } else {
        // If not valid JSON, wrap it in a success response
        echo json_encode([
            'success' => true,
            'data' => $response
        ]);
    }
} else {
    // Handle error responses
    echo json_encode([
        'success' => false,
        'error' => 'API returned error code: ' . $httpCode,
        'response' => $response
    ]);
}
?>
