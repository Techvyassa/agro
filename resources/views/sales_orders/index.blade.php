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
        <form action="{{ route('sales_orders.index') }}" method="GET" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search SO Number</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Enter SO Number" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="uploaded_from" class="form-label">Uploaded From</label>
                    <input type="date" name="uploaded_from" id="uploaded_from" class="form-control" value="{{ request('uploaded_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="uploaded_to" class="form-label">Uploaded To</label>
                    <input type="date" name="uploaded_to" id="uploaded_to" class="form-control" value="{{ request('uploaded_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="packing_from" class="form-label">Packing From</label>
                    <input type="date" name="packing_from" id="packing_from" class="form-control" value="{{ request('packing_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="packing_to" class="form-label">Packing To</label>
                    <input type="date" name="packing_to" id="packing_to" class="form-control" value="{{ request('packing_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-secondary mt-4 w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
        @if($soGroups->count() > 0)
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
                        @foreach($soGroups as $row)
                            @php
                                $packingDate = optional(\App\Models\Picking::where('so_no', $row->so_no)->orderBy('updated_at')->first())->updated_at;
                            @endphp
                            <tr>
                                <td>{{ $row->so_no }}</td>
                                <td>{{ $row->uploaded_at ? \Carbon\Carbon::parse($row->uploaded_at)->format('M d, Y') : '' }}</td>
                                <td>{{ $packingDate ? \Carbon\Carbon::parse($packingDate)->format('M d, Y') : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {!! $soGroups->onEachSide(1)->links('pagination::bootstrap-4') !!}
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