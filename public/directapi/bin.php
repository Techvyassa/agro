<?php
// Database configuration
$host = "192.250.231.31";
$dbname = "vyassa44_agro";
$username = "vyassa44_agro";
$password = "RoyalK1234";

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Set headers for JSON output
header("Content-Type: application/json");

// Query to fetch all records from bin_location table
$query = "SELECT id, bin_name, sequence FROM bin_location";

// Execute the query
$result = $conn->query($query);

// Check if any records are found
if ($result->num_rows > 0) {
    // Create an array to store the results
    $binLocations = [];
    
    // Fetch all records into the array
    while ($row = $result->fetch_assoc()) {
        $binLocations[] = $row;
    }

    // Return the records as a JSON response
    echo json_encode([
        "success" => true,
        "data" => $binLocations
    ]);
} else {
    // If no records found, return an error message
    echo json_encode([
        "success" => false,
        "message" => "No bin locations found."
    ]);
}

// Close the database connection
$conn->close();
?>
