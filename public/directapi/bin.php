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

// Check if bin_name is provided in the GET request
if (isset($_GET['bin_name'])) {
    $bin_name = $conn->real_escape_string($_GET['bin_name']);  // Sanitize the input

    // Query to fetch sequence for the given bin_name
    $query = "SELECT sequence FROM bin_location WHERE bin_name = '$bin_name'";

    // Execute the query
    $result = $conn->query($query);

    // Check if any record is found for the provided bin_name
    if ($result->num_rows > 0) {
        // Fetch the result
        $row = $result->fetch_assoc();
        $sequence = $row['sequence'];

        // Return the sequence as a JSON response
        echo json_encode([
            "success" => true,
            "bin_name" => $bin_name,
            "sequence" => $sequence
        ]);
    } else {
        // If no record found for the provided bin_name
        echo json_encode([
            "success" => false,
            "message" => "Bin name not found."
        ]);
    }
} else {
    // If bin_name is not provided
    echo json_encode([
        "success" => false,
        "message" => "Missing bin_name parameter."
    ]);
}

// Close the database connection
$conn->close();
?>
