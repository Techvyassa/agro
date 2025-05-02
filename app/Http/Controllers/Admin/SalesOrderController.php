<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\ItemMaster;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the sales orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $salesOrders = SalesOrder::latest()->paginate(10);
        return view('admin.sales.index', compact('salesOrders'));
    }

    /**
     * Show the form for creating a new sales order.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $items = ItemMaster::where('is_active', true)->get();
        return view('admin.sales.create', compact('items'));
    }

    /**
     * Store a newly created sales order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'so_no' => 'required|unique:sales_orders',
            'item_name' => 'required',
            'category' => 'required',
            'hsn' => 'required',
            'qty' => 'required|numeric|min:1',
            'rate' => 'required|numeric',
        ]);

        SalesOrder::create($request->all());

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sales order created successfully.');
    }

    /**
     * Display the specified sales order.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function show(SalesOrder $salesOrder)
    {
        return view('admin.sales.show', compact('salesOrder'));
    }

    /**
     * Show the form for editing the specified sales order.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(SalesOrder $salesOrder)
    {
        $items = ItemMaster::where('is_active', true)->get();
        return view('admin.sales.edit', compact('salesOrder', 'items'));
    }

    /**
     * Update the specified sales order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        $request->validate([
            'so_no' => 'required|unique:sales_orders,so_no,' . $salesOrder->id,
            'item_name' => 'required',
            'category' => 'required',
            'hsn' => 'required',
            'qty' => 'required|numeric|min:1',
            'rate' => 'required|numeric',
        ]);

        $salesOrder->update($request->all());

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sales order updated successfully');
    }

    /**
     * Remove the specified sales order from storage.
     *
     * @param  \App\Models\SalesOrder  $salesOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalesOrder $salesOrder)
    {
        $salesOrder->delete();

        return redirect()->route('admin.sales.index')
            ->with('success', 'Sales order deleted successfully');
    }
}