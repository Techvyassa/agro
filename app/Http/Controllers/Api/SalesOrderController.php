<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalesOrderController extends Controller
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

    public function show($so_no)
    {
        $orderItems = SalesOrder::where('so_no', $so_no)->get();

        if ($orderItems->isEmpty()) {
            return response()->json(['message' => 'Sales order not found'], 404);
        }

        return response()->json($orderItems);
    }
}