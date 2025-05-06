<?php

use Illuminate\Support\Facades\Route;

// This route file is specifically created to handle GET requests for pickings
// Separate from the main api.php file to avoid conflicts

Route::get('api/pickings', function (\Illuminate\Http\Request $request) {
    $controller = new \App\Http\Controllers\Api\PickingController();
    return $controller->store($request);
});
