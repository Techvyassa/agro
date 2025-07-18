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
            <a href="{{ route('user.pdfs.index') }}" class="btn btn-primary">
                <i class="fas fa-upload me-1"></i> Upload New PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter form -->
        <form action="{{ route('sales_orders.filter') }}" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="search" class="form-label">Search SO Number or Item Name</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Enter SO Number or Item Name" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary mt-4">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request('search'))
                        <a href="{{ route('sales_orders.index') }}" class="btn btn-outline-secondary mt-4">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
        
        @if(count($salesOrders) > 0)
            @php
                // Group sales orders by SO No
                $grouped = $salesOrders->groupBy('so_no');
            @endphp
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SO No</th>
                            <th>Uploaded Date</th>
                            <th>Packing Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $so_no => $orders)
                            @php
                                // Uploaded date: earliest created_at from sales_orders
                                $uploadedDate = $orders->min('created_at');
                                // Packing date: earliest updated_at from pickings for this SO No
                                $packingDate = optional(\App\Models\Picking::where('so_no', $so_no)->orderBy('updated_at')->first())->updated_at;
                            @endphp
                            <tr>
                                <td>{{ $so_no }}</td>
                                <td>{{ $uploadedDate ? $uploadedDate->format('M d, Y') : '' }}</td>
                                <td>{{ $packingDate ? $packingDate->format('M d, Y') : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {!! $salesOrders->onEachSide(1)->links('pagination::bootstrap-4') !!}
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