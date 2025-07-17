<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\RegisterController as AdminRegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ItemMasterController as AdminItemMasterController;
use App\Http\Controllers\Admin\SalesOrderController as AdminSalesOrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\TrackStatusController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ItemMasterController as ApiItemMasterController;
use App\Http\Controllers\Api\SalesOrderController as ApiSalesOrderController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\UserPdfController;
use App\Http\Controllers\Superadmin\AuthController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\LocationController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Superadmin\ProfileController;
use App\Http\Controllers\Superadmin\ItemMasterController as SuperadminItemMasterController;
use App\Http\Controllers\LocationDashboardController;
use App\Http\Controllers\Api\AsnUpdateController;

// Home route
Route::get('/', function () {
    return view('auth.simple-login');
});

// Combined Login Page
Route::get('/login-page', function () {
    return view('auth.login-page');
})->name('login.page');

// User Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// User Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// Add this route for force completing a picking
Route::post('/pickings/{id}/force-complete', [App\Http\Controllers\DashboardController::class, 'forceComplete'])->name('pickings.force-complete');
// Location User Dashboard Route
Route::get('/location-dashboard', [\App\Http\Controllers\LocationDashboardController::class, 'index'])->name('location.dashboard');
// Location User ASN Upload Route
Route::get('/location-user/asn-upload', [\App\Http\Controllers\LocationDashboardController::class, 'asnUpload'])->name('location.asn.upload');
Route::post('/location-user/asn-upload', [\App\Http\Controllers\LocationDashboardController::class, 'asnUploadPost'])->name('location.asn.upload.post');
// Location User ASN List Route
Route::get('/location-user/asn-list', [\App\Http\Controllers\LocationDashboardController::class, 'asnUploadList'])->name('location.asn.list');
// Location User Replenishment Route
Route::get('/location-user/replenishment', [\App\Http\Controllers\LocationDashboardController::class, 'replenishment'])->name('location.replenishment');
// Location User Replenishment Export Route
Route::get('/location-user/replenishment/export', [\App\Http\Controllers\LocationDashboardController::class, 'exportReplenishmentCsv'])->name('location.replenishment.export');

// Product Routes
Route::resource('products', ProductController::class);

// Sales Order Routes
Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('sales_orders.index');
Route::get('/sales-orders/filter', [SalesOrderController::class, 'filter'])->name('sales_orders.filter');
Route::get('/sales-orders/export', [SalesOrderController::class, 'export'])->name('sales_orders.export');
Route::get('/sales-orders/upload', [SalesOrderController::class, 'showUploadForm'])->name('sales_orders.upload');
Route::post('/sales-orders/process', [SalesOrderController::class, 'processUpload'])->name('sales_orders.process');
Route::post('/sales-orders/save', [SalesOrderController::class, 'saveOrders'])->name('sales_orders.save');

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
    Route::get('/register', [AdminRegisterController::class, 'showRegistrationForm'])->name('admin.register');
    Route::post('/register', [AdminRegisterController::class, 'register']);
    
    // Admin Dashboard Route
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Admin Product Routes (main route for managing items)
    Route::resource('/products', \App\Http\Controllers\Admin\ProductController::class)->names('admin.products');
    Route::post('/products/upload-csv', [\App\Http\Controllers\Admin\ProductController::class, 'uploadCsv'])->name('admin.products.uploadCsv');
    
    // Admin Sales Order Routes
    Route::resource('/sales', AdminSalesOrderController::class)->names('admin.sales');
    
    // Admin User Routes
    Route::resource('/users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users');
    
    // Admin Profile Routes
    Route::get('/profile/edit', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile/update', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('admin.profile.update');
});

// API Routes - manually registered as a workaround
// Use 'api' middleware but explicitly exclude the web middleware group which includes CSRF protection
Route::prefix('api')->middleware('api')->withoutMiddleware(['web', '\App\Http\Middleware\VerifyCsrfToken'])->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
        return $request->user();
    });
    
    // Item Master Routes - Limited to GET only
    Route::get('/item-masters', [ApiItemMasterController::class, 'index']);
    Route::get('/item-masters/{id}', [ApiItemMasterController::class, 'show']);
    
    // Add aliases for items using the new controller
    Route::get('/items', [\App\Http\Controllers\Api\ProductsApiController::class, 'index']);
    Route::get('/items/{id}', [\App\Http\Controllers\Api\ProductsApiController::class, 'show']);
    
    // Sales Order Routes - Limited to GET only
    Route::get('/sales-orders', [ApiSalesOrderController::class, 'index']);
    Route::get('/sales-orders/{id}', [ApiSalesOrderController::class, 'show']);
    
    // Add aliases for sales using the new controller
    Route::get('/sales', [\App\Http\Controllers\Api\SalesApiController::class, 'index']);
    Route::get('/sales/{id}', [\App\Http\Controllers\Api\SalesApiController::class, 'show']);
    
    // Authentication Routes
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [ApiAuthController::class, 'logout']);
    
    // Temporary test route for login without authentication check
    Route::post('/test-login', function (Illuminate\Http\Request $request) {
        return response()->json(['status' => 'success', 'message' => 'Test login successful', 'data' => $request->all()]);
    });

    // Picking Routes
    Route::post('/pickings', [\App\Http\Controllers\Api\PickingController::class, 'store']);

    // ASN Uploads API Route
    Route::get('/asn-uploads', [\App\Http\Controllers\Api\AsnUploadController::class, 'index']);
});

// Freight Estimation Page
Route::get('/freight', function () {
    return redirect('/freight.html');
})->name('freight');

// Freight Cost Calculation Routes
Route::get('/freight-calculator', [App\Http\Controllers\FreightController::class, 'index'])->name('freight.calculator');

// Direct access routes for HTML files in public directory
Route::get('/freight.html', function () {
    return redirect()->route('freight.calculator');
});

Route::get('/create-order', [App\Http\Controllers\FreightController::class, 'createOrder'])->name('create.order');
Route::get('/freight-calculator/get-box-details', [App\Http\Controllers\FreightController::class, 'getBoxDetails'])->name('freight.getBoxDetails');

// Packlist Generation Routes
Route::get('/packlist', [App\Http\Controllers\PicklistController::class, 'index'])->name('packlist.index');
Route::get('/packlist/get-boxes', [App\Http\Controllers\PicklistController::class, 'getBoxes'])->name('packlist.getBoxes');
Route::post('/packlist/generate', [App\Http\Controllers\PicklistController::class, 'generate'])->name('packlist.generate');
Route::get('/packlist/print/{so_no}/{box?}', [App\Http\Controllers\PicklistController::class, 'print'])->name('packlist.print');

// Improved Freight Estimation Proxy with CSRF exemption and proper error handling
Route::post('/freight-proxy', function (\Illuminate\Http\Request $request) {
    $client = new \GuzzleHttp\Client([
        'timeout' => 30,  // Increased timeout for API calls
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]
    ]);
    
    try {
        // Forward all request data to the target API
        $response = $client->post('https://agro-rpa.onrender.com/get-freight-estimates', [
            'json' => $request->all(),
            'http_errors' => false, // Don't throw exceptions for HTTP errors
        ]);
        
        // Return the API response with appropriate status code and headers
        return response($response->getBody()->getContents(), $response->getStatusCode())
            ->header('Content-Type', 'application/json')
            ->header('Access-Control-Allow-Origin', '*'); // Allow CORS
            
    } catch (\Exception $e) {
        // Handle and log any exceptions
        \Log::error('Freight API error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Error connecting to freight estimation service',
            'message' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
})->withoutMiddleware(['\App\Http\Middleware\VerifyCsrfToken']);

// CORS preflight handler for the freight proxy
Route::options('/freight-proxy', function () {
    return response()->make('', 200, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Accept, X-Requested-With',
        'Access-Control-Max-Age' => '86400',
    ]);
})->withoutMiddleware(['\App\Http\Middleware\VerifyCsrfToken']);

// Track Status Routes
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('pdfs', [UserPdfController::class, 'index'])->name('pdfs.index');
    Route::post('pdfs', [UserPdfController::class, 'store'])->name('pdfs.store');
    Route::get('pdfs/download/{id}', [UserPdfController::class, 'download'])->name('pdfs.download');
    Route::post('pdfs/save-to-sales-orders', [UserPdfController::class, 'saveToSalesOrders'])->name('pdfs.save-to-sales-orders');
});
Route::get('/track-status', [TrackStatusController::class, 'index'])->name('track.status');
Route::post('/track-status-proxy', [TrackStatusController::class, 'trackStatus'])->name('track.status.proxy')->withoutMiddleware(['\App\Http\Middleware\VerifyCsrfToken']);
Route::options('/track-status-proxy', function () {
    return response()->make('', 200, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Accept, X-Requested-With',
        'Access-Control-Max-Age' => '86400',
    ]);
})->withoutMiddleware(['\App\Http\Middleware\VerifyCsrfToken']);

// Superadmin Auth and Dashboard routes
Route::get('/superadmin/create-credential', [AuthController::class, 'showCreateForm'])->name('superadmin.create-credential');
Route::post('/superadmin/create-credential', [AuthController::class, 'createCredential']);
Route::get('/superadmin/login', [AuthController::class, 'showLoginForm'])->name('superadmin.login');
Route::post('/superadmin/login', [AuthController::class, 'login']);
Route::get('/superadmin/dashboard', [SuperadminDashboardController::class, 'index'])->name('superadmin.dashboard');

// Superadmin Location Routes
Route::prefix('superadmin')->name('superadmin.')->group(function() {
    Route::resource('locations', LocationController::class);
    Route::get('profile/edit', [\App\Http\Controllers\Superadmin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile/update', [\App\Http\Controllers\Superadmin\ProfileController::class, 'update'])->name('profile.update');
    Route::get('users', [\App\Http\Controllers\Superadmin\UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [\App\Http\Controllers\Superadmin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Superadmin\UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [\App\Http\Controllers\Superadmin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [\App\Http\Controllers\Superadmin\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [\App\Http\Controllers\Superadmin\UserController::class, 'destroy'])->name('users.destroy');
    Route::resource('item-masters', SuperadminItemMasterController::class);
    Route::resource('racks', App\Http\Controllers\Superadmin\RackController::class);
    Route::post('racks/upload', [App\Http\Controllers\Superadmin\RackController::class, 'upload'])->name('racks.upload');
     // report generation
    Route::get('reports', [App\Http\Controllers\Superadmin\ReportController::class, 'index'])->name('reports.index');

    Route::get('reports/export', [App\Http\Controllers\Superadmin\ReportController::class, 'export'])->name('reports.export');
});



// Route to call the public/update_asn.php script directly
Route::post('/api/update-asn', function () {
    include public_path('update_asn.php');
});

Route::post('/update-asn', [AsnUpdateController::class, 'update']);
