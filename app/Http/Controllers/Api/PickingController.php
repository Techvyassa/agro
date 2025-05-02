<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PickingController extends Controller
{
    /**
     * Store a newly created picking in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'box' => 'required|string',
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
            // Create a new picking
            $picking = Picking::create([
                'box' => $request->box,
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
}
