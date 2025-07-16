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
                        <div id="customDropdown" class="custom-dropdown">
                            <div id="dropdownSelected" class="dropdown-selected">-- Select SO Number --</div>
                            <div id="dropdownList" class="dropdown-list" style="display:none;">
                                <input type="text" id="dropdownSearch" class="form-control mb-2" placeholder="Search SO Number...">
                                <div id="dropdownOptions" class="dropdown-options" style="max-height:200px;overflow-y:auto;">
                                    @foreach($so_numbers as $so_no)
                                        <div class="dropdown-option" data-value="{{ $so_no }}">{{ $so_no }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="so_no" name="so_no" value="">
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
<style>
.custom-dropdown { position: relative; width: 100%; }
.dropdown-selected { border: 1px solid #ced4da; padding: 8px 12px; border-radius: 4px; background: #fff; cursor: pointer; }
.dropdown-list { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ced4da; border-top: none; z-index: 1000; border-radius: 0 0 4px 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.dropdown-option { padding: 8px 12px; cursor: pointer; }
.dropdown-option:hover, .dropdown-option.active { background: #f1f1f1; }
</style>
<script>
$(document).ready(function() {
    // Global variables to store shipment details
    let shipmentData = {
        totalWeight: 0,
        dimensions: '',
        boxes: []
    };
    
    // Custom dropdown logic
    var $dropdown = $('#customDropdown');
    var $selected = $('#dropdownSelected');
    var $list = $('#dropdownList');
    var $search = $('#dropdownSearch');
    var $options = $('#dropdownOptions');
    var $hiddenInput = $('#so_no');

    $selected.on('click', function(e) {
        $list.toggle();
        $search.val('');
        $options.children().show();
        $search.focus();
    });

    $search.on('keyup', function() {
        var filter = $(this).val().toUpperCase();
        $options.children('.dropdown-option').each(function() {
            if ($(this).text().toUpperCase().indexOf(filter) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $options.on('click', '.dropdown-option', function() {
        var value = $(this).data('value');
        var text = $(this).text();
        $selected.text(text);
        $hiddenInput.val(value).trigger('change');
        $list.hide();
    });

    // Hide dropdown if clicked outside
    $(document).on('mousedown', function(e) {
        if (!$dropdown.is(e.target) && $dropdown.has(e.target).length === 0) {
            $list.hide();
        }
    });

    // When SO number changes (triggered by custom dropdown)
    $hiddenInput.on('change', function() {
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
        soInput.value = $hiddenInput.val();
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
