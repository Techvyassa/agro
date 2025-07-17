<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$url = "http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/delhivery-relogin";

// Get POST data
$postData = file_get_contents("php://input");

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/x-www-form-urlencoded"
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["error" => curl_error($ch)]);
} else {
    header('Content-Type: application/json');
    echo $response;
}

curl_close($ch); 