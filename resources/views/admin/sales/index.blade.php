@extends('layouts.admin')

@section('title', 'Sales Order Management')
@section('page-title', 'Sales Order Management')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h1 class="h3">Sales Orders</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i> Create New Order
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">SO Number</th>
                        <th scope="col">Item</th>
                        <th scope="col">Category</th>
                        <th scope="col">HSN</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Rate</th>
                        <th scope="col">Total</th>
                        <th scope="col" width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesOrders as $order)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $order->so_no }}</td>
                        <td>{{ $order->item_name }}</td>
                        <td>{{ $order->category }}</td>
                        <td>{{ $order->hsn }}</td>
                        <td>{{ $order->qty }}</td>
                        <td>₹{{ number_format($order->rate, 2) }}</td>
                        <td>₹{{ number_format($order->qty * $order->rate, 2) }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.sales.show', $order->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <!-- <a href="{{ route('admin.sales.edit', $order->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a> -->
                                <form action="{{ route('admin.sales.destroy', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this order?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No sales orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $salesOrders->links() }}
        </div>
    </div>
</div>
@endsection