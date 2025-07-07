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

    // Pagination parameters
    $per_page_all = isset($_GET['per_page']) && $_GET['per_page'] === 'all';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = ($per_page_all) ? null : (isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 50);
    $offset = ($page - 1) * ($per_page ? $per_page : 1);

    // Query to count total matching records
    $count_query = "SELECT COUNT(*) as total FROM asn_uploads WHERE location_id = $location_id AND TRIM(invoice_number) = TRIM('$invoice_number') AND status = '$status'";
    $count_result = $conn->query($count_query);
    $total = 0;
    if ($count_result && $row = $count_result->fetch_assoc()) {
        $total = intval($row['total']);
    }

    // Query to fetch results (all or paginated)
    if ($per_page_all) {
        $query = "SELECT * FROM asn_uploads WHERE location_id = $location_id AND TRIM(invoice_number) = TRIM('$invoice_number') AND status = '$status'";
    } else {
        $query = "SELECT * FROM asn_uploads WHERE location_id = $location_id AND TRIM(invoice_number) = TRIM('$invoice_number') AND status = '$status' LIMIT $per_page OFFSET $offset";
    }

    // Debug: Print the query and number of rows to the browser console
    $debug = [
        "query" => $query,
        "rows_found" => null,
        "page" => $per_page_all ? null : $page,
        "per_page" => $per_page_all ? 'all' : $per_page,
        "offset" => $per_page_all ? null : $offset,
        "total" => $total
    ];

    // Execute the query
    $result = $conn->query($query);

    // Set debug row count
    $debug["rows_found"] = $result ? $result->num_rows : 0;

    // Prepare console log string
    $console_log = "console.log('Query: ".addslashes($query)." | Rows found: ".$debug["rows_found"]." | Page: ".($per_page_all ? 'all' : $page)." | Per page: ".($per_page_all ? 'all' : $per_page)." | Total: $total');";

    // Check if the user explicitly requests HTML (by passing ?html=1)
    $wants_html = isset($_GET['html']) && $_GET['html'] == '1';

    if ($wants_html) {
        // Return HTML with debug info in the console
        header('Content-Type: text/html');
        echo "<html><head><title>Debug Output</title></head><body><h2>API Debug Output</h2><p>Check your browser console for details.</p><script>" . $console_log . "</script></body></html>";
        exit;
    }

    // Check if any record is found
    if ($result && $result->num_rows > 0) {
        // Create an array to store the results
        $asnUploads = [];
        
        // Fetch all matching records into the array
        while ($row = $result->fetch_assoc()) {
            $asnUploads[] = $row;
        }

        // Return the records as a JSON response with debug info and pagination
        echo json_encode([
            "success" => true,
            "data" => $asnUploads,
            "pagination" => $per_page_all ? null : [
                "page" => $page,
                "per_page" => $per_page,
                "total" => $total,
                "total_pages" => ceil($total / $per_page)
            ],
            "debug" => $debug,
            "console_log" => $console_log
        ]);
    } else {
        // If no records found, return an error message with debug info and pagination
        echo json_encode([
            "success" => false,
            "message" => "No records found for the given parameters.",
            "pagination" => $per_page_all ? null : [
                "page" => $page,
                "per_page" => $per_page,
                "total" => $total,
                "total_pages" => ceil($total / $per_page)
            ],
            "debug" => $debug,
            "console_log" => $console_log
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
