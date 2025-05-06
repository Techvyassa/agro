@extends('layouts.dashboard')

@section('title', 'Packlist Results')
@section('page-title', 'Packlist Results')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Packlist Results</span>
        <div>
            @if($packitems->count() > 0)
                @php
                    $firstItem = $packitems->first();
                    $printUrl = route('packlist.print', ['so_no' => $firstItem->so_no, 'box' => $firstItem->box]);
                @endphp
                <a href="{{ $printUrl }}" class="btn btn-primary" target="_blank">
                    <i class="fa fa-print"></i> Print
                </a>
            @endif
            <a href="{{ route('packlist.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Box</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Dimension</th>
                        <th>Weight</th>
                        <th>SO Number</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @if($packitems->count() > 0)
                        @foreach($packitems as $item)
                            @php
                                $itemsArray = json_decode($item->items, true);
                            @endphp
                            @if(is_array($itemsArray))
                                @foreach($itemsArray as $packItem)
                                    @php
                                        $itemData = json_decode($packItem, true);
                                    @endphp
                                    <tr>
                                        <td>{{ $item->box }}</td>
                                        <td>{{ $itemData['item'] ?? 'N/A' }}</td>
                                        <td>{{ $itemData['qty'] ?? 'N/A' }}</td>
                                        <td>{{ $item->dimension }}</td>
                                        <td>{{ $item->weight }}</td>
                                        <td>{{ $item->so_no }}</td>
                                        <td>{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No packlist items found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
