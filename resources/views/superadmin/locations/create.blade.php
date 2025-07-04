@extends('layouts.superadmin')
@section('title', 'Add Location')
@section('page-title', 'Add Location')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Add Location</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.locations.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Location Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="code" class="form-label">Location Code</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Location</button>
        </form>
    </div>
</div>
@endsection 