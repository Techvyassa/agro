@extends('layouts.dashboard')

@section('title', 'Short SO (Packings on Hold)')
@section('page-title', 'Short SO (Packings on Hold)')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>{{ $pickings->count() }}</strong> SO(s) present in Short (on hold)
        </div>
        <!--short so-->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Short SO (Packings on Hold)</h5>
            </div>
            <div class="card-body">
                @if(isset($pickings) && $pickings->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>SO No
                                        <a href="?sort=desc" class="ms-1"><i class="fas fa-sort-down"></i></a>
                                    </th>
                                    <th>Items</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pickings->sortKeysDesc() as $so_no => $group)
                                    <tr>
                                        <td>{{ $so_no }}</td>
                                        <td>
                                            @php
                                                $allItems = collect();
                                                foreach($group as $packing) {
                                                    if(is_array($packing->items)) {
                                                        $allItems = $allItems->merge($packing->items);
                                                    } elseif(is_string($packing->items)) {
                                                        $decoded = json_decode($packing->items, true);
                                                        if(is_array($decoded)) $allItems = $allItems->merge($decoded);
                                                    }
                                                }
                                            @endphp
                                            {{ $allItems->unique()->implode(', ') }}
                                        </td>
                                        <td>
                                            @if($group->contains('status', 'hold'))
                                                <span class="badge bg-warning text-dark">hold</span>
                                            @elseif($group->contains('status', 'force_completed'))
                                                <span class="badge bg-info text-dark">closed with shortage</span>
                                            @else
                                                <span class="badge bg-success">completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($group->contains('status', 'hold'))
                                                <form action="{{ route('pickings.force-complete-so', $so_no) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark all packings for SO {{ $so_no }} as closed with shortage?')">
                                                        Close with shortage
                                                    </button>
                                                </form>
                                            @elseif($group->contains('status', 'force_completed'))
                                                <span class="text-info">Closed with shortage</span>
                                            @else
                                                <span class="text-success">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">No packings on hold found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 