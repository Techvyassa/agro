<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Store a newly created order in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Log the raw request for debugging
            Log::debug('Order creation request', [
                'payload' => $request->all()
            ]);
            
            // Extract data from the request
            $requestData = $request->all();
            $responseData = $requestData['response'] ?? [];
            
            // Create the order record
            $order = Order::create([
                'order_id' => $requestData['orderIds'] ?? $requestData['order_id'] ?? '',
                'order_date' => $requestData['orderDate'] ?? now()->format('Y-m-d'),
                'pickup_id' => $requestData['pickUpId'] ?? null,
                'pickup_city' => $requestData['pickUpCity'] ?? null,
                'pickup_state' => $requestData['pickUpState'] ?? null,
                'return_id' => $requestData['retrunId'] ?? null,
                'return_city' => $requestData['returnCity'] ?? null,
                'return_state' => $requestData['returnState'] ?? null,
                'invoice_amount' => $requestData['invoiceAmt'] ?? 0,
                'item_name' => $requestData['itemName'] ?? null,
                'cod_amount' => $requestData['codAmt'] ?? 0,
                'quantity' => $requestData['qty'] ?? 1,
                'buyer_name' => isset($requestData['buyer']) ? 
                    ($requestData['buyer']['fName'] ?? '') . ' ' . ($requestData['buyer']['lName'] ?? '') : null,
                'buyer_email' => $requestData['buyer']['emailId'] ?? null,
                'buyer_address' => $requestData['buyer']['buyerAddresses']['address1'] ?? null,
                'buyer_phone' => $requestData['buyer']['buyerAddresses']['mobileNo'] ?? null,
                'buyer_pincode' => $requestData['buyer']['buyerAddresses']['pinId'] ?? null,
                'order_status' => isset($responseData['success']) && $responseData['success'] ? 'SUCCESS' : 'FAILED',
                'response_code' => $responseData['responseCode'] ?? null,
                'response_message' => $responseData['message'] ?? null,
                'raw_request' => json_encode($requestData),
                'raw_response' => json_encode($responseData),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order data stored successfully',
                'id' => $order->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error storing order: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display a listing of orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::latest()->paginate(15);
        return view('orders.index', compact('orders'));
    }
    
    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }
}
