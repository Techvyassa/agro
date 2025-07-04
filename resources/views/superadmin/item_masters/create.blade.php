@extends('layouts.superadmin')

@section('content')
<div class="container mt-4">
    <h2>Upload Item Master CSV</h2>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('superadmin.item-masters.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="csv_file" class="form-label">CSV File</label>
            <input type="file" name="csv_file" id="csv_file" class="form-control" required accept=".csv">
            <small class="form-text text-muted">Columns: item_name, category_name, unit, MOQ, SKU Name Code, Category Code</small>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="{{ route('superadmin.item-masters.index') }}" class="btn btn-secondary">Back to List</a>
    </form>
</div>
@endsection 