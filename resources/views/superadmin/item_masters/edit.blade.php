@extends('layouts.superadmin')

@section('content')
<div class="container mt-4">
    <h2>Edit Item</h2>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('superadmin.item_masters.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Item Name</label>
            <input type="text" name="item_name" class="form-control" value="{{ old('item_name', $item->item_name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="category_name" class="form-control" value="{{ old('category_name', $item->category_name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Unit</label>
            <input type="text" name="unit" class="form-control" value="{{ old('unit', $item->unit) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">MOQ</label>
            <input type="number" name="moq" class="form-control" value="{{ old('moq', $item->moq) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">SKU Name Code</label>
            <input type="text" name="sku_name_code" class="form-control" value="{{ old('sku_name_code', $item->sku_name_code) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category Code</label>
            <input type="number" name="category_code" class="form-control" value="{{ old('category_code', $item->category_code) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('superadmin.item_masters.index') }}" class="btn btn-secondary">Back to List</a>
    </form>
</div>
@endsection 