<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Removed view()->share for shortSoCount; now handled in AppServiceProvider
    }

    public function index()
    {
        // Fetch pickings with status 'hold' for Short SO
        $shortSOs = \App\Models\Picking::where('status', 'hold')->get();
        // Display the dashboard page
        return view('dashboard', compact('shortSOs'));
    }

    public function forceComplete($id)
    {
        $picking = \App\Models\Picking::find($id);
        if ($picking && $picking->status === 'hold') {
            $picking->status = 'completed';
            $picking->save();
            return redirect()->back()->with('success', 'SO marked as completed.');
        }
        return redirect()->back()->with('error', 'Picking not found or not on hold.');
    }

    public function shortSoPage()
    {
        // 




        // Get all pickings on hold, grouped by so_no
        $pickings = \App\Models\Picking::where('status', 'hold')->get()->groupBy('so_no');

        // Get comparison data for each SO
        $comparisonData = [];

        foreach ($pickings as $so_no => $group) {
            // Get sales order items for this SO
            $salesOrderItems = \App\Models\SalesOrder::where('so_no', $so_no)
                ->select('item_name', 'qty')
                ->get()
                ->keyBy('item_name');

            // Get picked items for this SO
            $pickedItems = collect();
            foreach ($group as $packing) {
                $itemsData = $packing->items;

                if (is_array($itemsData)) {
                    foreach ($itemsData as $itemString) {
                        if (is_string($itemString)) {
                            $decoded = json_decode($itemString, true);
                            if (is_array($decoded) && isset($decoded['item']) && isset($decoded['qty'])) {
                                $pickedItems->push($decoded);
                            }
                        }
                    }
                }
            }

            // Group picked items by item name and sum quantities
            $pickedItemsGrouped = $pickedItems->groupBy('item')->map(function ($group) {
                return $group->sum('qty');
            });

            // Compare sales order vs picked items
            $shortItems = [];
            foreach ($salesOrderItems as $itemName => $salesOrder) {
                $orderedQty = $salesOrder->qty;
                $pickedQty = $pickedItemsGrouped->get($itemName, 0);
                $shortQty = $orderedQty - $pickedQty;

                if ($shortQty > 0) {
                    $shortItems[] = [
                        'item' => $itemName,
                        'ordered_qty' => $orderedQty,
                        'picked_qty' => $pickedQty,
                        'short_qty' => $shortQty
                    ];
                }
            }

            $comparisonData[$so_no] = $shortItems;
        }

        return view('short-so', compact('pickings', 'comparisonData'));
    }

    public function forceCompleteBySoNo($so_no)
    {
        $updated = \App\Models\Picking::where('so_no', $so_no)->where('status', 'hold')->update(['status' => 'force_completed']);
        if ($updated) {
            return redirect()->route('short-so')->with('success', 'All pickings for SO ' . $so_no . ' marked as force completed (closed with shortage).');
        }
        return redirect()->route('short-so')->with('error', 'No pickings found or already completed for SO ' . $so_no);
    }
}