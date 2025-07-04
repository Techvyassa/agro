@extends('layouts.location')

@section('title', 'ASN Uploads List')
@section('page-title', 'ASN Uploads List')

@section('content')
<div class="row">
    <div class="col-12">
        <h4>ASN Uploads</h4>
        <form method="GET" action="" class="row g-3 mb-3">
            <div class="col-auto">
                <input type="text" name="invoice_number" class="form-control" placeholder="Filter by Invoice Number" value="{{ $invoice_number ?? '' }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('location.asn.list') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Timestamp</th>
                    <th>Sr</th>
                    <th>Description</th>
                    <th>Part No</th>
                    <th>Model</th>
                    <th>PCS</th>
                    <th>Uploaded At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asn_uploads as $asn)
                    <tr>
                        <td>{{ $asn->invoice_number }}</td>
                        <td>{{ $asn->asn_timestamp }}</td>
                        <td>{{ $asn->sr }}</td>
                        <td>{{ $asn->description }}</td>
                        <td>{{ $asn->part_no }}</td>
                        <td>{{ $asn->model }}</td>
                        <td>{{ $asn->pcs }}</td>
                        <td>{{ $asn->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No ASN uploads found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection 