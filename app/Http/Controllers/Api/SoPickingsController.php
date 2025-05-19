<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SalesOrder;
use App\Models\Picking;

class SoPickingsController extends Controller
{
    /**
     * Get sales orders with their pickings
     * Handles the many-to-many relationship properly
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get status parameter
            $status = $request->input('status');
            
            // Get all distinct SO numbers
            $soNumbers = DB::table('sales_orders')
                ->select('so_no')
                ->distinct()
                ->get()
                ->pluck('so_no');
            
            $result = [];
            
            // Process each SO number
            foreach ($soNumbers as $soNumber) {
                // Get all items for this SO
                $items = DB::table('sales_orders')
                    ->where('so_no', $soNumber)
                    ->get();
                
                // Get all pickings for this SO, with optional status filter
                $pickingsQuery = DB::table('pickings')
                    ->where('so_no', $soNumber);
                
                // Apply status filter if provided
                if ($status) {
                    $pickingsQuery->where('status', $status);
                }
                
                $pickings = $pickingsQuery->get();
                
                // Only include in results if we have pickings after filtering
                if ($pickings->count() > 0) {
                    $result[] = [
                        'so_no' => $soNumber,
                        'items' => $items,
                        'pickings' => $pickings
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'count' => count($result),
                'filtered_by' => $status ? "status=$status" : null,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales orders with pickings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
