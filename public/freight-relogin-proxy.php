<?php
// public/freight-relogin-proxy.php

$url = "http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/delhivery-relogin";

$options = [
    "http" => [
        "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
        "method"  => "POST",
        "content" => file_get_contents("php://input"),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

header('Content-Type: application/json');
echo $result; 