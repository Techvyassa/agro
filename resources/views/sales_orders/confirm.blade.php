@extends('layouts.dashboard')

@section('title', 'Confirm Sales Orders')

@section('page-title', 'Confirm Sales Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sales_orders.upload') }}">Sales Order CSV Upload</a></li>
    <li class="breadcrumb-item active">Confirm</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Confirm Sales Orders</h5>
    </div>
    <div class="card-body">
        <p>Please review the sales order data before confirming:</p>
        
        @if(isset($groupedOrders) && count($groupedOrders) > 0)
            @foreach($groupedOrders as $soNumber => $orders)
                <h5 class="mt-4 mb-2">SO Number: {{ $soNumber }}</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>HSN Code</th>
                                <th>Qty</th>
                                <th>Rate</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $soTotal = 0; @endphp
                            @foreach($orders as $order)
                                @php 
                                    $itemAmount = $order['qty'] * $order['rate'];
                                    $soTotal += $itemAmount;
                                @endphp
                                <tr>
                                    <td>{{ $order['item_name'] }}</td>
                                    <td>{{ $order['category'] }}</td>
                                    <td>{{ $order['hsn'] }}</td>
                                    <td>{{ $order['qty'] }}</td>
                                    <td>{{ $order['rate'] }}</td>
                                    <td>{{ number_format($itemAmount, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-light">
                                <td colspan="5" class="text-end fw-bold">Total for {{ $soNumber }}:</td>
                                <td class="fw-bold">{{ number_format($soTotal, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SO Number</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>HSN Code</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesOrders as $order)
                        <tr>
                            <td>{{ $order['so_no'] }}</td>
                            <td>{{ $order['item_name'] }}</td>
                            <td>{{ $order['category'] }}</td>
                            <td>{{ $order['hsn'] }}</td>
                            <td>{{ $order['qty'] }}</td>
                            <td>{{ $order['rate'] }}</td>
                            <td>{{ number_format($order['qty'] * $order['rate'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No records found in the CSV file.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('sales_orders.upload') }}" class="btn btn-secondary">Cancel</a>
            <form action="{{ route('sales_orders.save') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Confirm and Save</button>
            </form>
        </div>
    </div>
</div>
@endsection