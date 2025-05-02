@extends('layouts.admin')

@section('title', 'Sales Order Details')
@section('page-title', 'Sales Order Details')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Sales Order Details</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Orders
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h5 class="border-bottom pb-2">Order Information</h5>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Order Number:</div>
                    <div class="col-md-8">{{ $salesOrder->so_no }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Created Date:</div>
                    <div class="col-md-8">{{ $salesOrder->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Last Updated:</div>
                    <div class="col-md-8">{{ $salesOrder->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <h5 class="border-bottom pb-2">Item Information</h5>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Item Name:</div>
                    <div class="col-md-8">{{ $salesOrder->item_name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Category:</div>
                    <div class="col-md-8">{{ $salesOrder->category }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">HSN Code:</div>
                    <div class="col-md-8">{{ $salesOrder->hsn }}</div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <h5 class="border-bottom pb-2">Order Details</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>HSN</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $salesOrder->item_name }}<br><small class="text-muted">{{ $salesOrder->category }}</small></td>
                                <td>{{ $salesOrder->hsn }}</td>
                                <td class="text-center">{{ $salesOrder->qty }}</td>
                                <td class="text-end">₹{{ number_format($salesOrder->rate, 2) }}</td>
                                <td class="text-end">₹{{ number_format($salesOrder->qty * $salesOrder->rate, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end">₹{{ number_format($salesOrder->qty * $salesOrder->rate, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-4 justify-content-end">
            <a href="{{ route('admin.sales.edit', $salesOrder->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <form action="{{ route('admin.sales.destroy', $salesOrder->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection