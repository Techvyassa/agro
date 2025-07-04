@extends('layouts.superadmin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Superadmin Item Master</h2>
        <a href="{{ route('superadmin.item-masters.create') }}" class="btn btn-primary">Upload CSV</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Item Name</th>
                <th>Category Name</th>
                <th>Unit</th>
                <th>MOQ</th>
                <th>SKU Name Code</th>
                <th>Category Code</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->category_name }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ $item->moq }}</td>
                    <td>{{ $item->sku_name_code }}</td>
                    <td>{{ $item->category_code }}</td>
                    <td>
                        <a href="{{ route('superadmin.item-masters.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('superadmin.item-masters.destroy', $item->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection 