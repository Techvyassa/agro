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

if (
    isset($data['location_id']) &&
    isset($data['invoice_number']) &&
    isset($data['part_no']) &&
    isset($data['transfer_qty']) &&
    isset($data['rack']) // Ensure 'rack' is included
) {
    $location_id = $conn->real_escape_string($data['location_id']);
    $invoice_number = $conn->real_escape_string($data['invoice_number']);
    $part_no = $conn->real_escape_string($data['part_no']);
    $transfer_qty = intval($data['transfer_qty']);
    $rack = $conn->real_escape_string($data['rack']); // Sanitize the rack value

    // Check if record exists in asn_uploads
    $checkQuery = "SELECT inward_qty, transfer_qty FROM asn_uploads WHERE invoice_number = '$invoice_number' AND part_no = '$part_no'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_inward_qty = intval($row['inward_qty']);
        $current_transfer_qty = intval($row['transfer_qty']);

        // Calculate the total transfer quantity after adding the new transfer_qty
        $new_transfer_qty = $current_transfer_qty + $transfer_qty;

        // Check if the new transfer_qty exceeds inward_qty
        if ($new_transfer_qty > $current_inward_qty) {
            echo json_encode([
                "success" => false,
                "message" => "Transfer quantity exceeds inward quantity."
            ]);
            exit;
        }

        // Insert into transfer table (including the rack field)
        $insertQuery = "INSERT INTO transfer (location_id, invoice_number, part_no, transfer_qty, rack) 
                        VALUES ('$location_id', '$invoice_number', '$part_no', $transfer_qty, '$rack')";
        if ($conn->query($insertQuery) === TRUE) {

            // Update transfer_qty in asn_uploads
            $updateQuery = "UPDATE asn_uploads 
                            SET transfer_qty = $new_transfer_qty 
                            WHERE invoice_number = '$invoice_number' AND part_no = '$part_no'";

            if ($conn->query($updateQuery) === TRUE) {
                echo json_encode([
                    "success" => true,
                    "message" => "Transfer recorded and asn_uploads updated successfully.",
                    "new_transfer_qty" => $new_transfer_qty
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Insert succeeded but update failed: " . $conn->error
                ]);
            }

        } else {
            echo json_encode([
                "success" => false,
                "message" => "Insert into transfer table failed: " . $conn->error
            ]);
        }

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Record not found in asn_uploads."
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
