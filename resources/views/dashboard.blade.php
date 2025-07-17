@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'User Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-file-csv dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Sales Orders</h6>
                        <h4 class="mb-0">Upload CSV</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="{{ route('sales_orders.upload') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> Upload Now
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-list dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">View Orders</h6>
                        <h4 class="mb-0">Sales List</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="{{ route('sales_orders.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> View List
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
                            <i class="fas fa-box dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Generate</h6>
                        <h4 class="mb-0">Packlist</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <a href="{{ route('packlist.index') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-arrow-right me-1"></i> Generate Now
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
                            <i class="fas fa-user dashboard-icon"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Account</h6>
                        <h4 class="mb-0">{{ Auth::user()->name }}</h4>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 py-2">
                <span class="text-muted small">
                    <i class="fas fa-envelope me-1"></i> {{ Auth::user()->email }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Quick Guide: Sales Order CSV Upload</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>CSV Upload Instructions</h6>
                        <ol class="ps-3">
                            <li>Prepare your CSV file with the following columns:
                                <ul>
                                    <li><strong>so_no</strong>: Sales Order Number</li>
                                    <li><strong>item_name</strong>: Item/Product Name</li>
                                    <li><strong>category</strong>: Product Category</li>
                                    <li><strong>hsn</strong>: HSN Code</li>
                                    <li><strong>qty</strong>: Quantity</li>
                                    <li><strong>rate</strong>: Rate per unit</li>
                                </ul>
                            </li>
                            <li>Click on "Sales Order CSV Upload" in the sidebar</li>
                            <li>Upload your CSV file</li>
                            <li>Review the data in the confirmation page</li>
                            <li>Confirm to save the data</li>
                        </ol>
                        <div class="mt-3">
                            <a href="{{ route('sales_orders.upload') }}" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Go to Upload Page
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Sample CSV Format</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>so_no</th>
                                        <th>item_name</th>
                                        <th>category</th>
                                        <th>hsn</th>
                                        <th>qty</th>
                                        <th>rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>SO1001</td>
                                        <td>LED Bulb</td>
                                        <td>Electronics</td>
                                        <td>94054090</td>
                                        <td>100</td>
                                        <td>120</td>
                                    </tr>
                                    <tr>
                                        <td>SO1002</td>
                                        <td>USB Cable</td>
                                        <td>Electronics</td>
                                        <td>85444999</td>
                                        <td>200</td>
                                        <td>50</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Make sure your CSV file has the column headers in the first row.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Short SO (Pickings on Hold)</h5>
            </div>
            <div class="card-body">
                @if(isset($shortSOs) && $shortSOs->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>SO No</th>
                                    <th>Box</th>
                                    <th>Items</th>
                                    <th>Dimension</th>
                                    <th>Weight</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shortSOs as $so)
                                    <tr>
                                        <td>{{ $so->so_no }}</td>
                                        <td>{{ $so->box }}</td>
                                        <td>
                                            @if(is_array($so->items))
                                                {{ implode(', ', $so->items) }}
                                            @else
                                                {{ $so->items }}
                                            @endif
                                        </td>
                                        <td>{{ $so->dimension }}</td>
                                        <td>{{ $so->weight }}</td>
                                        <td><span class="badge bg-warning text-dark">{{ $so->status }}</span></td>
                                        <td>
                                            <form method="POST" action="{{ route('pickings.force-complete', $so->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this SO as completed?')">Force Complete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">No pickings on hold found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection