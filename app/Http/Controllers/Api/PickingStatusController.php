<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PickingStatusController extends Controller
{
    /**
     * Update the status of a picking based on so_no
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'so_no' => 'required|string',
            'status' => 'required|string',
        ]);

        $so_no = $request->input('so_no');
        $status = $request->input('status');

        $picking = \App\Models\Picking::where('so_no', $so_no)->first();

        if (!$picking) {
            return response()->json([
                'success' => false,
                'message' => 'Picking not found for the given SO number',
            ], 404);
        }

        $picking->status = $status;
        $picking->save();

        return response()->json([
            'success' => true,
            'message' => 'Picking status updated successfully',
            'data' => $picking,
        ]);
    }
    
    /**
     * Static method to update picking status - used by fallback routes and direct API access
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function updatePickingStatus(Request $request)
    {
        // Add CORS headers
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Content-Type' => 'application/json'
        ];
        
        // Handle preflight OPTIONS request
        if ($request->method() === 'OPTIONS') {
            return response('', 200)->withHeaders($headers);
        }
        
        // Get data from various possible sources
        $postData = json_decode($request->getContent(), true);
        if (!$postData && $request->method() === 'POST' && !empty($request->post())) {
            $postData = $request->post();
        } elseif (!$postData && $request->method() === 'GET' && !empty($request->query())) {
            $postData = $request->query();
        }
        
        // Validate required fields
        if (!isset($postData['so_no']) || !isset($postData['status'])) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields: so_no and status are required'
            ], 422)->withHeaders($headers);
        }
        
        $so_no = $postData['so_no'];
        $status = $postData['status'];
        
        // Find the picking by so_no
        $picking = \App\Models\Picking::where('so_no', $so_no)->first();
        
        if (!$picking) {
            return response()->json([
                'success' => false,
                'message' => 'Picking not found for the given SO number: ' . $so_no,
            ], 404)->withHeaders($headers);
        }
        
        // Update the status
        $picking->status = $status;
        $picking->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Picking status updated successfully for SO number: ' . $so_no,
            'data' => $picking,
        ], 200)->withHeaders($headers);
    }
}
