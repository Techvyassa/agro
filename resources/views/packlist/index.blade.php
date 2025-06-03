@extends('layouts.dashboard')

@section('title', 'Print Packlist')
@section('page-title', 'Print Packlist')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Print Packlist
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('packlist.generate') }}" id="packlistForm">
                    @csrf
                    
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
                    
                    <div id="boxDetailsContainer" style="display: none;">
                        <div class="row mb-3">
                            <label for="box" class="col-md-4 col-form-label">Box Number</label>
                            <div class="col-md-8">
                                <select id="box" class="form-select" name="box">
                                    <option value="">-- All Boxes --</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <tbody>
                                            <tr>
                                                <th style="width: 40%">Dimension</th>
                                                <td id="boxDimension">-</td>
                                            </tr>
                                            <tr>
                                                <th>Weight</th>
                                                <td id="boxWeight">-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary" id="generateButton" disabled>
                                Print Packlist
                            </button>
                           
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Freight Calculation Modal -->
<div class="modal fade" id="freightCalculationModal" tabindex="-1" aria-labelledby="freightCalculationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="freightCalculationModalLabel">Freight Cost Calculation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="freightLoadingSpinner" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Calculating freight costs...</p>
                </div>
                <div id="freightResults" style="display: none;">
                    <div class="alert alert-info mb-3">
                        <h6>Shipment Details</h6>
                        <div id="shipmentDetails"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Carrier</th>
                                    <th>Service</th>
                                    <th>Rate (₹)</th>
                                    <th>Est. Delivery</th>
                                </tr>
                            </thead>
                            <tbody id="freightRatesTable">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="freightError" class="alert alert-danger" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // When SO number changes
        $('#so_no').on('change', function() {
            const soNo = $(this).val();
            const boxSelect = $('#box');
            
            // Reset box selection
            boxSelect.html('<option value="">-- All Boxes --</option>');
            $('#boxDimension').text('-');
            $('#boxWeight').text('-');
            
            if (!soNo) {
                $('#boxDetailsContainer').hide();
                $('#generateButton').prop('disabled', true);
                $('#calculateFreightButton').prop('disabled', true);
                return;
            }
            
            // Enable generate button when SO is selected
            $('#generateButton').prop('disabled', false);
            $('#calculateFreightButton').prop('disabled', false);
            
            // Fetch boxes for this SO
            $.ajax({
                url: "{{ route('packlist.getBoxes') }}",
                method: 'GET',
                data: {
                    'so_no': soNo
                },
                success: function(response) {
                    if (response.length > 0) {
                        // Add boxes to dropdown
                        response.forEach(function(item) {
                            boxSelect.append(
                                $('<option></option>')
                                    .val(item.box)
                                    .text(item.box)
                                    .data('dimension', item.dimension)
                                    .data('weight', item.weight)
                            );
                        });
                        
                        // Show boxes container
                        $('#boxDetailsContainer').show();
                    }
                },
                error: function(error) {
                    console.error('Error fetching boxes:', error);
                }
            });
        });
        
        // When box changes, update details
        $('#box').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            
            if (selectedOption.val()) {
                // Update box details
                $('#boxDimension').text(selectedOption.data('dimension') || 'Not specified');
                $('#boxWeight').text(selectedOption.data('weight') || 'Not specified');
            } else {
                $('#boxDimension').text('Multiple dimensions');
                $('#boxWeight').text('Multiple weights');
            }
        });
        
        // Calculate freight cost button click handler
        $('#calculateFreightButton').on('click', function() {
            const soNo = $('#so_no').val();
            const boxNumber = $('#box').val();
            
            if (!soNo) {
                alert('Please select a Sales Order Number first');
                return;
            }
            
            // Get dimensions and weight
            let dimensions = '';
            let weight = 0;
            
            if (boxNumber) {
                // Single box selected
                const selectedOption = $('#box').find('option:selected');
                dimensions = selectedOption.data('dimension') || '';
                weight = parseFloat(selectedOption.data('weight')) || 0;
            } else {
                // Multiple boxes - need to calculate total dimensions and weight
                let totalWeight = 0;
                let boxDimensions = [];
                
                $('#box option').each(function() {
                    if ($(this).val()) {
                        boxDimensions.push($(this).data('dimension'));
                        totalWeight += parseFloat($(this).data('weight') || 0);
                    }
                });
                
                dimensions = boxDimensions.join(', ');
                weight = totalWeight;
            }
            
            // Open the modal
            $('#freightCalculationModal').modal('show');
            $('#freightLoadingSpinner').show();
            $('#freightResults').hide();
            $('#freightError').hide();
            
            // Prepare data for API
            const freightData = {
                "source_pincode": "110001", // Default pincode (can be replaced with actual source pincode)
                "destination_pincode": "400001", // Default pincode (can be replaced with actual destination pincode)
                "weight": weight, // in kg
                "dimensions": dimensions,
                "order_id": soNo
            };
            
            // Call the freight API via proxy
            $.ajax({
                url: "{{ asset('freight-proxy.php') }}",
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(freightData),
                success: function(response) {
                    $('#freightLoadingSpinner').hide();
                    
                    // Display shipment details
                    let detailsHTML = `
                        <p><strong>Order ID:</strong> ${soNo}</p>
                        <p><strong>Weight:</strong> ${weight} kg</p>
                        <p><strong>Dimensions:</strong> ${dimensions || 'Not specified'}</p>
                    `;
                    $('#shipmentDetails').html(detailsHTML);
                    
                    // Display freight rates
                    const ratesTableBody = $('#freightRatesTable');
                    ratesTableBody.empty();
                    
                    if (response && response.carriers && response.carriers.length > 0) {
                        response.carriers.forEach(function(carrier) {
                            let rowHTML = `
                                <tr>
                                    <td>${carrier.name}</td>
                                    <td>${carrier.service_type || 'Standard'}</td>
                                    <td>₹${carrier.rate.toFixed(2)}</td>
                                    <td>${carrier.estimated_delivery_days || 'N/A'} days</td>
                                </tr>
                            `;
                            ratesTableBody.append(rowHTML);
                        });
                        $('#freightResults').show();
                    } else {
                        $('#freightError').text('No freight rates available for this shipment.').show();
                    }
                },
                error: function(error) {
                    $('#freightLoadingSpinner').hide();
                    $('#freightError').text('Failed to retrieve freight rates. Please try again later.').show();
                    console.error('Error fetching freight rates:', error);
                }
            });
        });
    });
</script>
@endsection
@endsection
