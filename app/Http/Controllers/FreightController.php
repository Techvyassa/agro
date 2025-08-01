<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreightController extends Controller
{
    /**
     * Show the freight calculation form.
     */
    public function index()
    {
        // Check if this is a direct access to the HTML file instead of the route
        if (request()->is('freight.html')) {
            return redirect()->route('freight.calculator');
        }
        // Get all unique SO numbers from pickings where status is completed or force_completed
        $so_numbers = Picking::whereIn('status', ['completed', 'force_completed'])
            ->select('so_no')
            ->distinct()
            ->orderBy('so_no')
            ->pluck('so_no');

        return view('freight.calculate', compact('so_numbers'));
    }
    
    /**
     * Get all boxes and their details for a specific sales order
     */
    /**
     * Show the create order form.
     * This handles the route to create an order from the freight calculator.
     */
    public function createOrder(Request $request)
    {
        // Get all URL parameters to pass to the view
        $params = $request->all();
        
        // Return the view with the URL parameters
        return view('freight.create-order', compact('params'));
    }
    
    public function getBoxDetails(Request $request)
    {
        $so_no = $request->get('so_no');
        
        if (!$so_no) {
            return response()->json([
                'success' => false,
                'message' => 'Sales order number is required'
            ]);
        }
        
        try {
            // Log the request for debugging
            Log::info('Fetching boxes for SO: ' . $so_no);
            
            // Fetch all boxes with their dimensions and weights for this sales order - using the same approach as PicklistController
            $boxes = Picking::where('so_no', $so_no)
                ->select('box', 'dimension', 'weight', 'items')
                ->get();
                
            // Log the retrieved data
            Log::info('Boxes retrieved: ', ['count' => count($boxes), 'data' => $boxes->toArray()]);
                
            if ($boxes->isEmpty()) {
                Log::warning('No boxes found for SO: ' . $so_no);
                return response()->json([
                    'success' => false,
                    'message' => 'No boxes found for this sales order'
                ]);
            }
            
            // Calculate total weight - ensure it's numeric
            $totalWeight = 0;
            foreach ($boxes as $box) {
                // Log weight value for debugging
                Log::info('Box weight value:', ['box' => $box->box, 'weight' => $box->weight, 'type' => gettype($box->weight)]);
                
                if (is_numeric($box->weight)) {
                    $totalWeight += (float)$box->weight;
                } else {
                    // Try to extract numeric value if weight contains non-numeric chars
                    if (preg_match('/([0-9.]+)/', $box->weight, $matches)) {
                        $totalWeight += (float)$matches[1];
                    }
                }
            }
            
            Log::info('Calculated total weight:', ['totalWeight' => $totalWeight]);
            
            // Collect all dimensions - ensure they're formatted properly
            $dimensions = [];
            foreach ($boxes as $box) {
                if (!empty($box->dimension)) {
                    $dimensions[] = $box->dimension;
                }
            }
            $dimensionsStr = implode(', ', $dimensions);
            
            // Create formatted box data for the front-end
            $formattedBoxes = [];
            foreach ($boxes as $box) {
                // Ensure weight is handled properly
                $weight = 0;
                if (!empty($box->weight)) {
                    if (is_numeric($box->weight)) {
                        $weight = (float)$box->weight;
                    } elseif (preg_match('/([0-9.]+)/', $box->weight, $matches)) {
                        $weight = (float)$matches[1];
                    }
                }
                
                $formattedBoxes[] = [
                    'box' => $box->box,
                    'dimension' => $box->dimension,
                    'weight' => $weight,
                    'items' => $box->items
                ];
            }
            
            return response()->json([
                'success' => true,
                'boxes' => $formattedBoxes,
                'totalWeight' => $totalWeight,
                'dimensions' => $dimensionsStr
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching box details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching box details: ' . $e->getMessage()
            ]);
        }
    }
}
