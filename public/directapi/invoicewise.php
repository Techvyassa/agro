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

// Check if all required parameters are provided in the GET request
if (
    isset($_GET['location_id']) &&
    isset($_GET['invoice_number']) &&
    isset($_GET['status'])
) {
    // Get and sanitize input data
    $location_id = intval($_GET['location_id']);  // Sanitize as integer
    $invoice_number = $conn->real_escape_string($_GET['invoice_number']);  // Sanitize as string
    $status = $conn->real_escape_string($_GET['status']);  // Sanitize as string

    // Query to check if the combination of location_id, invoice_number, and status exists
    $query = "SELECT * FROM asn_uploads WHERE location_id = $location_id AND invoice_number = '$invoice_number' AND status = '$status'";

    // Execute the query
    $result = $conn->query($query);

    // Check if any record is found
    if ($result->num_rows > 0) {
        // Create an array to store the results
        $asnUploads = [];
        
        // Fetch all matching records into the array
        while ($row = $result->fetch_assoc()) {
            $asnUploads[] = $row;
        }

        // Return the records as a JSON response
        echo json_encode([
            "success" => true,
            "data" => $asnUploads
        ]);
    } else {
        // If no records found, return an error message
        echo json_encode([
            "success" => false,
            "message" => "No records found for the given parameters."
        ]);
    }
} else {
    // If any required parameter is missing
    echo json_encode([
        "success" => false,
        "message" => "Missing required parameters: location_id, invoice_number, or status."
    ]);
}

// Close the database connection
$conn->close();
?>
