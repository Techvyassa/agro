@extends('layouts.superadmin')
@section('title', 'Edit Location')
@section('page-title', 'Edit Location')
@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Edit Location</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.locations.update', $location) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Location Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $location->name }}" required>
            </div>
            <div class="mb-3">
                <label for="code" class="form-label">Location Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $location->code }}" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Location</button>
        </form>
    </div>
</div>
@endsection 