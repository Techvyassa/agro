<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemMasterController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Use the new controllers specifically for items and sales endpoints
use App\Http\Controllers\Api\ProductsApiController;
use App\Http\Controllers\Api\SalesApiController;

// ItemMaster routes - only GET methods as per user request
// Remove leading slashes as API routes are automatically prefixed
Route::get('items', [ItemMasterController::class, 'index']);
Route::get('items/{id}', [ItemMasterController::class, 'show']);
Route::get('item-masters', [ItemMasterController::class, 'index']);
Route::get('item-masters/{id}', [ItemMasterController::class, 'show']);

// SalesOrder routes - only GET methods as per user request
// Remove leading slashes as API routes are automatically prefixed
Route::get('sales', [SalesOrderController::class, 'index']);
Route::get('sales/{so_no}', [SalesOrderController::class, 'show']);
Route::get('sales-orders', [SalesOrderController::class, 'index']);
Route::get('sales-orders/{so_no}', [SalesOrderController::class, 'show']);

// Authentication routes
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Picking routes - POST request using original controller
Route::post('pickings', function (\Illuminate\Http\Request $request) {
    try {
        // Validate the request
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'box' => 'required|string',
            'so_no' => 'required|string',
            'items' => 'required|array',
            'items.*' => 'string',
            'dimension' => 'nullable|string',
            'weight' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if pickings table has the necessary columns using DB facade
        $hasColumns = true;
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('pickings');
        $required_columns = ['box', 'so_no', 'items'];
        
        foreach ($required_columns as $column) {
            if (!in_array($column, $columns)) {
                $hasColumns = false;
                break;
            }
        }
        
        if (!$hasColumns) {
            // Columns are missing, run the migration
            \Illuminate\Support\Facades\Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_05_06_add_so_no_to_pickings_table.php',
                '--force' => true
            ]);
        }
        
        // Create a new picking
        $picking = new \App\Models\Picking();
        $picking->box = $request->box;
        $picking->so_no = $request->so_no;
        $picking->items = $request->items;
        
        if (in_array('dimension', $columns)) {
            $picking->dimension = $request->dimension;
        }
        
        if (in_array('weight', $columns)) {
            $picking->weight = $request->weight;
        }
        
        $picking->save();

        return response()->json([
            'success' => true,
            'message' => 'Picking created successfully',
            'data' => $picking
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create picking',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Clear, simple route for handling GET pickings
Route::get('get-pickings', function (\Illuminate\Http\Request $request) {
    // Validate input
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'box' => 'required|string',
        'so_no' => 'required|string',
        'items' => 'required|array',
        'items.*' => 'string',
        'dimension' => 'nullable|string',
        'weight' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Create new picking
        $picking = \App\Models\Picking::create([
            'box' => $request->box,
            'so_no' => $request->so_no,
            'items' => $request->items,
            'dimension' => $request->dimension,
            'weight' => $request->weight,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Picking created successfully',
            'data' => $picking
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create picking',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Freight Estimation Proxy - to handle CORS issues
Route::post('freight-proxy', function (\Illuminate\Http\Request $request) {
    $client = new \GuzzleHttp\Client();
    
    try {
        $response = $client->post('https://agro-rpa.onrender.com/get-freight-estimates', [
            'json' => $request->all(),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        
        return response($response->getBody(), $response->getStatusCode())
            ->header('Content-Type', 'application/json');
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});
