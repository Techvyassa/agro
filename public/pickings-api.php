<?php

// This file serves as a direct endpoint for both GET and POST requests to the pickings API
// It bypasses the Laravel routing system to ensure both methods work

// Initialize Laravel
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request from current server variables
$request = Illuminate\Http\Request::capture();

// Access the current request method
$method = $_SERVER['REQUEST_METHOD'];

// If it's a GET request, transform it to work with the pickings controller
if ($method === 'GET') {
    // Transfer GET parameters to the request
    $items = isset($_GET['items']) ? $_GET['items'] : [];
    
    // Create a controller instance
    $controller = new \App\Http\Controllers\Api\PickingController();
    
    // Make the request that will be passed to the controller
    $params = [
        'box' => $_GET['box'] ?? '',
        'so_no' => $_GET['so_no'] ?? '',
        'items' => $items,
        'dimension' => $_GET['dimension'] ?? null,
        'weight' => $_GET['weight'] ?? null,
    ];
    
    // Create a new request with these parameters
    $newRequest = new Illuminate\Http\Request();
    $newRequest->replace($params);
    
    // Call the controller's store method
    $response = $controller->store($newRequest);
    
    // Output the response
    header('Content-Type: application/json');
    echo $response->getContent();
}
else {
    // For POST and other methods, let Laravel handle it normally
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
}
