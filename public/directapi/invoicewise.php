<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

$host = "192.250.231.31";
$dbname = "vyassa44_agro";
$username = "vyassa44_agro";
$password = "RoyalK1234";

header("Content-Type: application/json");

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

if (
    isset($_GET['location_id']) &&
    isset($_GET['invoice_number']) &&
    isset($_GET['status'])
) {
    $location_id = intval($_GET['location_id']);
    $invoice_number = $conn->real_escape_string(trim($_GET['invoice_number']));
    $status = $conn->real_escape_string(trim($_GET['status']));

    // Count total records (for debug only)
    $count_query = "SELECT COUNT(*) as total FROM asn_uploads WHERE location_id = $location_id AND TRIM(invoice_number) = TRIM('$invoice_number') AND status = '$status'";
    $count_result = $conn->query($count_query);
    $total = ($count_result && $row = $count_result->fetch_assoc()) ? intval($row['total']) : 0;

    // Fetch all matching records (no pagination)
    $query = "SELECT * FROM asn_uploads WHERE location_id = $location_id AND TRIM(invoice_number) = TRIM('$invoice_number') AND status = '$status'";
    $debug = [
        "query" => $query,
        "total" => $total
    ];

    $result = $conn->query($query);

    if (!$result) {
        echo json_encode([
            "success" => false,
            "message" => "Query failed: " . $conn->error,
            "debug" => $debug
        ]);
        exit;
    }

    $asnUploads = [];
    while ($row = $result->fetch_assoc()) {
        $asnUploads[] = utf8ize($row);
    }

    file_put_contents("debug_data.json", json_encode($asnUploads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $debug["rows_found"] = count($asnUploads);
    $console_log = "console.log('Query: " . addslashes($query) . " | Rows found: " . $debug["rows_found"] . " | Total: $total');";

    if (isset($_GET['html']) && $_GET['html'] == '1') {
        header('Content-Type: text/html');
        echo "<html><head><title>Debug</title></head><body><h3>Debug Mode</h3><p>Check browser console.</p><script>$console_log</script></body></html>";
        exit;
    }

    $response = [
        "success" => true,
        "data" => $asnUploads,
        "debug" => $debug,
        "console_log" => $console_log
    ];

    if (ob_get_length()) ob_end_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Missing required parameters: location_id, invoice_number, or status."
    ]);
}

$conn->close();

function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
    }
    return $mixed;
}
?>
