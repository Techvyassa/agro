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
            <!-- kjhldsfjls -->
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
                                        <th>
                                            Items
                                            <table class="table table-sm table-borderless mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="border-0 p-1">Item</th>
                                                        <th class="border-0 p-1">Qty</th>
                                                        <th class="border-0 p-1">Short Qty</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </th>
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
                                                    foreach ($group as $packing) {
                                                        $itemsData = $packing->items;

                                                        if (is_array($itemsData)) {
                                                            // Handle array of JSON strings
                                                            foreach ($itemsData as $itemString) {
                                                                if (is_string($itemString)) {
                                                                    $decoded = json_decode($itemString, true);
                                                                    if (is_array($decoded) && isset($decoded['item']) && isset($decoded['qty'])) {
                                                                        $allItems->push($decoded);
                                                                    }
                                                                } elseif (is_array($itemString) && isset($itemString['item']) && isset($itemString['qty'])) {
                                                                    $allItems->push($itemString);
                                                                }
                                                            }
                                                        } elseif (is_string($itemsData)) {
                                                            // Try to decode JSON string
                                                            $decoded = json_decode($itemsData, true);
                                                            if (is_array($decoded)) {
                                                                // If decoded result is an array of items
                                                                if (isset($decoded[0]) && is_array($decoded[0])) {
                                                                    foreach ($decoded as $item) {
                                                                        if (isset($item['item']) && isset($item['qty'])) {
                                                                            $allItems->push($item);
                                                                        }
                                                                    }
                                                                }
                                                                // If decoded result is a single item object
                                                                elseif (isset($decoded['item']) && isset($decoded['qty'])) {
                                                                    $allItems->push($decoded);
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Remove duplicates and group by item
                                                    $uniqueItems = $allItems->groupBy('item')->map(function ($group) {
                                                        return [
                                                            'item' => $group->first()['item'],
                                                            'qty' => $group->sum('qty')
                                                        ];
                                                    })->values();

                                                    // Get comparison data for this SO
                                                    $shortItems = $comparisonData[$so_no] ?? [];
                                                @endphp

                                                <table class="table table-sm table-borderless mb-0">
                                                    <tbody>
                                                        @php
                                                            // Combine picked items and shortages for comprehensive display
                                                            $allDisplayItems = collect();

                                                            // Add picked items
                                                            foreach ($uniqueItems as $item) {
                                                                $allDisplayItems->push([
                                                                    'item' => $item['item'],
                                                                    'picked_qty' => $item['qty'],
                                                                    'short_qty' => 0,
                                                                    'type' => 'picked'
                                                                ]);
                                                            }

                                                            // Add shortage items (only if not already shown as picked)
                                                            foreach ($shortItems as $shortItem) {
                                                                $existingItem = $allDisplayItems->where('item', $shortItem['item'])->first();
                                                                if ($existingItem) {
                                                                    // Update existing item with shortage info
                                                                    $existingItem['short_qty'] = $shortItem['short_qty'];
                                                                } else {
                                                                    // Add new shortage item
                                                                    $allDisplayItems->push([
                                                                        'item' => $shortItem['item'],
                                                                        'picked_qty' => 0,
                                                                        'short_qty' => $shortItem['short_qty'],
                                                                        'type' => 'shortage'
                                                                    ]);
                                                                }
                                                            }
                                                        @endphp

                                                        @foreach($allDisplayItems as $displayItem)
                                                            <tr>
                                                                <td class="p-1 border-0">
                                                                    @if($displayItem['type'] == 'shortage' && $displayItem['picked_qty'] == 0)
                                                                        <span class="text-muted">{{ $displayItem['item'] }}</span>
                                                                    @else
                                                                        {{ $displayItem['item'] }}
                                                                    @endif
                                                                </td>
                                                                <td class="p-1 border-0 text-center">
                                                                    @if($displayItem['picked_qty'] > 0)
                                                                        {{ $displayItem['picked_qty'] }}
                                                                    @else
                                                                        <span class="text-muted">0</span>
                                                                    @endif
                                                                </td>
                                                                <td class="p-1 border-0 text-center">
                                                                    @if($displayItem['short_qty'] > 0)
                                                                        <span
                                                                            class="text-danger fw-bold">{{ $displayItem['short_qty'] }}</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
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
                                                    <form action="{{ route('pickings.force-complete-so', $so_no) }}" method="POST"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Mark all packings for SO {{ $so_no }} as closed with shortage?')">
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