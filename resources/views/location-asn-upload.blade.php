@extends('layouts.location')

@section('title', 'Upload ASN')
@section('page-title', 'Upload ASN')

@section('content')
<div class="row">
    <div class="col-12">
       
        <form method="POST" action="#" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="asn_file" class="form-label">ASN File (PDF, CSV, etc.)</label>
                <input type="file" class="form-control" id="asn_file" name="asn_file" required>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>
@endsection 