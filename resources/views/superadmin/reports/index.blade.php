@extends('layouts.superadmin')

@section('title', 'Reports Generation')
@section('page-title', 'Reports Generation')

@section('content')
    <div class="container">
        {{-- Filter Form --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Filter by Location ID</strong>
                @if(isset($reports) && $reports->count())
                    <a href="{{ route('superadmin.reports.export', ['locationcode' => request('locationcode')]) }}"
                        class="btn btn-success btn-sm">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                @endif
            </div>

            <div class="card-body">
                <form action="{{ route('superadmin.reports.index') }}" method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <select name="locationcode" class="form-control" required>
                            <option value="">-- Select Location ID --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ request('locationcode') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('superadmin.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Report Table --}}
        @if(isset($reports) && $reports->count())
            <div class="card">
                <div class="card-header"><strong>Filtered Transfers</strong></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Location ID</th>
                                <th>Invoice Number</th>
                                <th>Part No</th>
                                <th>Description</th>
                                <th>Transfer Qty</th>
                                <th>Rack</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $index => $report)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $report->location_id }}</td>
                                    <td>{{ $report->invoice_number }}</td>
                                    <td>{{ $report->part_no }}</td>
                                    <td>{{ $report->asn_description ?? '-' }}</td>
                                    <td>{{ $report->transfer_qty }}</td>
                                    <td>{{ $report->rack }}</td>
                                    <td>{{ $report->formatted_date }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request()->has('locationcode'))
            <div class="alert alert-warning">
                No data found for location <strong>{{ request('locationcode') }}</strong>.
            </div>
        @endif
    </div>
@endsection