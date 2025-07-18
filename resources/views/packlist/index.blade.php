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
                            <button type="button" class="btn btn-primary" id="generateButton" disabled>
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
<style>
.custom-dropdown { position: relative; width: 100%; }
.dropdown-selected { border: 1px solid #ced4da; padding: 8px 12px; border-radius: 4px; background: #fff; cursor: pointer; }
.dropdown-list { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ced4da; border-top: none; z-index: 1000; border-radius: 0 0 4px 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.dropdown-option { padding: 8px 12px; cursor: pointer; }
.dropdown-option:hover, .dropdown-option.active { background: #f1f1f1; }
</style>
<script>
$(document).ready(function() {
    // Custom dropdown logic (copied from freight calculator)
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
        const boxSelect = $('#box');
        // Reset box selection
        boxSelect.html('<option value="all">-- All Boxes --</option>');
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
        // Print Packlist button logic
        $('#generateButton').off('click').on('click', function(e) {
            e.preventDefault();
            const soNo = $hiddenInput.val();
            const boxVal = $('#box').val();
            if (boxVal === 'all') {
                if (soNo) {
                    window.open(`/packlist/print/${soNo}/all`, '_blank');
                } else {
                    alert('Please select a Sales Order Number.');
                }
            } else {
                $('#packlistForm')[0].submit();
            }
        });
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
                    response.forEach(function(item) {
                        boxSelect.append(
                            $('<option></option>')
                                .val(item.box)
                                .text(item.box)
                                .data('dimension', item.dimension)
                                .data('weight', item.weight)
                        );
                    });
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
        if (selectedOption.val() !== 'all') {
            $('#boxDimension').text(selectedOption.data('dimension') || 'Not specified');
            $('#boxWeight').text(selectedOption.data('weight') || 'Not specified');
        } else {
            $('#boxDimension').text('Multiple dimensions');
            $('#boxWeight').text('Multiple weights');
        }
    });
});
</script>
@endsection
@endsection
