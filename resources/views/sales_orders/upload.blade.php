@extends('layouts.dashboard')

@section('title', 'Sales Order CSV Upload')

@section('page-title', 'Sales Order CSV Upload')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Order CSV Upload</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Upload Sales Order CSV</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('sales_orders.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="csv_file" class="form-label">CSV File</label>
                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv">
                <div class="form-text">
                    The CSV should contain the following columns: so_no, item_name, category, hsn, qty, rate
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Upload CSV</button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">CSV Format Instructions</h5>
    </div>
    <div class="card-body">
        <p>Your CSV file should have the following columns:</p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Column Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>so_no</td>
                    <td>Sales Order Number</td>
                </tr>
                <tr>
                    <td>item_name</td>
                    <td>Item/Product Name</td>
                </tr>
                <tr>
                    <td>category</td>
                    <td>Product Category</td>
                </tr>
                <tr>
                    <td>hsn</td>
                    <td>HSN Code</td>
                </tr>
                <tr>
                    <td>qty</td>
                    <td>Quantity</td>
                </tr>
                <tr>
                    <td>rate</td>
                    <td>Rate per unit</td>
                </tr>
            </tbody>
        </table>
        
        <div class="alert alert-info">
            <strong>Note:</strong> The first row of your CSV file should contain the column headers as listed above.
        </div>
    </div>
</div>
@endsection