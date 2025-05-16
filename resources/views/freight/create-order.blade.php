@extends('layouts.app')

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <a href="{{ route('freight.calculator') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Freight Calculator
            </a>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Create New Order</h4>
        </div>
        <div class="card-body">
            <form id="orderForm">
                <!-- Order Details -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="orderIds" class="form-label">Order ID*</label>
                                <input type="text" class="form-control" id="orderIds" name="orderIds" required>
                            </div>
                            <div class="col-md-6">
                                <label for="orderDate" class="form-label">Order Date*</label>
                                <input type="date" class="form-control" id="orderDate" name="orderDate" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pickup Location -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pickup Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="pickUpId" class="form-label">Pickup ID*</label>
                                <input type="text" class="form-control" id="pickUpId" name="pickUpId" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pickUpCity" class="form-label">Pickup City*</label>
                                <input type="text" class="form-control" id="pickUpCity" name="pickUpCity" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pickUpState" class="form-label">Pickup State*</label>
                                <input type="text" class="form-control" id="pickUpState" name="pickUpState" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Return Location -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Return Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="retrunId" class="form-label">Return ID</label>
                                <input type="text" class="form-control" id="retrunId" name="retrunId">
                            </div>
                            <div class="col-md-6">
                                <label for="returnCity" class="form-label">Return City</label>
                                <input type="text" class="form-control" id="returnCity" name="returnCity">
                            </div>
                            <div class="col-md-6">
                                <label for="returnState" class="form-label">Return State</label>
                                <input type="text" class="form-control" id="returnState" name="returnState">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Buyer Information -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Buyer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="buyerFName" class="form-label">First Name*</label>
                                <input type="text" class="form-control" id="buyerFName" name="buyerFName" required>
                            </div>
                            <div class="col-md-6">
                                <label for="buyerLName" class="form-label">Last Name*</label>
                                <input type="text" class="form-control" id="buyerLName" name="buyerLName" required>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="buyerEmail" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="buyerEmail" name="buyerEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label for="buyerMobile" class="form-label">Mobile Number*</label>
                                <input type="text" class="form-control" id="buyerMobile" name="buyerMobile" required>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="buyerAddress" class="form-label">Address*</label>
                                <input type="text" class="form-control" id="buyerAddress" name="buyerAddress" required>
                            </div>
                            <div class="col-md-6">
                                <label for="destinationPincode" class="form-label">Pincode*</label>
                                <input type="text" class="form-control" id="destinationPincode" name="destinationPincode" required>
                            </div>
                            <input type="hidden" id="buyerState" name="buyerState">
                        </div>
                    </div>
                </div>
                
                <!-- Item & Box Details -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Item Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="itemName" class="form-label">Item Name*</label>
                                <input type="text" class="form-control" id="itemName" name="itemName" required>
                            </div>
                            <div class="col-md-3">
                                <label for="codAmt" class="form-label">COD Amount</label>
                                <input type="number" class="form-control" id="codAmt" name="codAmt" value="0">
                            </div>
                            <div class="col-md-3">
                                <label for="invoiceAmt" class="form-label">Invoice Amount*</label>
                                <input type="number" class="form-control" id="invoiceAmt" name="invoiceAmt" required>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="qty" class="form-label">Quantity*</label>
                                <input type="number" class="form-control" id="qty" name="qty" min="1" value="1" required>
                            </div>
                            <div class="col-md-4">
                                <label for="length" class="form-label">Length (cm)</label>
                                <input type="number" class="form-control" id="length" name="length">
                            </div>
                            <div class="col-md-4">
                                <label for="width" class="form-label">Width (cm)</label>
                                <input type="number" class="form-control" id="width" name="width">
                            </div>
                            <div class="col-md-4">
                                <label for="height" class="form-label">Height (cm)</label>
                                <input type="number" class="form-control" id="height" name="height">
                            </div>
                            <div class="col-md-4">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control" id="weight" name="weight">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Response Popup -->
<div id="responsePopup" class="response-popup">
    <div class="response-content">
        <span class="close-response">&times;</span>
        <h4 id="responseTitle">Response</h4>
        <div id="responseContent"></div>
        <button class="btn btn-secondary mt-3" id="closeResponseBtn">Close</button>
    </div>
</div>

<div id="overlay" class="loading-overlay">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@endsection

@section('styles')
<style>
    .response-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        max-width: 80%;
        width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .response-content {
        position: relative;
        width: 100%;
    }
    
    .close-response {
        position: absolute;
        top: 0;
        right: 0;
        cursor: pointer;
        font-size: 24px;
    }
    
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
</style>
@endsection

@section('scripts')
<script src="order-response.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prefill form with URL parameters if available
    function prefillFormFromUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Prefill fields from URL parameters
        if (urlParams.has('pickUpId')) {
            document.getElementById('pickUpId').value = urlParams.get('pickUpId');
        }
        
        if (urlParams.has('pickUpCity')) {
            document.getElementById('pickUpCity').value = urlParams.get('pickUpCity');
        }
        
        if (urlParams.has('pickUpState')) {
            document.getElementById('pickUpState').value = urlParams.get('pickUpState');
        }
        
        // Set return location same as pickup location by default
        if (urlParams.has('pickUpId')) {
            document.getElementById('retrunId').value = document.getElementById('pickUpId').value;
        }
        
        if (urlParams.has('pickUpCity')) {
            document.getElementById('returnCity').value = document.getElementById('pickUpCity').value;
        }
        
        if (urlParams.has('pickUpState')) {
            document.getElementById('returnState').value = document.getElementById('pickUpState').value;
        }
        
        // Don't auto-generate Order ID - let user input manually
        
        // Let user input Order Date manually - no auto-setting to today
        
        // Populate destination details from pincode if available
        if (urlParams.has('destinationPincode')) {
            document.getElementById('destinationPincode').value = urlParams.get('destinationPincode');
        }
        
        // Populate item dimensions if available
        if (urlParams.has('length')) {
            document.getElementById('length').value = urlParams.get('length');
        }
        
        if (urlParams.has('width')) {
            document.getElementById('width').value = urlParams.get('width');
        }
        
        if (urlParams.has('height')) {
            document.getElementById('height').value = urlParams.get('height');
        }
        
        if (urlParams.has('weight')) {
            document.getElementById('weight').value = urlParams.get('weight');
        }
        
        // Set default invoice amount from URL if available
        if (urlParams.has('invoiceAmt')) {
            document.getElementById('invoiceAmt').value = urlParams.get('invoiceAmt');
        }
        
        // Let user input Item Details manually
        // We won't set default values for Item Name
        
        // Let user input Buyer Information manually
        // No default values for buyer name, email, mobile, address
        // Only COD amount gets default to 0 if not entered
        if (!document.getElementById('codAmt').value) {
            document.getElementById('codAmt').value = '0';
        }
    }
    
    prefillFormFromUrlParams();
    
    // Handle form submission
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitOrder();
    });
});
</script>
@endsection
