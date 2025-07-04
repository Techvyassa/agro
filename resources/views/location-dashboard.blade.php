@extends('layouts.location')

@section('title', 'Location User Dashboard')
@section('page-title', 'Location User Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert alert-info mt-4">
            <h4>Welcome, {{ \App\Models\LocationUser::find(Auth::id())?->name ?? 'Location User' }}!</h4>
            <p>You can upload ASN (Advance Shipping Notice) using the sidebar link.</p>
        </div>
    </div>
</div>
@endsection 