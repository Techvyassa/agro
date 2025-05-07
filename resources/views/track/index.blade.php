@extends('layouts.dashboard')

@section('title', 'Track Shipment Status')

@section('page-title', 'Track Shipment Status')

@section('breadcrumb')
    <li class="breadcrumb-item active">Track Status</li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Track Shipment Status</h5>
                </div>
                <div class="card-body">
                    <form id="trackStatusForm">
                        <div class="mb-3">
                            <label for="service_name" class="form-label">Courier Service</label>
                            <select class="form-select" id="service_name" name="service_name" required>
                                <option value="" selected disabled>Select a courier service</option>
                                @if(count($courierServices) > 0)
                                    @foreach($courierServices as $service)
                                        <option value="{{ $service['code'] }}">{{ $service['name'] }}</option>
                                    @endforeach
                                @else
                                    <option value="delhivery">Delhivery</option>
                                    <option value="bigship">BigShip</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="search_value" class="form-label">Tracking Number</label>
                            <input type="text" class="form-control" id="search_value" name="search_value" placeholder="Enter tracking number" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Track Shipment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4" id="resultContainer" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tracking Results</h5>
                </div>
                <div class="card-body" id="trackingResults">
                    <!-- Results will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('#trackStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        const serviceProvider = $('#service_name').val();
        const trackingNumber = $('#search_value').val();
        
        if (!serviceProvider || !trackingNumber) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Show loading indicator
        $('#trackingResults').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Fetching tracking information...</p></div>');
        $('#resultContainer').show();
        
        // Call the API through our direct PHP proxy
        $.ajax({
            url: '/track-status-proxy.php',
            type: 'POST',
            data: JSON.stringify({
                service_name: serviceProvider,
                search_value: trackingNumber
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                displayTrackingResults(response);
            },
            error: function(xhr, status, error) {
                let errorMessage = 'An error occurred while tracking the shipment.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ' ' + xhr.responseJSON.message;
                }
                
                $('#trackingResults').html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                        <p>${errorMessage}</p>
                    </div>
                `);
            }
        });
    });
    
    function displayTrackingResults(data) {
        let html = '';
        
        if (data.error) {
            html = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                    <p>${data.message || data.error}</p>
                </div>
            `;
        } else if (data.result) {
            // Process the actual API response structure
            const trackData = data.result;
            
            html = `
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Tracking Information Found</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
            `;
            
            // Add main tracking fields
            html += `<tr><th>AWB/Tracking Number</th><td>${trackData.awb || 'N/A'}</td></tr>`;
            html += `<tr><th>Courier</th><td>${trackData.courier_name || 'N/A'}</td></tr>`;
            html += `<tr><th>Status</th><td><span class="badge bg-${trackData.status === 'LOST' ? 'danger' : trackData.status === 'DELIVERED' ? 'success' : 'warning'}">${trackData.status || 'N/A'}</span></td></tr>`;
            html += `<tr><th>Order ID</th><td>${trackData.order_id || 'N/A'}</td></tr>`;
            html += `<tr><th>User Order ID</th><td>${trackData.user_order_id || 'N/A'}</td></tr>`;
            html += `<tr><th>Shipment Date</th><td>${trackData.shipment_date ? new Date(trackData.shipment_date).toLocaleString() : 'N/A'}</td></tr>`;
            html += `<tr><th>Delivery Date</th><td>${trackData.delivery_date ? new Date(trackData.delivery_date).toLocaleString() : 'Pending'}</td></tr>`;
            
            if (trackData.cod_amount !== undefined) {
                html += `<tr><th>COD Amount</th><td>₹${trackData.cod_amount.toFixed(2)}</td></tr>`;
            }
            
            if (trackData.order_value !== undefined) {
                html += `<tr><th>Order Value</th><td>₹${trackData.order_value.toFixed(2)}</td></tr>`;
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            // Receiver information
            if (trackData.receiver) {
                html += `
                    <h5 class="mt-4">Receiver Information</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>Name</th><td>${trackData.receiver.name || 'N/A'}</td></tr>
                                <tr><th>Mobile</th><td>${trackData.receiver.mobile || 'N/A'}</td></tr>
                                <tr><th>Address</th><td>${trackData.receiver.address || 'N/A'}</td></tr>
                                <tr><th>Pincode</th><td>${trackData.receiver.pincode || 'N/A'}</td></tr>
                                <tr><th>City</th><td>${trackData.receiver.city || 'N/A'}</td></tr>
                                <tr><th>State</th><td>${trackData.receiver.state || 'N/A'}</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            // Sender information
            if (trackData.sender) {
                html += `
                    <h5 class="mt-4">Sender Information</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>Name</th><td>${trackData.sender.name || 'N/A'}</td></tr>
                                <tr><th>Mobile</th><td>${trackData.sender.mobile || 'N/A'}</td></tr>
                                <tr><th>Address</th><td>${trackData.sender.address || 'N/A'}</td></tr>
                                <tr><th>Pincode</th><td>${trackData.sender.pincode || 'N/A'}</td></tr>
                                <tr><th>City</th><td>${trackData.sender.city || 'N/A'}</td></tr>
                                <tr><th>State</th><td>${trackData.sender.state || 'N/A'}</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            // Weight information
            if (trackData.weight) {
                html += `
                    <h5 class="mt-4">Weight Information</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>Actual Weight</th><td>${trackData.weight.actual ? trackData.weight.actual + ' kg' : 'N/A'}</td></tr>
                                <tr><th>Volumetric Weight</th><td>${trackData.weight.volumetric ? trackData.weight.volumetric + ' kg' : 'N/A'}</td></tr>
                                <tr><th>Applied Weight</th><td>${trackData.weight.applied ? trackData.weight.applied + ' kg' : 'N/A'}</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            // Box information
            if (trackData.box && trackData.box.dimensions && trackData.box.dimensions.length > 0) {
                html += `
                    <h5 class="mt-4">Package Information</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Number of Boxes</th>
                                    <th>Length</th>
                                    <th>Breadth</th>
                                    <th>Height</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                trackData.box.dimensions.forEach((dim, index) => {
                    html += `
                        <tr>
                            <td>${index === 0 ? trackData.box.count || 1 : ''}</td>
                            <td>${dim.length ? dim.length + ' cm' : 'N/A'}</td>
                            <td>${dim.breadth ? dim.breadth + ' cm' : 'N/A'}</td>
                            <td>${dim.height ? dim.height + ' cm' : 'N/A'}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            // Product information
            if (trackData.product && trackData.product.length > 0) {
                html += `
                    <h5 class="mt-4">Product Information</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                trackData.product.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.name || 'N/A'}</td>
                            <td>${item.qty || 'N/A'}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            // Extra information with 'Show More' button
            if (trackData.extra) {
                html += `
                    <h5 class="mt-4">Additional Information</h5>
                    <div class="card">
                        <div class="card-body">
                            <div id="extraInfoSummary">
                                <!-- Show limited information initially -->
                                <p>This shipment contains additional details. <button class="btn btn-sm btn-outline-primary" id="showMoreBtn">Show More</button></p>
                            </div>
                            <div id="extraInfoFull" style="display: none;">
                                <!-- Full extra information will be shown here -->
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                `;
                                        
                for (const key in trackData.extra) {
                    let value = trackData.extra[key];
                    if (typeof value === 'boolean') {
                        value = value ? 'Yes' : 'No';
                    } else if (typeof value === 'object') {
                        value = JSON.stringify(value, null, 2);
                    }
                    
                    html += `<tr><th>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</th><td>${value}</td></tr>`;
                }
                
                html += `
                                        </tbody>
                                    </table>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" id="showLessBtn">Show Less</button>
                            </div>
                        </div>
                    </div>
                `;
            }
            
        } else {
            html = `
                <div class="alert alert-warning">
                    <h5><i class="fas fa-info-circle"></i> No Information</h5>
                    <p>No tracking information was found for the provided details.</p>
                    <pre class="mt-3 border p-3 bg-light" style="max-height: 200px; overflow: auto">${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }
        
        $('#trackingResults').html(html);
        
        // Add event listeners for Show More/Less buttons
        $('#showMoreBtn').on('click', function() {
            $('#extraInfoSummary').hide();
            $('#extraInfoFull').show();
        });
        
        $('#showLessBtn').on('click', function() {
            $('#extraInfoFull').hide();
            $('#extraInfoSummary').show();
        });
    }
});
</script>
@endsection
