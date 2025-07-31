@extends('layouts.dashboard')

@section('title', 'Sales Orders')
@section('page-title', 'Sales Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Orders</li>
@endsection
<style>
    .rotate-180 {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }

    .toggle-details-btn {
        cursor: pointer;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
    }
</style>

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
            {{-- Filter form --}}
            <form action="{{ route('sales_orders.index') }}" method="GET" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search SO Number</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Enter SO Number"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="uploaded_date" class="form-label">Uploaded Date</label>
                        <input type="date" name="uploaded_date" id="uploaded_date" class="form-control"
                            value="{{ request('uploaded_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="packing_date" class="form-label">Packing Date</label>
                        <input type="date" name="packing_date" id="packing_date" class="form-control"
                            value="{{ request('packing_date') }}">
                    </div>
                    <div class="col-md-2">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($soGroups as $row)
                                @php
                                    $packingDate = optional(\App\Models\Picking::where('so_no', $row->so_no)->orderBy('updated_at')->first())->updated_at;
                                    $pickings = $pickingsBySo[$row->so_no] ?? collect();
                                    $salesOrders = $salesOrdersBySo[$row->so_no] ?? collect();
                                @endphp
                                <tr>
                                    <td>{{ $row->so_no }}</td>
                                    <td>{{ $row->uploaded_at ? \Carbon\Carbon::parse($row->uploaded_at)->format('M d, Y') : '' }}
                                    </td>
                                    <td>{{ $packingDate ? \Carbon\Carbon::parse($packingDate)->format('M d, Y') : '' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary toggle-details-btn" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#so-details-{{ $loop->index }}"
                                            aria-expanded="false" aria-controls="so-details-{{ $loop->index }}">
                                            View SO Details
                                            <i class="fas fa-chevron-down ms-2 toggle-icon"></i>
                                        </button>
                                    </td>
                                </tr>

                                <tr class="collapse bg-light" id="so-details-{{ $loop->index }}">
                                <td colspan="4">
                                    <div class="row">
                                        {{-- Sales Order Items --}}
                                        <div class="col-md-6">
                                            <h6>Sales Order Items</h6>
                                            @if($salesOrdersBySo[$row->so_no] ?? false)
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th>Item Name</th>
                                                            <th>Category</th>
                                                            <th>HSN</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($salesOrdersBySo[$row->so_no] as $so)
                                                            <tr>
                                                                <td>{{ $so->item_name }}</td>
                                                                <td>{{ $so->category }}</td>
                                                                <td>{{ $so->hsn }}</td>
                                                                <td>{{ $so->qty }}</td>
                                                                <td>{{ $so->rate }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="text-muted">No sales order items found.</div>
                                            @endif
                                        </div>

                                        {{-- Picking Items --}}
                                        <div class="col-md-6">
                                            <h6>Picking Details</h6>
                                            @if($pickingsBySo[$row->so_no] ?? false)
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th>Item</th>
                                                            <th>Dimension</th>
                                                            <th>Weight</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pickingsBySo[$row->so_no] as $picking)
                                                            <tr>
                                                                <td>
                                                                    <ul class="mb-0 ps-3">
                                                                        @foreach($picking->items as $itemJson)
                                                                            @php
                                                                                $item = json_decode($itemJson, true);
                                                                            @endphp
                                                                            <li>{{ $item['item'] }} (Qty: {{ $item['qty'] }})</li>
                                                                        @endforeach
                                                                    </ul>
                                                                    
                                                                </td>
                                                                <td>{{ $picking->dimension }}</td>
                                                                <td>{{ $picking->weight }}</td>
                                                                <td>{{ ucfirst($picking->status) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <div class="text-muted">No picking data found.</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toggle-details-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const icon = this.querySelector('.toggle-icon');
                const targetId = this.getAttribute('data-bs-target');
                const target = document.querySelector(targetId);
                const isShown = target.classList.contains('show');

                // Reset all icons
                document.querySelectorAll('.toggle-icon').forEach(i => i.classList.remove('rotate-180'));

                // Toggle current
                if (!isShown) {
                    icon.classList.add('rotate-180');
                }
            });
        });
    });
</script>
