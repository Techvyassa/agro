<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuperadminItemMaster;
use Illuminate\Support\Facades\Redirect;

class ItemMasterController extends Controller
{
    // List all items
    public function index()
    {
        $items = SuperadminItemMaster::all();
        return view('superadmin.item_masters.index', compact('items'));
    }

    // Show CSV upload form
    public function create()
    {
        return view('superadmin.item_masters.create');
    }

    // Handle CSV upload and store items
    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $expected = ['item_name', 'category_name', 'unit', 'MOQ', 'SKU Name Code', 'Category Code'];
        // Normalize header for comparison
        $headerNorm = array_map(fn($h) => strtolower(str_replace([' ', '_'], '', $h)), $header);
        $expectedNorm = array_map(fn($h) => strtolower(str_replace([' ', '_'], '', $h)), $expected);
        if ($headerNorm !== $expectedNorm) {
            return back()->withErrors(['csv_file' => 'CSV columns do not match expected format.']);
        }
        while (($row = fgetcsv($handle)) !== false) {
            SuperadminItemMaster::create([
                'item_name' => $row[0],
                'category_name' => $row[1],
                'unit' => $row[2],
                'moq' => $row[3],
                'sku_name_code' => $row[4],
                'category_code' => $row[5],
            ]);
        }
        fclose($handle);
        return Redirect::route('superadmin.item-masters.index')->with('success', 'Items uploaded successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $item = SuperadminItemMaster::findOrFail($id);
        return view('superadmin.item_masters.edit', compact('item'));
    }

    // Update item
    public function update(Request $request, $id)
    {
        $item = SuperadminItemMaster::findOrFail($id);
        $request->validate([
            'item_name' => 'required',
            'category_name' => 'required',
            'unit' => 'required',
            'moq' => 'required|integer',
            'sku_name_code' => 'required',
            'category_code' => 'required|integer',
        ]);
        $item->update($request->only(['item_name', 'category_name', 'unit', 'moq', 'sku_name_code', 'category_code']));
        return Redirect::route('superadmin.item-masters.index')->with('success', 'Item updated successfully.');
    }

    // Delete item
    public function destroy($id)
    {
        $item = SuperadminItemMaster::findOrFail($id);
        $item->delete();
        return Redirect::route('superadmin.item-masters.index')->with('success', 'Item deleted successfully.');
    }
}
