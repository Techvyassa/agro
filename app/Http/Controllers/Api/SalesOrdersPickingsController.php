<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrdersPickingsController extends Controller
{
    /**
     * Get sales orders joined with their picking data
     * Filters by status if provided in the request
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Start building the query with the join
            $query = DB::table('sales_orders')
                ->select(
                    'sales_orders.*',
                    'pickings.status as picking_status',
                    'pickings.box',
                    'pickings.dimension',
                    'pickings.weight'
                )
                ->leftJoin('pickings', 'sales_orders.so_no', '=', 'pickings.so_no');
            
            // Apply status filter if provided
            if ($request->has('status')) {
                $query->where('pickings.status', $request->status);
            }
            
            // Execute the query and get results
            $results = $query->get();
            
            // Return JSON response
            return response()->json([
                'success' => true,
                'count' => count($results),
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales orders with pickings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
