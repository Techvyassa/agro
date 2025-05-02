@extends('layouts.admin')

@section('title', 'Item Details')
@section('page-title', 'Item Details')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Item Details</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('admin.items.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Items
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h5 class="border-bottom pb-2">Basic Information</h5>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Item Code:</div>
                    <div class="col-md-8">{{ $item->item_code }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Item Name:</div>
                    <div class="col-md-8">{{ $item->item_name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Category:</div>
                    <div class="col-md-8">{{ $item->category }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">HSN Code:</div>
                    <div class="col-md-8">{{ $item->hsn }}</div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <h5 class="border-bottom pb-2">Other Details</h5>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Rate:</div>
                    <div class="col-md-8">₹{{ number_format($item->rate, 2) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Status:</div>
                    <div class="col-md-8">
                        @if($item->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Created At:</div>
                    <div class="col-md-8">{{ $item->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Last Updated:</div>
                    <div class="col-md-8">{{ $item->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
            
            <div class="col-12">
                <h5 class="border-bottom pb-2">Description</h5>
                <p>{{ $item->description ?: 'No description available.' }}</p>
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-4 justify-content-end">
            <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection