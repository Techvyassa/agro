@extends('layouts.dashboard')

@section('title', 'Calculate Freight')
@section('page-title', 'Calculate Freight')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Calculate Freight Cost
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="so_no" class="col-md-4 col-form-label">Sales Order Number</label>
                    <div class="col-md-8">
                        <select id="so_no" class="form-select" name="so_no" required>
                            <option value="">-- Select SO Number --</option>
                            @foreach($so_numbers as $so_no)
                                <option value="{{ $so_no }}">{{ $so_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div id="shipmentDetailsContainer" style="display: none;">
                    <div class="alert alert-info">
                        <h6>Shipment Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Total Boxes:</strong> <span id="totalBoxes">0</span></p>
                                <p><strong>Total Weight:</strong> <span id="totalWeight">0</span> kg</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Dimensions:</strong> <span id="boxDimensions">-</span></p>
                            </div>
                        </div>
                        <div id="individualBoxes" class="mt-3">
                            <h6>Individual Box Details</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Box</th>
                                            <th>Weight (kg)</th>
                                            <th>Dimensions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="boxesTable">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="button" class="btn btn-success" id="redirectFreightButton" disabled>
                            Calculate Freight Cost
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Global variables to store shipment details
        let shipmentData = {
            totalWeight: 0,
            dimensions: '',
            boxes: []
        };
        
        // Initialize Select2 for SO Number dropdown
        $('#so_no').select2({
            placeholder: '-- Select SO Number --',
            allowClear: true,
            width: '100%'
        });

        // When SO number changes
        $('#so_no').on('change', function() {
            const soNo = $(this).val();
            
            if (!soNo) {
                $('#redirectFreightButton').prop('disabled', true);
                $('#shipmentDetailsContainer').hide();
                return;
            }
            
            // Fetch box details for this SO
            $.ajax({
                url: "{{ route('freight.getBoxDetails') }}",
                method: 'GET',
                data: {
                    'so_no': soNo
                },
                success: function(response) {
                    if (response.success) {
                        // Store shipment data
                        shipmentData.totalWeight = response.totalWeight;
                        shipmentData.dimensions = response.dimensions;
                        shipmentData.boxes = response.boxes;
                        
                        // Update UI
                        $('#totalBoxes').text(response.boxes.length);
                        $('#totalWeight').text(parseFloat(response.totalWeight).toFixed(2));
                        $('#boxDimensions').text(response.dimensions || 'Not specified');
                        
                        // Update individual boxes table
                        const boxesTable = $('#boxesTable');
                        boxesTable.empty();
                        
                        response.boxes.forEach(function(box) {
                            let boxWeight = parseFloat(box.weight) || 0;
                            let row = `
                                <tr>
                                    <td>${box.box}</td>
                                    <td>${boxWeight.toFixed(2)}</td>
                                    <td>${box.dimension || 'Not specified'}</td>
                                </tr>
                            `;
                            boxesTable.append(row);
                        });
                        
                        // Show shipment details
                        $('#shipmentDetailsContainer').show();
                        
                        // Enable calculate button
                        $('#redirectFreightButton').prop('disabled', false);
                    } else {
                        alert(response.message);
                        $('#redirectFreightButton').prop('disabled', true);
                        $('#shipmentDetailsContainer').hide();
                    }
                },
                error: function(error) {
                    console.error('Error fetching box details:', error);
                    alert('Failed to fetch box details for this sales order');
                    $('#redirectFreightButton').prop('disabled', true);
                    $('#shipmentDetailsContainer').hide();
                }
            });
        });
        
        // Redirect to freight-redirect.php with shipment details
        $('#redirectFreightButton').on('click', function() {
            if (shipmentData.boxes.length === 0) {
                alert('No box details available for this sales order');
                return;
            }

            // Create a form and submit via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/freight-redirect.php';

            // Add SO number
            const soInput = document.createElement('input');
            soInput.type = 'hidden';
            soInput.name = 'so_no';
            soInput.value = $('#so_no').val();
            form.appendChild(soInput);

            // Add boxes data
            const boxesInput = document.createElement('input');
            boxesInput.type = 'hidden';
            boxesInput.name = 'boxes';
            boxesInput.value = encodeURIComponent(JSON.stringify(shipmentData.boxes));
            form.appendChild(boxesInput);

            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
@endsection
@endsection
