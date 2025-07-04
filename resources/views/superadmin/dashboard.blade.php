@extends('layouts.superadmin')

@section('title', 'Superadmin Dashboard')
@section('page-title', 'Superadmin Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Users</h6>
                        <h4 class="mb-0">{{ $userCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="#" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> View Details
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-boxes dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Items</h6>
                        <h4 class="mb-0">{{ $itemCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="#" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> View Items
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-shopping-cart dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Sales Orders</h6>
                        <h4 class="mb-0">{{ $salesOrderCount ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="#" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> View Orders
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-money-bill-wave dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Revenue</h6>
                        <h4 class="mb-0">₹{{ number_format($totalRevenue ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="#" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> View Reports
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Items</h5>
                <a href="#" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Length</th>
                                <th scope="col">Width</th>
                                <th scope="col">Height</th>
                                <th scope="col">Weight</th>
                                <th scope="col" width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentItems ?? [] as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->length }}</td>
                                <td>{{ $item->width }}</td>
                                <td>{{ $item->height }}</td>
                                <td>{{ $item->weight }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No items found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Sales Orders</h5>
                <a href="#" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">SO No</th>
                                <th scope="col">Item</th>
                                <th scope="col">Category</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Rate</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSalesOrders ?? [] as $order)
                            <tr>
                                <td>{{ $order->so_no }}</td>
                                <td>{{ $order->item_name }}</td>
                                <td>{{ $order->category }}</td>
                                <td>{{ $order->qty }}</td>
                                <td>₹{{ number_format((float)$order->rate, 2) }}</td>
                                <td>₹{{ number_format((float)$order->qty * (float)$order->rate, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No sales orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i> Add New Item
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i> Add New User
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 