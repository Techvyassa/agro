<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemMasterController extends Controller
{
    /**
     * Display a listing of the items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = ItemMaster::latest()->paginate(10);
        return view('admin.items.index', compact('items'));
    }

    /**
     * Show the form for creating a new item.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.items.create');
    }

    /**
     * Store a newly created item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_code' => 'required|unique:item_masters',
            'item_name' => 'required',
            'category' => 'required',
            'hsn' => 'required',
            'rate' => 'required|numeric',
            'description' => 'nullable',
        ]);

        ItemMaster::create($request->all());

        return redirect()->route('admin.items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified item.
     *
     * @param  \App\Models\ItemMaster  $item
     * @return \Illuminate\Http\Response
     */
    public function show(ItemMaster $item)
    {
        return view('admin.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param  \App\Models\ItemMaster  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(ItemMaster $item)
    {
        return view('admin.items.edit', compact('item'));
    }

    /**
     * Update the specified item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ItemMaster  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ItemMaster $item)
    {
        $request->validate([
            'item_code' => 'required|unique:item_masters,item_code,' . $item->id,
            'item_name' => 'required',
            'category' => 'required',
            'hsn' => 'required',
            'rate' => 'required|numeric',
            'description' => 'nullable',
        ]);

        $item->update($request->all());

        return redirect()->route('admin.items.index')
            ->with('success', 'Item updated successfully');
    }

    /**
     * Remove the specified item from storage.
     *
     * @param  \App\Models\ItemMaster  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(ItemMaster $item)
    {
        $item->delete();

        return redirect()->route('admin.items.index')
            ->with('success', 'Item deleted successfully');
    }
}