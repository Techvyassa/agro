@extends('layouts.dashboard')

@section('title', 'Sales Orders')

@section('page-title', 'Sales Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Orders</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Sales Orders List</h5>
        <div>
            <a href="{{ route('sales_orders.export', request()->query()) }}" class="btn btn-success me-2">
                <i class="fas fa-file-excel me-1"></i> Export to CSV
            </a>
            <a href="{{ route('sales_orders.upload') }}" class="btn btn-primary">
                <i class="fas fa-upload me-1"></i> Upload New CSV
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter form -->
        <form action="{{ route('sales_orders.filter') }}" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="so_no" class="form-label">Filter by SO Number</label>
                    <select name="so_no" id="so_no" class="form-select">
                        <option value="">All SO Numbers</option>
                        @foreach($soNumbers as $soNumber)
                            <option value="{{ $soNumber }}" {{ isset($so_no) && $so_no == $soNumber ? 'selected' : '' }}>{{ $soNumber }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(isset($so_no) && !empty($so_no))
                        <a href="{{ route('sales_orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
        
        @if(count($salesOrders) > 0)
            <!-- Group by SO Number -->
            @php
                $currentSoNo = null;
                $totalAmount = 0;
            @endphp
            
            @foreach($salesOrders as $order)
                @if($currentSoNo !== $order->so_no)
                    @if($currentSoNo !== null)
                        <!-- Display total for previous SO -->
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold">Total for {{ $currentSoNo }}:</td>
                            <td class="fw-bold">{{ number_format($totalAmount, 2) }}</td>
                            <td></td>
                        </tr>
                        </tbody>
                        </table>
                        </div>
                    @endif
                    
                    <!-- Start new table for new SO Number -->
                    <h5 class="mt-4 mb-2">SO Number: {{ $order->so_no }}</h5>
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
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody>
                    
                    @php
                        $currentSoNo = $order->so_no;
                        $totalAmount = 0;
                    @endphp
                @endif
                
                @php
                    $itemAmount = $order->qty * $order->rate;
                    $totalAmount += $itemAmount;
                @endphp
                
                <tr>
                    <td>{{ $order->item_name }}</td>
                    <td>{{ $order->category }}</td>
                    <td>{{ $order->hsn }}</td>
                    <td>{{ $order->qty }}</td>
                    <td>{{ $order->rate }}</td>
                    <td>{{ number_format($itemAmount, 2) }}</td>
                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
            
            @if($currentSoNo !== null)
                <!-- Display total for last SO -->
                <tr class="table-light">
                    <td colspan="5" class="text-end fw-bold">Total for {{ $currentSoNo }}:</td>
                    <td class="fw-bold">{{ number_format($totalAmount, 2) }}</td>
                    <td></td>
                </tr>
                </tbody>
                </table>
                </div>
            @endif
            
            <div class="d-flex justify-content-center">
                {{ $salesOrders->links() }}
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No sales orders found. Please upload a CSV file to add sales orders.
            </div>
        @endif
    </div>
</div>
@endsection