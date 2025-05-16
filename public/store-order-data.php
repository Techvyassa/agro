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
$postData = file_get_contents('php://input');
$orderData = json_decode($postData, true);

if (!$orderData) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Log all order data for debugging
$logFile = 'orders_debug.log';
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "\n=== NEW ORDER LOGGED ===\n", FILE_APPEND);
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Request Body: " . $postData . PHP_EOL, FILE_APPEND);

// Create a data structure to store in the database
$dbOrderData = [
    'order_id' => $orderData['order_id'] ?? $orderData['orderIds'] ?? '',
    'order_date' => $orderData['orderDate'] ?? date('Y-m-d'),
    'pickup_id' => $orderData['pickUpId'] ?? '',
    'pickup_city' => $orderData['pickUpCity'] ?? '',
    'pickup_state' => $orderData['pickUpState'] ?? '',
    'return_id' => $orderData['retrunId'] ?? '',
    'return_city' => $orderData['returnCity'] ?? '',
    'return_state' => $orderData['returnState'] ?? '',
    'invoice_amount' => $orderData['invoiceAmt'] ?? 0,
    'item_name' => $orderData['itemName'] ?? '',
    'cod_amount' => $orderData['codAmt'] ?? 0,
    'quantity' => $orderData['qty'] ?? 1,
    'buyer_name' => isset($orderData['buyer']) ? (($orderData['buyer']['fName'] ?? '') . ' ' . ($orderData['buyer']['lName'] ?? '')) : '',
    'buyer_email' => isset($orderData['buyer']) ? ($orderData['buyer']['emailId'] ?? '') : '',
    'buyer_address' => isset($orderData['buyer']) && isset($orderData['buyer']['buyerAddresses']) ? ($orderData['buyer']['buyerAddresses']['address1'] ?? '') : '',
    'buyer_phone' => isset($orderData['buyer']) && isset($orderData['buyer']['buyerAddresses']) ? ($orderData['buyer']['buyerAddresses']['mobileNo'] ?? '') : '',
    'buyer_pincode' => isset($orderData['buyer']) && isset($orderData['buyer']['buyerAddresses']) ? ($orderData['buyer']['buyerAddresses']['pinId'] ?? '') : '',
    'order_status' => isset($orderData['response']) && isset($orderData['response']['success']) && $orderData['response']['success'] ? 'SUCCESS' : 'FAILED',
    'response_code' => isset($orderData['response']) ? ($orderData['response']['responseCode'] ?? 0) : 0,
    'response_message' => isset($orderData['response']) ? ($orderData['response']['message'] ?? '') : '',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'raw_request' => $postData,
    'raw_response' => json_encode($orderData['response'] ?? [])
];

// Log the prepared data structure
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Prepared Data: " . json_encode($dbOrderData, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

// Try to connect to the database 
try {
    // Database credentials - adjust these to match your XAMPP setup
    $host = 'localhost';
    $dbname = 'agro';
    $username = 'root';
    $password = '';
    
    // Create database connection to the agro database directly
    $dsn = "mysql:host=$host;dbname=$dbname;charset=UTF8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify that the orders table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($stmt->rowCount() == 0) {
        // Table doesn't exist, inform the user
        throw new Exception('Orders table does not exist in the agro database. Please run the migration using: php artisan migrate');    
    }
    
    // Prepare SQL statement with updated_at field
    $sql = "INSERT INTO orders (
        order_id, order_date, pickup_id, pickup_city, pickup_state, 
        return_id, return_city, return_state, invoice_amount, item_name, 
        cod_amount, quantity, buyer_name, buyer_email, buyer_address, 
        buyer_phone, buyer_pincode, order_status, response_code, 
        response_message, created_at, updated_at, raw_request, raw_response
    ) VALUES (
        :order_id, :order_date, :pickup_id, :pickup_city, :pickup_state, 
        :return_id, :return_city, :return_state, :invoice_amount, :item_name, 
        :cod_amount, :quantity, :buyer_name, :buyer_email, :buyer_address, 
        :buyer_phone, :buyer_pincode, :order_status, :response_code, 
        :response_message, :created_at, :updated_at, :raw_request, :raw_response
    )";
    
    $stmt = $pdo->prepare($sql);
    
    // Log SQL query for debugging
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "SQL Query: " . $sql . PHP_EOL, FILE_APPEND);
    
    // Bind parameters (using named parameters)
    foreach ($dbOrderData as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Binding: " . $key . " = " . (is_array($value) ? json_encode($value) : $value) . PHP_EOL, FILE_APPEND);
    }
    
    // Execute the statement
    $stmt->execute();
    
    // Get the last inserted ID
    $lastId = $pdo->lastInsertId();
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Success! Order stored with ID: " . $lastId . PHP_EOL, FILE_APPEND);
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Order data stored successfully',
        'id' => $lastId
    ]);
    
} catch (PDOException $e) {
    // Log the detailed error information
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "DATABASE ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR CODE: " . $e->getCode() . PHP_EOL, FILE_APPEND);
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "ERROR TRACE: " . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
    
    // Check for specific error like duplicate entry
    if ($e->getCode() == 23000) {
        // This is likely a duplicate entry error (e.g., duplicate order_id)
        http_response_code(409); // Conflict
        echo json_encode([
            'success' => false,
            'error' => 'Order ID already exists in database',
            'message' => $e->getMessage(),
            'responseCode' => 202 // Match the API response for already exists
        ]);
    } else {
        // Return general error response
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    // Log general exceptions
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "GENERAL ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
