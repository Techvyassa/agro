<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PicklistController extends Controller
{
    /**
     * Display the packlist generation page
     */
    public function index()
    {
        // Get all unique SO numbers from the pickings table
        $so_numbers = Picking::select('so_no')->distinct()->orderBy('so_no')->pluck('so_no');
        
        return view('packlist.index', compact('so_numbers'));
    }

    /**
     * Get boxes for a specific SO number via AJAX
     */
    public function getBoxes(Request $request)
    {
        $so_no = $request->so_no;
        
        if (empty($so_no)) {
            return response()->json(['error' => 'SO number is required'], 400);
        }

        $boxes = Picking::where('so_no', $so_no)
                      ->select('box', 'dimension', 'weight')
                      ->distinct('box')
                      ->get();
                      
        // Log the retrieved boxes data for debugging
        Log::info('Boxes for SO: ' . $so_no, ['count' => count($boxes), 'data' => $boxes]);
        
        return response()->json($boxes);
    }

    /**
     * Generate packlist based on SO number and box
     */
    public function generate(Request $request)
    {
        $request->validate([
            'so_no' => 'required|string',
            'box' => 'nullable|string',
        ]);

        $query = Picking::query();
        
        // Filter by SO number
        $query->where('so_no', $request->so_no);
        
        // Filter by box if provided
        if ($request->filled('box') && $request->box !== 'all') {
            $query->where('box', $request->box);
        }
        
        $packitems = $query->get();
        
        // Ensure JSON is properly formatted
        foreach ($packitems as $item) {
            if (!is_string($item->items)) {
                $item->items = json_encode($item->items);
            }
        }
        
        return view('packlist.result', compact('packitems'));
    }

    /**
     * Print packlist for a specific SO number or box
     */
    public function print($so_no, $box = null)
    {
        $query = Picking::query()->where('so_no', $so_no);
        
        if ($box && $box !== 'null' && $box !== 'all') {
            $query->where('box', $box);
        }
        
        $packitems = $query->get();
        
        // Ensure JSON is properly formatted
        foreach ($packitems as $item) {
            if (!is_string($item->items)) {
                $item->items = json_encode($item->items);
            }
        }
        
        return view('packlist.print', compact('packitems', 'so_no', 'box'));
    }
}
