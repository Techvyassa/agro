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
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ItemMasterController as ApiItemMasterController;
use App\Http\Controllers\Api\SalesOrderController as ApiSalesOrderController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;

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
});
