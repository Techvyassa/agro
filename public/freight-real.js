document.addEventListener('DOMContentLoaded', function() {
    const freightForm = document.getElementById('freightForm');
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');
    const loader = document.getElementById('loader');
    const boxesContainer = document.getElementById('boxesContainer');
    const addBoxBtn = document.getElementById('addBoxBtn');
    const totalBoxesInput = document.getElementById('totalBoxes');
    const totalWeightInput = document.getElementById('totalWeight');

    // Parse URL parameters
    const parseUrlParams = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const params = {};
        
        // Get all parameters
        for (const [key, value] of urlParams.entries()) {
            params[key] = value;
        }
        
        return params;
    };
    
    // Fill form with URL parameters
    const fillFormFromUrlParams = () => {
        const params = parseUrlParams();
        
        // Fill source and destination if provided
        if (params.source) document.getElementById('sourcePincode').value = params.source;
        if (params.sourcePincode) document.getElementById('sourcePincode').value = params.sourcePincode;
        if (params.destination) document.getElementById('destinationPincode').value = params.destination;
        if (params.destinationPincode) document.getElementById('destinationPincode').value = params.destinationPincode;
        
        // Handle invoice amount if provided
        if (params.invoiceAmount) document.getElementById('invoiceAmount').value = params.invoiceAmount;
        
        // First try to use dimensions and boxWeights parameters (from freight-calculator)
        if (params.dimensions && params.boxCount) {
            try {
                console.log('Processing data from freight-calculator');
                
                // Split dimensions and box weights
                const dimensionSets = params.dimensions.split(',');
                const boxWeights = params.boxWeights ? params.boxWeights.split(',') : [];
                const boxCount = parseInt(params.boxCount) || dimensionSets.length;
                
                console.log('Dimensions:', dimensionSets);
                console.log('Box Weights:', boxWeights);
                console.log('Box Count:', boxCount);
                
                // Clear existing boxes first (leave one)
                while (boxesContainer.children.length > 1) {
                    boxesContainer.removeChild(boxesContainer.lastChild);
                }
                
                // Set values for the first box
                if (dimensionSets.length > 0) {
                    const firstDimSet = dimensionSets[0];
                    let length, width, height;
                    
                    if (firstDimSet.includes('x')) {
                        [length, width, height] = firstDimSet.split('x').map(val => parseFloat(val.trim()));
                    } else {
                        // Fallback if dimensions aren't in expected format
                        length = parseFloat(params.length) || 15;
                        width = parseFloat(params.width) || 15;
                        height = parseFloat(params.height) || 15;
                    }
                    
                    const weight = boxWeights[0] ? parseFloat(boxWeights[0]) : 
                                  parseFloat(params.deadWeight) || 5;
                    
                    const firstBox = document.querySelector('.box-row');
                    firstBox.querySelector('.box-length').value = length || 15;
                    firstBox.querySelector('.box-width').value = width || 15;
                    firstBox.querySelector('.box-height').value = height || 15;
                    firstBox.querySelector('.box-weight').value = weight || 5;
                    
                    // Add additional boxes with their specific dimensions and weights
                    for (let i = 1; i < Math.min(boxCount, dimensionSets.length); i++) {
                        addNewBox();
                        
                        let boxLength, boxWidth, boxHeight;
                        if (dimensionSets[i] && dimensionSets[i].includes('x')) {
                            [boxLength, boxWidth, boxHeight] = dimensionSets[i].split('x')
                                .map(val => parseFloat(val.trim()));
                        } else {
                            // Use first box dimensions as fallback
                            boxLength = length;
                            boxWidth = width;
                            boxHeight = height;
                        }
                        
                        const boxWeight = boxWeights[i] ? parseFloat(boxWeights[i]) : 
                                        parseFloat(params.deadWeight) || 5;
                        
                        const newBox = boxesContainer.lastChild;
                        newBox.querySelector('.box-length').value = boxLength || 15;
                        newBox.querySelector('.box-width').value = boxWidth || 15;
                        newBox.querySelector('.box-height').value = boxHeight || 15;
                        newBox.querySelector('.box-weight').value = boxWeight || 5;
                    }
                    
                    // Add any remaining boxes using the first box dimensions
                    for (let i = dimensionSets.length; i < boxCount; i++) {
                        addNewBox();
                        const newBox = boxesContainer.lastChild;
                        newBox.querySelector('.box-length').value = length || 15;
                        newBox.querySelector('.box-width').value = width || 15;
                        newBox.querySelector('.box-height').value = height || 15;
                        newBox.querySelector('.box-weight').value = parseFloat(params.deadWeight) || 5;
                    }
                }
                
                // Update totals
                updateTotals();
                
            } catch (error) {
                console.error('Error processing dimensions from freight-calculator:', error);
            }
        }
        // Fall back to legacy format if dimensions parameter is not available
        else if (params.length && params.width && params.height) {
            console.log('Processing data in legacy format');
            
            // Clear existing boxes first (leave one)
            while (boxesContainer.children.length > 1) {
                boxesContainer.removeChild(boxesContainer.lastChild);
            }
            
            // Get the box count
            const boxCount = parseInt(params.boxCount) || 1;
            const length = parseFloat(params.length);
            const width = parseFloat(params.width);
            const height = parseFloat(params.height);
            const weight = parseFloat(params.deadWeight) || 5; // Default to 5kg if not specified
            
            console.log('Legacy dimensions:', length, width, height);
            console.log('Legacy box count:', boxCount);
            
            // Set values for the first box
            const firstBox = document.querySelector('.box-row');
            firstBox.querySelector('.box-length').value = length;
            firstBox.querySelector('.box-width').value = width;
            firstBox.querySelector('.box-height').value = height;
            firstBox.querySelector('.box-weight').value = weight;
            
            // Add additional boxes if needed
            for (let i = 1; i < boxCount; i++) {
                addNewBox();
                const newBox = boxesContainer.lastChild;
                newBox.querySelector('.box-length').value = length;
                newBox.querySelector('.box-width').value = width;
                newBox.querySelector('.box-height').value = height;
                newBox.querySelector('.box-weight').value = weight;
            }
            
            // Update totals
            updateTotals();
        }
        // Support for the dimensions format (backwards compatibility)
        else if (params.dimensions) {
            try {
                // Split the dimensions into individual boxes
                const dimensionSets = params.dimensions.split(',');
                const boxWeights = params.boxWeights ? params.boxWeights.split(',') : [];
                
                // Clear existing boxes first (leave one)
                while (boxesContainer.children.length > 1) {
                    boxesContainer.removeChild(boxesContainer.lastChild);
                }
                
                // Set values for the first box
                if (dimensionSets.length > 0) {
                    const [length, width, height] = dimensionSets[0].split('x').map(val => parseFloat(val.trim()));
                    const weight = boxWeights[0] ? parseFloat(boxWeights[0]) : parseFloat(params.deadWeight || 5);
                    
                    const firstBox = document.querySelector('.box-row');
                    firstBox.querySelector('.box-length').value = length || '';
                    firstBox.querySelector('.box-width').value = width || '';
                    firstBox.querySelector('.box-height').value = height || '';
                    firstBox.querySelector('.box-weight').value = weight || '';
                }
                
                // Add additional boxes
                for (let i = 1; i < dimensionSets.length; i++) {
                    addNewBox();
                    const [length, width, height] = dimensionSets[i].split('x').map(val => parseFloat(val.trim()));
                    const weight = boxWeights[i] ? parseFloat(boxWeights[i]) : parseFloat(params.deadWeight || 5);
                    
                    const newBox = boxesContainer.lastChild;
                    newBox.querySelector('.box-length').value = length || '';
                    newBox.querySelector('.box-width').value = width || '';
                    newBox.querySelector('.box-height').value = height || '';
                    newBox.querySelector('.box-weight').value = weight || '';
                }
                
                // Update totals
                updateTotals();
            } catch (error) {
                console.error('Error parsing dimensions:', error);
            }
        }
        
        // Handle total weight if specified (convert from grams to kg if needed)
        if (params.totalWeight) {
            let weightInKg;
            // If value is large, assume it's in grams and convert to kg
            if (parseFloat(params.totalWeight) > 1000) {
                weightInKg = parseFloat(params.totalWeight) / 1000;
            } else {
                weightInKg = parseFloat(params.totalWeight);
            }
            totalWeightInput.value = weightInKg.toFixed(2);
        }
        
        // Handle freight mode
        if (params.freightMode) document.getElementById('freightMode').value = params.freightMode;
        
        // Auto-submit the form if all required parameters are present
        const sourcePincode = document.getElementById('sourcePincode').value;
        const destinationPincode = document.getElementById('destinationPincode').value;

        const hasRequiredParams = params.length && params.width && params.height && 
            (params.boxCount || params.boxCount === '1') && 
            (sourcePincode && destinationPincode);
        
        // Add invoice amount if needed
        if (params.invoiceAmount && !document.getElementById('invoiceAmount').value) {
            document.getElementById('invoiceAmount').value = params.invoiceAmount;
        }

        console.log('URL parameters detected:', params);
        console.log('Form is ready for submission:', hasRequiredParams);
            
        if (hasRequiredParams || params.autoSubmit === 'true') {
            console.log('Auto-submitting form with URL parameters');
            setTimeout(() => {
                document.querySelector('#freightForm button[type="submit"]').click();
            }, 800); // Increased timeout to ensure all values are loaded
        }
    };

    // Add a status message area
    const statusContainer = document.createElement('div');
    statusContainer.className = 'alert alert-info mb-4';
    statusContainer.innerHTML = '<p class="mb-0">This tool connects to the real freight API to provide actual shipping rates.</p>';
    document.querySelector('#freightForm button[type="submit"]').parentNode.prepend(statusContainer);

    // Add event listener for the Add Box button
    addBoxBtn.addEventListener('click', function() {
        addNewBox();
        updateTotals();
    });

    // Initial setup for remove box buttons
    setupRemoveBoxButtons();
    
    // Function to add a new box row
    function addNewBox() {
        const boxRow = document.querySelector('.box-row').cloneNode(true);
        boxRow.querySelectorAll('input').forEach(input => {
            input.value = '';
        });
        
        // Show the remove button for all boxes
        const removeButtons = document.querySelectorAll('.remove-box');
        removeButtons.forEach(btn => {
            btn.style.display = 'block';
        });
        
        // Show the remove button for the new box
        boxRow.querySelector('.remove-box').style.display = 'block';
        
        // Add the new row to the container
        boxesContainer.appendChild(boxRow);
        
        // Re-setup remove box buttons
        setupRemoveBoxButtons();
    }
    
    // Function to set up remove box buttons
    function setupRemoveBoxButtons() {
        const removeButtons = document.querySelectorAll('.remove-box');
        removeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                if (document.querySelectorAll('.box-row').length > 1) {
                    this.closest('.box-row').remove();
                    updateTotals();
                    
                    // If only one box remains, hide its remove button
                    if (document.querySelectorAll('.box-row').length === 1) {
                        document.querySelector('.remove-box').style.display = 'none';
                    }
                }
            });
        });
        
        // Add input change listeners to update totals
        document.querySelectorAll('.box-weight, .box-length, .box-width, .box-height').forEach(input => {
            input.addEventListener('input', updateTotals);
        });
    }
    
    // Function to update total boxes and total weight
    function updateTotals() {
        const boxRows = document.querySelectorAll('.box-row');
        let totalWeight = 0;
        
        boxRows.forEach(row => {
            const weightInput = row.querySelector('.box-weight');
            if (weightInput && weightInput.value) {
                totalWeight += parseFloat(weightInput.value);
            }
        });
        
        totalBoxesInput.value = boxRows.length;
        totalWeightInput.value = totalWeight.toFixed(2);
    }

    // Initialize totals
    updateTotals();

    // Fill form with URL parameters
    fillFormFromUrlParams();

    freightForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loader and results section
        resultsSection.style.display = 'block';
        loader.style.display = 'block';
        resultsContainer.innerHTML = '';
        
        // Show loading message
        statusContainer.className = 'alert alert-warning mb-4';
        statusContainer.innerHTML = '<p class="mb-0"><i class="fas fa-sync fa-spin"></i> Connecting to freight API...</p>';
        
        // Collect all box dimensions
        const boxRows = document.querySelectorAll('.box-row');
        const dimensions = [];
        let totalValidBoxes = 0;
        
        boxRows.forEach(row => {
            // Get the values from the form fields
            const lengthInput = row.querySelector('.box-length');
            const widthInput = row.querySelector('.box-width');
            const heightInput = row.querySelector('.box-height');
            const weightInput = row.querySelector('.box-weight');
            
            // Only include boxes that have all dimensions and weight
            if (lengthInput.value && widthInput.value && heightInput.value && weightInput.value) {
                const length = parseFloat(lengthInput.value) || 0;
                const width = parseFloat(widthInput.value) || 0;
                const height = parseFloat(heightInput.value) || 0;
                const weight = parseFloat(weightInput.value) || 0;
                
                if (length > 0 && width > 0 && height > 0) {
                    dimensions.push({
                        length_cm: length,
                        width_cm: width,
                        height_cm: height,
                        box_count: 1,
                        each_box_dead_weight: weight
                    });
                    totalValidBoxes++;
                }
            }
        });
        
        // Get source and destination pincodes
        const sourcePincodeValue = document.getElementById('sourcePincode').value.trim();
        const destinationPincodeValue = document.getElementById('destinationPincode').value.trim();
        
        // Get invoice amount
        const invoiceAmountInput = document.getElementById('invoiceAmount');
        const invoiceAmountValue = invoiceAmountInput.value.trim() ? 
            parseFloat(invoiceAmountInput.value) : 1000; // Default to 1000 if empty
        
        // Build request payload
        const payload = {
            common: {
                pincode: {
                    source: sourcePincodeValue,
                    destination: destinationPincodeValue
                },
                payment: {
                    type: document.getElementById('paymentType').value,
                    cheque_payment: document.getElementById('chequePayment').checked
                },
                invoice_amount: invoiceAmountValue,
                insurance: {
                    rov: document.getElementById('rov').checked
                }
            },
            shipment_details: {
                dimensions: dimensions,
                weight_g: parseFloat(totalWeightInput.value) * 1000, // Convert kg to g
                freight_mode: document.getElementById('freightMode').value
            }
        };
        
        // Debugging information
        console.log('Shipment details:', {
            boxes: totalValidBoxes,
            source: sourcePincodeValue,
            destination: destinationPincodeValue,
            dimensions: dimensions.map(d => `${d.length_cm}x${d.width_cm}x${d.height_cm}`),
            invoice: invoiceAmountValue,
            totalWeight: `${totalWeightInput.value}kg (${parseFloat(totalWeightInput.value) * 1000}g)`
        });
        
        console.log('Sending to API:', payload);

        // Check if we have all required data before making API call
        if (!sourcePincodeValue || !destinationPincodeValue || dimensions.length === 0) {
            // Hide loader
            loader.style.display = 'none';
            
            // Show error message
            statusContainer.className = 'alert alert-danger mb-4';
            statusContainer.innerHTML = `
                <p class="mb-0"><i class="fas fa-exclamation-triangle"></i> Missing required information:</p>
                <ul class="mb-0 small">
                    ${!sourcePincodeValue ? '<li>Source pincode is required</li>' : ''}
                    ${!destinationPincodeValue ? '<li>Destination pincode is required</li>' : ''}
                    ${dimensions.length === 0 ? '<li>At least one box with valid dimensions is required</li>' : ''}
                </ul>
            `;
            
            // Show error in the results area
            resultsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-circle"></i> Missing Information</h5>
                        <p>Please fill in all required fields before submitting.</p>
                    </div>
                </div>
            `;
            return;
        }

        // Use our PHP proxy to call the real API
        fetch('freight-proxy.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.error || `API returned status ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            // Hide loader
            loader.style.display = 'none';
            
            // Verify we received actual API data and not a hardcoded response
            console.log('API Response received:', data);
            
            // Check for _request_context which contains our original request parameters
            // This was added by our enhanced proxy to verify real-time responses
            const requestContext = data._request_context || {};
            const isRealTimeResponse = !!requestContext.request_id;
            
            // Remove the request context before displaying results
            if (data._request_context) {
                delete data._request_context;
            }
            
            // Calculate response metrics
            let responseFingerprint = '';
            let carrierCount = 0;
            let estimateCount = 0;
            let hasData = false;
            let totalCharges = 0;
            
            try {
                // Check if we have actual data
                if (data && typeof data === 'object') {
                    // Only count real carrier keys (exclude our added metadata)
                    carrierCount = Object.keys(data).filter(key => !key.startsWith('_')).length;
                    
                    for (const carrier in data) {
                        if (carrier.startsWith('_')) continue; // Skip metadata fields
                        
                        if (Array.isArray(data[carrier])) {
                            estimateCount += data[carrier].length;
                            
                            // Sample values to verify real response
                            if (data[carrier].length > 0) {
                                hasData = true;
                                // Check first estimate charges and build fingerprint
                                data[carrier].forEach(estimate => {
                                    if (estimate.total_charges) {
                                        totalCharges += estimate.total_charges;
                                        responseFingerprint += `${carrier}:${estimate.total_charges},`;
                                    }
                                });
                            }
                        }
                    }
                }
                
                // Log detailed response validation
                console.log('Response validation:', {
                    isRealTimeResponse,
                    requestSource: requestContext.source,
                    requestDest: requestContext.destination,
                    carriers: carrierCount,
                    estimates: estimateCount,
                    totalCharges: totalCharges,
                    hasData: hasData,
                    fingerprint: responseFingerprint,
                    timestamp: requestContext.timestamp
                });
                
                // Verify that response matches our request parameters
                const sourceMatches = !requestContext.source || requestContext.source === sourcePincodeValue;
                const destMatches = !requestContext.destination || requestContext.destination === destinationPincodeValue;
                
                // Alert if there might be a mismatch (response doesn't match request)
                if (!sourceMatches || !destMatches) {
                    console.warn('Response parameters don\'t match request:', {
                        requestSource: sourcePincodeValue,
                        responseSource: requestContext.source,
                        requestDest: destinationPincodeValue, 
                        responseDest: requestContext.destination
                    });
                }
                
                // Check if we have a valid response with appropriate data
                if (!hasData || carrierCount === 0) {
                    throw new Error('API returned an empty response');
                }
            } catch (e) {
                console.error('Error validating API response:', e);
                statusContainer.className = 'alert alert-warning mb-4';
                statusContainer.innerHTML = `
                    <p class="mb-0"><i class="fas fa-exclamation-triangle"></i> Warning: The API response may not be accurate.</p>
                    <p class="small mb-0">Please verify the rates before proceeding.</p>
                `;
            }
            
            // Update status based on data validation
            const freshResponse = isRealTimeResponse ? 'real-time' : 'cached';
            statusContainer.className = isRealTimeResponse ? 'alert alert-success mb-4' : 'alert alert-info mb-4';
            statusContainer.innerHTML = `
                <p class="mb-0"><i class="fas fa-${isRealTimeResponse ? 'check-circle' : 'info-circle'}"></i> 
                ${isRealTimeResponse ? 'Connected successfully' : 'Retrieved data'} from the freight API.</p>
                <p class="small mb-0">Showing ${freshResponse} shipping rates for <strong>${sourcePincodeValue}</strong> to <strong>${destinationPincodeValue}</strong></p>
                <p class="small mb-0">Found ${carrierCount} carriers with ${estimateCount} shipping options</p>
            `;
            
            // Display results
            displayResults(data);
        })
        .catch(error => {
            // Hide loader
            loader.style.display = 'none';
            
            // Update status to show error
            statusContainer.className = 'alert alert-danger mb-4';
            statusContainer.innerHTML = `
                <p class="mb-0"><i class="fas fa-exclamation-triangle"></i> Error connecting to the freight API:</p>
                <p class="mb-0 small">${error.message}</p>
                <p class="mb-0 small">Please check your network connection and try again.</p>
            `;
            
            // Show error in the results area
            resultsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-circle"></i> Connection Error</h5>
                        <p>Could not retrieve freight estimates from the API. Technical details:</p>
                        <pre class="bg-light p-2 rounded small">${error.toString()}</pre>
                    </div>
                </div>
            `;
        });
    });

    function displayResults(data) {
        resultsContainer.innerHTML = '';
        
        // Check if data is empty
        if (Object.keys(data).length === 0) {
            resultsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> No freight estimates available for this route.
                    </div>
                </div>
            `;
            return;
        }

        // Process each carrier's results
        for (const [carrier, estimates] of Object.entries(data)) {
            if (!Array.isArray(estimates) || estimates.length === 0) continue;
            
            // Create a card for each carrier
            const carrierCard = document.createElement('div');
            carrierCard.className = 'col-12 mb-4';
            carrierCard.innerHTML = `
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">${formatCarrierName(carrier)}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="row m-0">
                            ${createEstimateCards(estimates, carrier)}
                        </div>
                    </div>
                </div>
            `;
            
            resultsContainer.appendChild(carrierCard);
        }

        // Add event listeners to toggle buttons
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                const extraDetails = this.closest('.card-body').querySelector('.extra-details');
                
                // Toggle expanded class
                if (extraDetails.style.maxHeight === '1000px') {
                    extraDetails.style.maxHeight = '0';
                    this.innerHTML = 'Show details <i class="fas fa-chevron-down"></i>';
                } else {
                    extraDetails.style.maxHeight = '1000px';
                    this.innerHTML = 'Hide details <i class="fas fa-chevron-up"></i>';
                }
            });
        });
    }

    function createEstimateCards(estimates, carrier) {
        return estimates.map((estimate, index) => {
            const {
                service_name,
                total_charges,
                tat,
                charged_wt,
                risk_type,
                risk_type_charge,
                extra
            } = estimate;

            // Format extra details as a readable JSON
            const extraDetails = extra ? formatExtraDetails(extra) : 'No additional details available';
            
            return `
                <div class="col-md-6 col-lg-4 p-2">
                    <div class="card result-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">${service_name || 'Service'}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h3 mb-0">₹${total_charges.toFixed(2)}</span>
                                ${tat ? `<span class="badge bg-info">TAT: ${tat} days</span>` : ''}
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><strong>Charged Weight:</strong></span>
                                    <span>${charged_wt} kg</span>
                                </div>
                                ${risk_type !== null ? `
                                <div class="d-flex justify-content-between">
                                    <span><strong>Risk Type:</strong></span>
                                    <span>${risk_type}</span>
                                </div>` : ''}
                                ${risk_type_charge ? `
                                <div class="d-flex justify-content-between">
                                    <span><strong>Risk Charge:</strong></span>
                                    <span>₹${risk_type_charge.toFixed(2)}</span>
                                </div>` : ''}
                            </div>
                            
                            <button class="btn btn-sm btn-outline-secondary w-100 toggle-details mt-2">
                                Show details <i class="fas fa-chevron-down"></i>
                            </button>
                            
                            <div class="extra-details mt-3">
                                <h6 class="border-bottom pb-2">Price Breakdown</h6>
                                <pre class="bg-light p-3 rounded" style="font-size: 0.8rem; overflow-x: auto;">${extraDetails}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function formatExtraDetails(extra) {
        // Create a formatted version of the extra details
        try {
            // For price breakup, create a more readable format
            if (extra.price_breakup) {
                return formatPriceBreakup(extra);
            }
            
            // For other formats (like bigship)
            return formatGenericExtra(extra);
        } catch (error) {
            return JSON.stringify(extra, null, 2);
        }
    }

    function formatPriceBreakup(extra) {
        // Format for Delhivery-style response
        let result = '';
        
        if (extra.min_charged_wt) {
            result += `Minimum Charged Weight: ${extra.min_charged_wt} kg\n`;
        }
        
        if (extra.price_breakup) {
            result += 'Price Breakup:\n';
            const pb = extra.price_breakup;
            
            // Base charges
            if (pb.base_freight_charge) result += `  Base Freight: ₹${pb.base_freight_charge}\n`;
            if (pb.fuel_surcharge) result += `  Fuel Surcharge: ₹${pb.fuel_surcharge}\n`;
            if (pb.fuel_hike) result += `  Fuel Hike: ₹${pb.fuel_hike}\n`;
            if (pb.insurance_rov) result += `  Insurance (ROV): ₹${pb.insurance_rov}\n`;
            
            // Additional charges
            if (pb.fm) result += `  FM: ₹${pb.fm}\n`;
            if (pb.lm) result += `  LM: ₹${pb.lm}\n`;
            if (pb.green) result += `  Green Tax: ₹${pb.green}\n`;
            
            // ODA charges
            if (pb.oda && (pb.oda.fm || pb.oda.lm)) {
                result += `  ODA: FM ₹${pb.oda.fm || 0}, LM ₹${pb.oda.lm || 0}\n`;
            }
            
            // Total pre-tax
            if (pb.pre_tax_freight_charges) result += `  Pre-tax Charges: ₹${pb.pre_tax_freight_charges}\n`;
            
            // GST
            if (pb.gst) result += `  GST (${pb.gst_percent}%): ₹${pb.gst}\n`;
            
            // Markup
            if (pb.markup) result += `  Markup: ₹${pb.markup}\n`;
            
            // Handling charges
            if (pb.other_handling_charges) result += `  Handling Charges: ₹${pb.other_handling_charges}\n`;
            
            // Meta charges
            if (pb.meta_charges && Object.keys(pb.meta_charges).length > 0) {
                result += '  Meta Charges:\n';
                for (const [key, value] of Object.entries(pb.meta_charges)) {
                    if (value > 0) {
                        result += `    ${formatMetaChargeKey(key)}: ₹${value}\n`;
                    }
                }
            }
        }
        
        return result;
    }

    function formatGenericExtra(extra) {
        // Format for Bigship-style response
        let result = '';
        
        if (extra.courier_partner_id) {
            result += `Courier Partner ID: ${extra.courier_partner_id}\n`;
        }
        
        if (extra.courier_type) {
            result += `Courier Type: ${extra.courier_type}\n`;
        }
        
        if (extra.plan_name) {
            result += `Plan: ${extra.plan_name}\n`;
        }
        
        if (extra.risk_type_name) {
            result += `Risk Type: ${extra.risk_type_name}\n`;
        }
        
        if (extra.zone) {
            result += `Zone: ${extra.zone}\n`;
        }
        
        if (extra.freight_charge) {
            result += `Freight Charge: ₹${extra.freight_charge}\n`;
        }
        
        if (extra.total_freight_charge) {
            result += `Total Freight: ₹${extra.total_freight_charge}\n`;
        }
        
        // Format additional charges
        if (extra.additional_charges && Object.keys(extra.additional_charges).length > 0) {
            result += 'Additional Charges:\n';
            for (const [key, value] of Object.entries(extra.additional_charges)) {
                if (value > 0) {
                    result += `  ${formatChargeKey(key)}: ₹${value}\n`;
                }
            }
        }
        
        // Format other additional charges
        if (extra.other_additional_charges && extra.other_additional_charges.length > 0) {
            result += 'Other Charges:\n';
            for (const charge of extra.other_additional_charges) {
                if (charge.key_value > 0) {
                    result += `  ${formatChargeKey(charge.key_name)}: ₹${charge.key_value}\n`;
                }
            }
        }
        
        return result;
    }

    function formatCarrierName(carrier) {
        // Format carrier name for display
        if (carrier === 'delhivery') {
            return 'Delhivery';
        } else if (carrier.includes('@')) {
            // For email-based carriers, show the full identifier
            return 'Bigship (' + carrier + ')';
        }
        return carrier.charAt(0).toUpperCase() + carrier.slice(1);
    }

    function formatChargeKey(key) {
        // Format charge key for better display
        return key
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    function formatMetaChargeKey(key) {
        // Special format for meta charge keys
        const keyMap = {
            'cod': 'COD',
            'demurrage': 'Demurrage',
            'reattempt': 'Reattempt',
            'handling': 'Handling',
            'pod': 'POD',
            'sunday': 'Sunday Delivery',
            'to_pay': 'To Pay',
            'cheque': 'Cheque',
            'csd': 'CSD',
            'add_cost': 'Additional Cost',
            'adh_vhl': 'Adhoc Vehicle',
            'sp_dlv_area': 'Special Delivery Area',
            'add_machine': 'Additional Machine',
            'add_man_pwr': 'Additional Manpower',
            'mathadi_un': 'Mathadi Union',
            'appt_chg': 'Appointment Charge'
        };
        
        return keyMap[key] || formatChargeKey(key);
    }
});
