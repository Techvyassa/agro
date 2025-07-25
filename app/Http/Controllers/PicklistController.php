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
        // Get all unique SO numbers from the pickings table where status is completed or force_completed
        $so_numbers = Picking::whereIn('status', ['completed', 'force_completed'])
            ->select('so_no')->distinct()->orderBy('so_no')->pluck('so_no');
        
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

    /**
     * AJAX: Get all packlist items for a given SO/box
     */
    public function getPacklistItems(Request $request)
    {
        $so_no = $request->input('so_no');
        $box = $request->input('box');
        if (!$so_no) {
            return response()->json([], 400);
        }
        $query = \App\Models\Picking::where('so_no', $so_no);
        if ($box && $box !== 'all') {
            $query->where('box', $box);
        }
        $pickings = $query->get();
        $result = [];
        foreach ($pickings as $picking) {
            $itemsArray = is_array($picking->items) ? $picking->items : json_decode($picking->items, true);
            if (is_array($itemsArray)) {
                foreach ($itemsArray as $idx => $packItem) {
                    $itemData = is_array($packItem) ? $packItem : json_decode($packItem, true);
                    $result[] = [
                        'id' => $picking->id,
                        'item_index' => $idx,
                        'item_name' => $itemData['item'] ?? '',
                        'quantity' => $itemData['qty'] ?? '',
                        'weight' => $itemData['weight'] ?? $picking->weight,
                        'dimension' => $itemData['dimension'] ?? $picking->dimension,
                        'box' => $picking->box,
                        'so_no' => $picking->so_no,
                    ];
                }
            }
        }
        return response()->json($result);
    }

    /**
     * AJAX: Update a specific packlist item (quantity, weight, dimension)
     */
    public function updatePacklistItem(Request $request, $picking_id, $item_index)
    {
        $picking = \App\Models\Picking::findOrFail($picking_id);
        $itemsArray = is_array($picking->items) ? $picking->items : json_decode($picking->items, true);
        if (!isset($itemsArray[$item_index])) {
            \Log::error('Packlist update: Item not found', ['picking_id' => $picking_id, 'item_index' => $item_index]);
            return response()->json(['error' => 'Item not found'], 404);
        }
        // Parse the JSON string to an array
        $itemData = json_decode($itemsArray[$item_index], true);
        $itemData['qty'] = $request->input('quantity', $itemData['qty']);
        $itemData['weight'] = $request->input('weight', $itemData['weight'] ?? $picking->weight);
        $itemData['dimension'] = $request->input('dimension', $itemData['dimension'] ?? $picking->dimension);
        // Re-encode as JSON string
        $itemsArray[$item_index] = json_encode($itemData);
        $picking->items = $itemsArray;
        try {
            $picking->save();
        } catch (\Exception $e) {
            \Log::error('Packlist update: Failed to save picking', ['error' => $e->getMessage(), 'picking_id' => $picking_id]);
            return response()->json(['error' => 'Failed to update item.'], 500);
        }
        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Delete a specific packlist item
     */
    public function deletePacklistItem($picking_id, $item_index)
    {
        $picking = \App\Models\Picking::findOrFail($picking_id);
        $itemsArray = is_array($picking->items) ? $picking->items : json_decode($picking->items, true);
        if (!isset($itemsArray[$item_index])) {
            return response()->json(['error' => 'Item not found'], 404);
        }
        array_splice($itemsArray, $item_index, 1);
        $picking->items = $itemsArray;
        $picking->save();
        return response()->json(['success' => true]);
    }
}
