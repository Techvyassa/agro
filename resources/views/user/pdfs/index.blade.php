@extends('layouts.dashboard')

@section('title', 'PDF Uploads')
@section('page-title', 'PDF Uploads')
@section('content')

    <h2>Upload PDF</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('user.pdfs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="pdf_file" class="form-label">Select PDF file</label>
            <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept="application/pdf" required>
            @error('pdf_file')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    @if(isset($message) && $message)
        <div class="alert alert-info mt-3">{{ $message }}</div>
    @endif
    @if(isset($filename) && $filename)
        <div class="alert alert-secondary">File: <strong>{{ $filename }}</strong></div>
    @endif

    @if(isset($invoiceData) && is_array($invoiceData) && count($invoiceData))
        <hr>
        <h3>Extracted Invoice Data</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-3 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>SO No</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>HSN</th>
                        <th>Qty</th>
                        <th>Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $soNo = isset($filename) ? pathinfo($filename, PATHINFO_FILENAME) : '';
                    @endphp
                    @foreach($invoiceData as $row)
                        <tr>
                            <td>{{ $row['sr_no'] ?? '' }}</td>
                            <td>{{ $soNo }}</td>
                            <td>{{ $row['item_name'] ?? '' }}</td>
                            <td>{{ $row['category'] ?? '' }}</td>
                            <td>{{ $row['hsn'] ?? '' }}</td>
                            <td>{{ $row['qty'] ?? '' }}</td>
                            <td>{{ $row['rate'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if(isset($invoiceData) && is_array($invoiceData) && count($invoiceData) && !empty($apiTimestamp))
        <form action="{{ route('user.pdfs.save-to-sales-orders') }}" method="POST" class="mt-3">
            @csrf
            <input type="hidden" name="so_no" value="{{ $soNo }}">
            <input type="hidden" name="invoice_data" value='@json($invoiceData)'>
            <input type="hidden" name="api_timestamp" value='{{ $apiTimestamp }}'>
            <button type="submit" class="btn btn-success">Continue</button>
        </form>
    @endif
@if(isset($invoiceData) && is_array($invoiceData) && count($invoiceData))
    <script>
        console.log('API Extraction Response:', {
            success: @json($apiSuccess ?? null),
            message: @json($message ?? null),
            filename: @json($filename ?? null),
            timestamp: @json($apiTimestamp ?? null),
            data: @json($invoiceData ?? [])
        });
    </script>
@endif
@endsection
