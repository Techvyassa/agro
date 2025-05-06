<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;

// Special handler for GET pickings
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($requestUri, '/api/get-pickings') === 0) {
    // Initialize Laravel to use models - this must be done first for DB to work
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
        // Extract so_no from query
        $so_no = $_GET['so_no'] ?? null;
        
        // Find pickings by so_no
        if ($so_no) {
            // Fetch pickings matching the so_no
            $pickings = \App\Models\Picking::where('so_no', $so_no)->get();
            
            if ($pickings->count() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pickings retrieved successfully',
                    'data' => $pickings
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No pickings found for sales order: ' . $so_no,
                    'data' => []
                ]);
            }
        } else {
            // No so_no provided, return all pickings (with limit)
            $pickings = \App\Models\Picking::orderBy('created_at', 'desc')->limit(50)->get();
            
            echo json_encode([
                'success' => true,
                'message' => 'All pickings retrieved',
                'data' => $pickings
            ]);
        }
        
        exit;
        
    } catch (Exception $e) {
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => 'Failed to retrieve pickings',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
