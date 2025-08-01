<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packlist Print - {{ $so_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .order-info {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print();">Print Packlist</button>
        <button onclick="window.close();">Close</button>
    </div>

    @if(!$box || $box === 'all')
    @php
        $grouped = $packitems->groupBy('box');
    @endphp
    @foreach($grouped as $boxNo => $items)
        <div class="print-header">
            <h1>PACKING LIST</h1>
        </div>
        <div class="order-info">
            <div>
                <p><strong>Sales Order Number:</strong> {{ $so_no }}</p>
                <p><strong>Box Number:</strong> {{ $boxNo }}</p>
                <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
                <p><strong>Dimension:</strong> {{ $items->first()->dimension ?? '-' }}</p>
                <p><strong>Weight:</strong> {{ $items->first()->weight ?? '-' }}</p>
            </div>
            <div>
                <p><strong>Generated On:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <!-- <th>Dimension</th>
                    <th>Weight</th> -->
                </tr>
            </thead>
            <tbody>
                @php $serial = 1; @endphp
                @foreach($items as $item)
                    @php
                        $itemsArray = json_decode($item->items, true);
                    @endphp
                    @if(is_array($itemsArray))
                        @foreach($itemsArray as $packItem)
                            @php
                                $itemData = json_decode($packItem, true);
                            @endphp
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td>{{ $itemData['item'] ?? 'N/A' }}</td>
                                <td>{{ $itemData['qty'] ?? 'N/A' }}</td>
                                <!-- <td>{{ $item->dimension }}</td>
                                <td>{{ $item->weight }}</td> -->
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
        <hr style="page-break-after: always; border: none; margin: 40px 0;">
    @endforeach
@else
    <div class="print-header">
        <h1>PACKING LIST</h1>
    </div>
    <div class="order-info">
        <div>
            <p><strong>Sales Order Number:</strong> {{ $so_no }}</p>
            <p><strong>Box Number:</strong> {{ $box }}</p>
            <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
            <p><strong>Dimension:</strong> {{ $packitems->first()->dimension ?? '-' }}</p>
            <p><strong>Weight:</strong> {{ $packitems->first()->weight ?? '-' }}</p>
        </div>
        <div>
            <p><strong>Generated On:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Serial No</th>
                <th>Item Name</th>
                <th>Quantity</th>
                     <th>Dimension</th>
                    <th>Weight</th> 
            </tr>
        </thead>
        <tbody>
            @php $serial = 1; @endphp
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
                            <td>{{ $serial++ }}</td>
                            <td>{{ $itemData['item'] ?? 'N/A' }}</td>
                            <td>{{ $itemData['qty'] ?? 'N/A' }}</td>
                             <td>{{ $item->dimension }}</td>
                            <td>{{ $item->weight }}</td> 
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
@endif

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
