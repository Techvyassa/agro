<?php
// Database configuration
$host = "192.250.231.31";
$dbname = "vyassa44_agro";
$username = "vyassa44_agro";
$password = "RoyalK1234";

// Set headers
header("Content-Type: application/json");

// Create DB connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]));
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if required fields are provided
if (
    isset($data['invoice_number']) &&
    isset($data['part_no']) &&
    isset($data['status']) &&
    isset($data['inward_qty']) &&
    isset($data['location_id']) // Check for location_id
) {
    $invoice_number = $conn->real_escape_string($data['invoice_number']);
    $part_no = $conn->real_escape_string($data['part_no']);
    $status = $conn->real_escape_string($data['status']);
    $inward_qty = intval($data['inward_qty']);
    $location_id = intval($data['location_id']); // Capture location_id

    // Check if the location_id exists in the database
    $checkLocationQuery = "SELECT id FROM locations WHERE id = $location_id";
    $locationResult = $conn->query($checkLocationQuery);

    if ($locationResult->num_rows > 0) {
        // Location ID exists, proceed with checking the record
        $checkQuery = "SELECT inward_qty FROM asn_uploads WHERE invoice_number = '$invoice_number' AND part_no = '$part_no'";
        $result = $conn->query($checkQuery);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_qty = intval($row['inward_qty']);
            $new_qty = $current_qty + $inward_qty;

            // Update the record with the new summed quantity
            $updateQuery = "UPDATE asn_uploads SET status = '$status', inward_qty = $new_qty WHERE invoice_number = '$invoice_number' AND part_no = '$part_no'";
            if ($conn->query($updateQuery) === TRUE) {
                echo json_encode([
                    "success" => true,
                    "message" => "Record updated successfully. New inward_qty: $new_qty"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Update failed: " . $conn->error
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Record not found."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid location_id. Location does not exist."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields."
    ]);
}

$conn->close();
?>
