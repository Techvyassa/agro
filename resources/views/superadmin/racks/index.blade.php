@extends('layouts.superadmin')

@section('title', 'Rack Management')
@section('page-title', 'Rack Management')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Upload Bin Locations CSV</div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('superadmin.racks.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Bin Locations List</div>
                <div class="card-body">
                    @if(isset($bins) && $bins->count())
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Bin Name</th>
                                    <th>Sequence</th>
                                    <th>Status</th>
                                    <th>Min Qty</th>
                                    <th>Max Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bins as $bin)
                                    <tr>
                                        <td>{{ $bin->bin_name }}</td>
                                        <td>{{ $bin->sequence }}</td>
                                        <td>
                                            @if($bin->status)
                                                @php
                                                    $badgeClass = match(strtolower($bin->status)) {
                                                        'fastmoving' => 'bg-success',
                                                        'slowmoving' => 'bg-warning',
                                                        'overflow' => 'bg-secondary',
                                                        default => 'bg-dark',
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($bin->status) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $bin->min_qty ?? '-' }}</td>
                                        <td>{{ $bin->max_qty ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No bin locations found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 