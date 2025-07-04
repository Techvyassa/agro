@extends('layouts.location')

@section('title', 'Upload ASN')
@section('page-title', 'Upload ASN')

@section('content')
<div class="row">
    <div class="col-12">
       
        <form method="POST" action="{{ route('location.asn.upload.post') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="asn_file" class="form-label">ASN File (PDF only)</label>
                <input type="file" class="form-control" id="asn_file" name="asn_file" accept="application/pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>
@if(isset(
    $api_response))
    <div class="mt-4">
        @if(isset($api_response['success']) && $api_response['success'])
            <h5>Extracted ASN Data</h5>
            <table class="table table-bordered">
                <tr><th>Invoice Number</th><td>{{ $api_response['invoice_number'] ?? '' }}</td></tr>
                <tr><th>Timestamp</th><td>{{ $api_response['timestamp'] ?? '' }}</td></tr>
            </table>
            <h6>Items</h6>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sr</th>
                        <th>Description</th>
                        <th>Part No</th>
                        <th>Model</th>
                        <th>PCS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($api_response['items'] ?? [] as $item)
                        <tr>
                            <td>{{ $item['Sr'] ?? '' }}</td>
                            <td>{{ $item['Description'] ?? '' }}</td>
                            <td>{{ $item['Part No'] ?? '' }}</td>
                            <td>{{ $item['Model'] ?? '' }}</td>
                            <td>{{ $item['PCS'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <form method="POST" action="{{ route('location.asn.upload.post') }}">
                @csrf
                <input type="hidden" name="asn_confirmed" value="1">
                <input type="hidden" name="asn_data" value='@json($api_response)'>
                <button type="submit" class="btn btn-success">Continue</button>
            </form>
        @else
            <div class="alert alert-danger">
                {{ $api_response['error'] ?? 'Failed to extract ASN data.' }}
            </div>
        @endif
    </div>
@endif
@if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif
@endsection 