<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\Picking;
use Illuminate\Support\Facades\DB;

class SalesPickingsController extends Controller
{
    /**
     * Get all sales orders with their picking status
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesOrdersWithPickings(Request $request)
    {
        try {
            // Build the query
            $query = DB::table('sales_orders')
                ->select(
                    'sales_orders.*',
                    'pickings.status as picking_status',
                    'pickings.box',
                    'pickings.dimension',
                    'pickings.weight'
                )
                ->leftJoin('pickings', 'sales_orders.so_no', '=', 'pickings.so_no');
            
            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('pickings.status', $request->status);
            }
            
            // Execute the query
            $salesOrdersWithPickings = $query->get();

            return response()->json([
                'success' => true,
                'data' => $salesOrdersWithPickings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sales orders with picking status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
