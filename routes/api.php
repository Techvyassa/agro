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

// Picking route - explicitly configured for API requests
Route::post('pickings', [\App\Http\Controllers\Api\PickingController::class, 'store'])->middleware('api');

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
