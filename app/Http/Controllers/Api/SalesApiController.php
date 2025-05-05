<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesApiController extends Controller
{
    /**
     * Display a listing of sales orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(SalesOrder::all());
    }

    /**
     * Display the specified sales order.
     *
     * @param  string  $so_no
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($so_no)
    {
        $orderItems = SalesOrder::where('so_no', $so_no)->get();

        if ($orderItems->isEmpty()) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        return response()->json($orderItems);
    }
}
