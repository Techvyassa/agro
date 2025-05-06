<?php

// Initialize Laravel to use models
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap Laravel application to get database working
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = \Illuminate\Http\Request::capture()
);

// Set content type
header('Content-Type: application/json');

try {
    // Fetch some sample pickings with their weights
    $pickings = \App\Models\Picking::select('id', 'so_no', 'box', 'weight', 'dimension')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
        
    // Output the results
    echo json_encode([
        'success' => true,
        'data' => $pickings,
        'weights' => $pickings->pluck('weight')->toArray()
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
