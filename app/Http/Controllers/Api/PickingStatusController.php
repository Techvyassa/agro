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
}
