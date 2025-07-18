@extends('layouts.dashboard')

@section('title', 'Short SO (Pickings on Hold)')
@section('page-title', 'Short SO (Pickings on Hold)')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="alert alert-primary d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>{{ $pickings->count() }}</strong> SO(s) present in Short (on hold)
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Short SO (Pickings on Hold)</h5>
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
                                                foreach($group as $picking) {
                                                    if(is_array($picking->items)) {
                                                        $allItems = $allItems->merge($picking->items);
                                                    } elseif(is_string($picking->items)) {
                                                        $decoded = json_decode($picking->items, true);
                                                        if(is_array($decoded)) $allItems = $allItems->merge($decoded);
                                                    }
                                                }
                                            @endphp
                                            {{ $allItems->unique()->implode(', ') }}
                                        </td>
                                        <td>
                                            @if($group->contains('status', 'hold'))
                                                <span class="badge bg-warning text-dark">hold</span>
                                            @else
                                                <span class="badge bg-success">completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($group->contains('status', 'hold'))
                                                <form action="{{ route('pickings.force-complete-so', $so_no) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark all pickings for SO {{ $so_no }} as completed?')">
                                                        Close
                                                    </button>
                                                </form>
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
                    <div class="alert alert-info">No pickings on hold found.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 