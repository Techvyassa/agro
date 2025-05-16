<?php
// Set headers to allow CORS and specify JSON response
header('Content-Type: application/json');

// Database credentials - adjust these to match your XAMPP setup
$host = 'localhost';
$dbname = 'agro_freight';
$username = 'root';
$password = '';

// Initialize log file
$logFile = 'db_migration.log';
file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Starting migration process\n", FILE_APPEND);

try {
    // Create database connection
    $dsn = "mysql:host=$host;charset=UTF8";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, check if the database exists, if not create it
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Database selected: $dbname\n", FILE_APPEND);
    
    // Create orders table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` VARCHAR(50) NOT NULL,
        `order_date` DATE NOT NULL,
        `pickup_id` VARCHAR(50) DEFAULT NULL,
        `pickup_city` VARCHAR(100) DEFAULT NULL,
        `pickup_state` VARCHAR(100) DEFAULT NULL,
        `return_id` VARCHAR(50) DEFAULT NULL,
        `return_city` VARCHAR(100) DEFAULT NULL,
        `return_state` VARCHAR(100) DEFAULT NULL,
        `invoice_amount` DECIMAL(10,2) DEFAULT NULL,
        `item_name` VARCHAR(255) DEFAULT NULL,
        `cod_amount` DECIMAL(10,2) DEFAULT NULL,
        `quantity` INT(11) DEFAULT NULL,
        `buyer_name` VARCHAR(255) DEFAULT NULL,
        `buyer_email` VARCHAR(255) DEFAULT NULL,
        `buyer_address` TEXT DEFAULT NULL,
        `buyer_phone` VARCHAR(20) DEFAULT NULL,
        `buyer_pincode` VARCHAR(20) DEFAULT NULL,
        `order_status` ENUM('SUCCESS', 'FAILED', 'PENDING') DEFAULT 'PENDING',
        `response_code` INT(11) DEFAULT NULL,
        `response_message` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME DEFAULT NULL,
        `raw_request` TEXT DEFAULT NULL,
        `raw_response` TEXT DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Orders table created or verified\n", FILE_APPEND);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Migration completed successfully. The orders table has been created.'
    ]);
    
} catch (PDOException $e) {
    // Log the error
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "DATABASE ERROR: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Migration failed: ' . $e->getMessage()
    ]);
}
?>
