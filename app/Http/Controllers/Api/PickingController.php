<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PickingController extends Controller
{
    /**
     * Explicitly handles both GET and POST methods for creating a picking.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // Works for both GET and POST methods
        // Validate the request 
        $validator = Validator::make($request->all(), [
            'box' => 'required|string',
            'so_no' => 'required|string',  // Validate the sales order number
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
            // Create a new picking - same logic for both GET and POST
            $picking = Picking::create([
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
    }
    
    /**
     * Original store method - preserved for compatibility
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        return $this->__invoke($request);
    }
}
