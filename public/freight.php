<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freight Estimation Tool</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card { margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .result-card { transition: all 0.3s ease; }
        .result-card:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
        .loader { display: none; border: 5px solid #f3f3f3; border-top: 5px solid #3498db; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .extra-details { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .btn-primary { background-color: #4CAF50; border-color: #4CAF50; }
        .btn-primary:hover { background-color: #3e8e41; border-color: #3e8e41; }
        .badge { font-size: 0.85rem; }
    </style>
</head>
<body>
<?php
// Inject POST data as hidden fields for JS to access
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $safeKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
        $safeValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        echo "<input type='hidden' name='{$safeKey}' value='{$safeValue}'>\n";
    }
}
?>
    <div class="container py-5">
        <h1 class="mb-4 text-center">Freight Estimation Tool</h1>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Shipping Details</h5>
            </div>
            <div class="card-body">
                <form id="freightForm">
                    <div class="row">
                        <!-- Source and Destination -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">Locations</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="sourcePincode" class="form-label">Source Pincode</label>
                                        <input type="text" class="form-control" id="sourcePincode" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="destinationPincode" class="form-label">Destination Pincode</label>
                                        <input type="text" class="form-control" id="destinationPincode" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Payment Details -->
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">Payment Details</div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="paymentType" class="form-label">Payment Type</label>
                                        <select class="form-select" id="paymentType">
                                            <option value="Prepaid" selected>Prepaid</option>
                                            <option value="COD">COD</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="chequePayment">
                                        <label class="form-check-label" for="chequePayment">Cheque Payment</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="invoiceAmount" class="form-label">Invoice Amount</label>
                                        <input type="number" class="form-control" id="invoiceAmount" required>
                                    </div>
                                    <div class="mb-3" id="codAmountContainer" style="display: none;">
                                        <label for="codAmount" class="form-label">COD Amount</label>
                                        <input type="number" class="form-control" id="codAmount">
                                        <div class="form-text text-muted">COD Amount must be less than or equal to Invoice Amount.</div>
                                        <div id="codAmountError" class="invalid-feedback">COD Amount cannot exceed Invoice Amount.</div>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="rov" checked>
                                        <label class="form-check-label" for="rov">Risk of Value (ROV)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Shipment Dimensions -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">Shipment Dimensions</div>
                        <div class="card-body">
                            <div id="boxesContainer">
                                <!-- Individual box entries will be added here -->
                                <div class="row mb-3 box-row">
                                    <div class="col-md-2">
                                        <label class="form-label">Length (cm)</label>
                                        <input type="number" class="form-control box-length" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Width (cm)</label>
                                        <input type="number" class="form-control box-width" step="0.1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Height (cm)</label>
                                        <input type="number" class="form-control box-height" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Weight (Each Box in kg)</label>
                                        <input type="number" class="form-control box-weight" step="0.1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Box Count</label>
                                        <input type="number" class="form-control box-count" value="1" min="1" required oninput="calculateTotals()">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end mb-2">
                                        <button type="button" class="btn btn-danger remove-box" style="display: none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <button type="button" id="addBoxBtn" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Another Box</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="totalBoxes" class="form-label">Total Boxes</label>
                                        <input type="number" class="form-control" id="totalBoxes" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="totalWeight" class="form-label">Total Weight (kg)</label>
                                        <input type="number" class="form-control" id="totalWeight" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="freightMode" class="form-label">Freight Mode</label>
                                        <select class="form-select" id="freightMode" required>
                                            <option value="fod" >Freight On Delhivery</option>
                                            <option value="fop" selected>Freight On Picking</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">Get Freight Estimates</button>
                        <button type="button" id="reloginBtn" class="btn btn-warning btn-lg ms-2">Relogin</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Results Section -->
        <div id="resultsSection" style="display: none;">
            <h2 class="mb-3">Freight Estimates</h2>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Showing estimates from available carriers. Click on a carrier to view detailed breakdown.
            </div>
            <div id="loader" class="loader"></div>
            <div id="resultsContainer" class="row"></div>
        </div>
    </div>
    <input id="user-name" type="hidden" ></input>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="freight-real.js"></script>
    <script src="freight-to-order.js"></script>
    <!-- Data transfer script for dimensions and weight -->
    <script src="data-transfer.js"></script>
    <!-- Script to calculate total weight -->
    <script>
        // Global variable to store weight per box ratios for each row
        const weightPerBoxMap = new Map();
        // Main function to calculate totals
        function calculateTotals() {
            let totalBoxes = 0;
            let totalWeight = 0;
            // Get all box rows
            const boxRows = document.querySelectorAll('.box-row');
            // Calculate for each row
            boxRows.forEach((row, index) => {
                const weightInput = row.querySelector('.box-weight');
                const countInput = row.querySelector('.box-count');
                if (weightInput && countInput) {
                    const rowId = `row-${index}`;
                    const weight = parseFloat(weightInput.value) || 0;
                    const count = parseInt(countInput.value) || 0;
                    // Store weight per box ratio if not already stored or if weight was manually changed
                    if (!weightPerBoxMap.has(rowId) || weightInput.dataset.userModified === 'true') {
                        if (count > 0) {
                            weightPerBoxMap.set(rowId, weight);
                        }
                        // Reset the user modified flag
                        weightInput.dataset.userModified = 'false';
                    }
                    // If box count changed but we have a saved weight per box, use that
                    if (weightPerBoxMap.has(rowId) && count > 0) {
                        const savedWeightPerBox = weightPerBoxMap.get(rowId);
                        // Only update if user isn't actively modifying the weight field
                        if (document.activeElement !== weightInput) {
                            weightInput.value = savedWeightPerBox;
                        }
                    }
                    totalBoxes += count;
                    totalWeight += weight * count;
                }
            });
            // Update total fields
            document.getElementById('totalBoxes').value = totalBoxes;
            document.getElementById('totalWeight').value = totalWeight.toFixed(2);
            // Save the current values to ensure they persist
            saveCurrentValues();
        }
        // Store current values to session storage
        function saveCurrentValues() {
            const boxRows = document.querySelectorAll('.box-row');
            const savedData = [];
            boxRows.forEach((row, index) => {
                const weightInput = row.querySelector('.box-weight');
                const countInput = row.querySelector('.box-count');
                if (weightInput && countInput) {
                    savedData.push({
                        rowIndex: index,
                        weight: weightInput.value,
                        count: countInput.value
                    });
                }
            });
            // Save to session storage
            sessionStorage.setItem('freightBoxData', JSON.stringify(savedData));
        }
        document.addEventListener('DOMContentLoaded', function() {
            // Initial calculation of total boxes and weight
            calculateTotals();
            // Add global input event listeners for real-time updates
            document.querySelectorAll('.box-weight').forEach(input => {
                input.addEventListener('input', function() {
                    this.dataset.userModified = 'true';
                    calculateTotals();
                });
            });
            document.querySelectorAll('.box-count').forEach(input => {
                input.addEventListener('input', calculateTotals);
                input.addEventListener('change', calculateTotals);
                input.addEventListener('blur', calculateTotals);
            });
            // Add event listener to container to handle all box inputs (event delegation)
            document.getElementById('boxesContainer').addEventListener('input', function(event) {
                // Check if the input is from a box weight or box count field
                if (event.target.classList.contains('box-weight')) {
                    event.target.dataset.userModified = 'true';
                    calculateTotals();
                } else if (event.target.classList.contains('box-count')) {
                    calculateTotals();
                }
            });
            // Add event listener for the add box button
            document.getElementById('addBoxBtn').addEventListener('click', function() {
                // Wait for the DOM to update with the new box
                setTimeout(function() {
                    // Add event listeners to the newly added box
                    const newRow = document.querySelector('.box-row:last-child');
                    if (newRow) {
                        const weightInput = newRow.querySelector('.box-weight');
                        const countInput = newRow.querySelector('.box-count');
                        if (weightInput) {
                            weightInput.addEventListener('input', function() {
                                this.dataset.userModified = 'true';
                                calculateTotals();
                            });
                        }
                        if (countInput) {
                            countInput.addEventListener('input', calculateTotals);
                            countInput.addEventListener('change', calculateTotals);
                            countInput.addEventListener('blur', calculateTotals);
                        }
                    }
                    calculateTotals();
                }, 100);
            });
            // Helper to get POSTed data from hidden inputs
            function getPostedData() {
                const data = {};
                document.querySelectorAll('input[type="hidden"]').forEach(input => {
                    data[input.name] = input.value;
                });
                return data;
            }
            const postData = getPostedData();
            // Fill in the first box row if data is present
            if (postData.length) document.querySelector('.box-length').value = postData.length;
            if (postData.width) document.querySelector('.box-width').value = postData.width;
            if (postData.height) document.querySelector('.box-height').value = postData.height;
            if (postData.deadWeight) document.querySelector('.box-weight').value = postData.deadWeight;
            if (postData.boxCount) document.querySelector('.box-count').value = postData.boxCount;
            // Fill in total boxes and total weight
            if (postData.boxCount) document.getElementById('totalBoxes').value = postData.boxCount;
            if (postData.totalWeight) document.getElementById('totalWeight').value = (parseFloat(postData.totalWeight) / 1000).toFixed(2);
            // Optionally, if you want to handle multiple boxes, parse postData.boxData (JSON) and dynamically add rows
            if (postData.boxData) {
                try {
                    const boxes = JSON.parse(postData.boxData);
                    // Clear existing box rows except the first one
                    const boxesContainer = document.getElementById('boxesContainer');
                    while (boxesContainer.children.length > 1) {
                        boxesContainer.removeChild(boxesContainer.lastChild);
                    }
                    // If more than one box, add rows and fill them
                    for (let i = 1; i < boxes.length; i++) {
                        document.getElementById('addBoxBtn').click();
                    }
                    // Fill all box rows
                    document.querySelectorAll('.box-row').forEach((row, idx) => {
                        if (boxes[idx]) {
                            const dims = boxes[idx].dimension
                                ? boxes[idx].dimension.split('x').map(d => d.trim())
                                : [15, 15, 15];
                            row.querySelector('.box-length').value = dims[0] || '';
                            row.querySelector('.box-width').value = dims[1] || '';
                            row.querySelector('.box-height').value = dims[2] || '';
                            row.querySelector('.box-weight').value = boxes[idx].weight || '';
                            row.querySelector('.box-count').value = 1;
                        }
                    });
                } catch (e) {
                    // Ignore if boxData is not valid
                }
            }
        });
        
        // Relogin functionality
        document.getElementById('reloginBtn').addEventListener('click', function() {
            // Show loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Relogging in...';
            this.disabled = true;
            
            // Make API call to relogin endpoint
            fetch('http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/delhivery-relogin', {
                method: 'POST',
                mode: 'cors'
                // No headers or body data to avoid CORS preflight
            })
            .then(response => {
                // Log status code and response body like in the Python example
                console.log('Status Code:', response.status);
                return response.text();
            })
            .then(responseText => {
                console.log('Response Body:', responseText);
            })
            .catch(error => {
                console.error('Error during relogin:', error);
            })
            .finally(() => {
                // Reset button state
                this.innerHTML = originalText;
                this.disabled = false;
            });
        });
    </script>
</body>
</html> 