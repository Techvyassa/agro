// This script integrates warehouse selection with the order form
document.addEventListener('DOMContentLoaded', function() {
    // Override existing book freight buttons to use our standalone approach
    document.addEventListener('click', function(e) {
        // Check if the clicked element is a book freight button
        if (e.target && (e.target.classList.contains('book-freight') || e.target.closest('.book-freight'))) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get the button (might be the icon inside)
            const button = e.target.classList.contains('book-freight') ? e.target : e.target.closest('.book-freight');
            
            // Get carrier info
            let userEmail = document.getElementById('user-name').value;
            // Only log email once
            let carrierName = '';
            let rate = '';
            
            // Try to get data from the card
            const card = button.closest('.card-body');
            if (card) {
                // Get rate information
                const rateElement = card.querySelector('.h3');
                if (rateElement) {
                    rate = rateElement.textContent.trim();
                }
                
                // Get carrier name from card header
                const cardHeader = button.closest('.card').querySelector('.card-header h5');
                if (cardHeader) {
                    carrierName = cardHeader.textContent.trim();
                }
                
                // If no email found, try to extract from carrier name
                if (!userEmail && carrierName.includes('(')) {
                    const emailMatch = carrierName.match(/\(([^)]+)\)/);
                    if (emailMatch && emailMatch[1]) {
                        userEmail = emailMatch[1];
                        // If it has a prefix like 'carrier-', remove it
                        if (userEmail.includes('-')) {
                            userEmail = userEmail.split('-')[1];
                        }
                    }
                }
            }
            
            // Fallback to default email if none found
            userEmail = userEmail || 'user@example.com';
            console.log('Using email for warehouse query:', userEmail);
            
            // Show loader
            const loader = document.getElementById('loader');
            if (loader) loader.classList.remove('d-none');
            
            // IMPORTANT: Always treat first carrier section as Delhivery
            // This is the most reliable way to identify all Delhivery carriers
            
            // Get the carrier section this button belongs to
            const cardElement = button.closest('.card');
            const parentRow = cardElement?.closest('.row');
            
            // Check if this is the first section (Delhivery section)
            // In your UI, the first row with cards is always the Delhivery section
            let isDelhivery = false;
            
            // Method 1: Check if we're in the first set of cards
            const resultsContainer = document.getElementById('resultsContainer');
            if (resultsContainer && parentRow) {
                const allRows = resultsContainer.querySelectorAll('.row');
                if (allRows.length > 0 && allRows[0] === parentRow) {
                    isDelhivery = true;
                }
            }
            
            // Method 2: Check by carrier name for specific keywords
            if (carrierName) {
                const delhiveryKeywords = ['b2br', 'b2brc', 'toolik'];
                for (const keyword of delhiveryKeywords) {
                    if (carrierName.toLowerCase().includes(keyword)) {
                        isDelhivery = true;
                        break;
                    }
                }
            }
            
            // Method 3: Look for section header text
            const cardHeader = cardElement?.closest('.card')?.previousElementSibling;
            if (cardHeader && cardHeader.textContent) {
                const headerText = cardHeader.textContent.toLowerCase();
                if (['b2br', 'b2brc', 'toolik'].some(h => headerText.includes(h))) {
                    isDelhivery = true;
                }
            }
            
            // Only Delhivery carriers should use the dropdowns
            // Other carriers like Bigship will use the original warehouse selection
            
            console.log('Carrier name:', carrierName, 'isDelhivery:', isDelhivery);
            
            if (isDelhivery) {
                // For Delhivery, redirect to the new manifest creation page with dropdowns
                const manifestUrl = new URL('create-manifest.html', window.location.href);
                
                // Add parameters
                manifestUrl.searchParams.append('carrier', carrierName);
                manifestUrl.searchParams.append('rate', rate);
                manifestUrl.searchParams.append('email', userEmail);
                
                // Add source and destination pincodes if available
                const sourcePincode = document.getElementById('sourcePincode')?.value;
                const destinationPincode = document.getElementById('destinationPincode')?.value;
                
                if (sourcePincode) manifestUrl.searchParams.append('sourcePincode', sourcePincode);
                if (destinationPincode) manifestUrl.searchParams.append('destinationPincode', destinationPincode);
                
                // Add payment type and COD amount if applicable
                const paymentType = document.getElementById('paymentType')?.value;
                if (paymentType) manifestUrl.searchParams.append('paymentType', paymentType);
                
                if (paymentType === 'COD') {
                    const codAmount = document.getElementById('codAmount')?.value;
                    if (codAmount) manifestUrl.searchParams.append('codAmount', codAmount);
                }
                
                // Collect and pass dimensions data
                try {
                    console.log('Getting dimension data from page...');
                    
                    // Find dimension data from boxes container
                    const boxRows = document.querySelectorAll('#boxesContainer .box-row');
                    if (boxRows && boxRows.length > 0) {
                        console.log(`Found ${boxRows.length} box rows`);
                        
                        let dimensionsStr = [];
                        let totalBoxes = 0;
                        let totalWeight = 0;
                        
                        boxRows.forEach((row, index) => {
                            // Get inputs by class names
                            const lengthInput = row.querySelector('.box-length');
                            const widthInput = row.querySelector('.box-width');
                            const heightInput = row.querySelector('.box-height');
                            const weightInput = row.querySelector('.box-weight');
                            
                            // Get values
                            const length = lengthInput ? lengthInput.value : '';
                            const width = widthInput ? widthInput.value : '';
                            const height = heightInput ? heightInput.value : '';
                            const weight = weightInput ? weightInput.value : '';
                            
                            console.log(`Box ${index}: L=${length}, W=${width}, H=${height}, W=${weight}kg`);
                            
                            // Add to dimensions string if valid
                            if (length && width && height) {
                                // Format: L-W-H-W (Length-Width-Height-Weight)
                                dimensionsStr.push(`${length}-${width}-${height}-${weight}`);
                                totalBoxes++;
                                totalWeight += parseFloat(weight) || 0;
                            }
                        });
                        
                        // Add parameters to URL
                        if (dimensionsStr.length > 0) {
                            console.log('Adding dimensions to URL:', dimensionsStr.join('|'));
                            manifestUrl.searchParams.append('dims', dimensionsStr.join('|'));
                            manifestUrl.searchParams.append('boxes', totalBoxes);
                            manifestUrl.searchParams.append('weight', totalWeight.toFixed(2));
                        }
                    } else {
                        // Try alternate method - find total boxes and weight directly
                        const totalBoxesInput = document.getElementById('totalBoxes');
                        const totalWeightInput = document.getElementById('totalWeight');
                        
                        if (totalBoxesInput && totalWeightInput) {
                            const boxes = totalBoxesInput.value;
                            const weight = totalWeightInput.value;
                            
                            if (boxes) manifestUrl.searchParams.append('boxes', boxes);
                            if (weight) manifestUrl.searchParams.append('weight', weight);
                            
                            console.log(`Using total values: Boxes=${boxes}, Weight=${weight}kg`);
                        }
                        
                        // Try to get dimensions from dimension rows (alternative layout)
                        const dimensionRows = document.querySelectorAll('.dimension-row');
                        if (dimensionRows && dimensionRows.length > 0) {
                            console.log(`Found ${dimensionRows.length} dimension rows as backup`);
                            
                            let dimsArray = [];
                            
                            dimensionRows.forEach((row, index) => {
                                // Get all number inputs in the row
                                const inputs = row.querySelectorAll('input[type="number"]');
                                if (inputs.length >= 3) {
                                    const l = inputs[0].value || '';
                                    const w = inputs[1].value || '';
                                    const h = inputs[2].value || '';
                                    const count = (inputs.length > 3) ? (inputs[3].value || '1') : '1';
                                    
                                    if (l && w && h) {
                                        dimsArray.push(`${l}-${w}-${h}-${count}`);
                                    }
                                }
                            });
                            
                            if (dimsArray.length > 0) {
                                manifestUrl.searchParams.append('dims', dimsArray.join('|'));
                            }
                        }
                    }
                } catch (error) {
                    console.error('Error collecting dimensions data:', error);
                }
                
                // Hide loader before redirecting
                if (loader) loader.classList.add('d-none');
                
                // Redirect to the manifest creation page
                window.location.href = manifestUrl.toString();
            } else {
                // For other carriers, use the original warehouse selection flow
                fetchWarehouses(userEmail)
                    .then(warehouseData => {
                        // Hide loader
                        if (loader) loader.classList.add('d-none');
                        
                        // Display warehouse options directly on the page (not in a modal)
                        displayWarehouseOptions(warehouseData, carrierName, rate);
                    })
                    .catch(error => {
                        console.error('Error fetching warehouses:', error);
                        
                        // Instead of showing an alert, retry after 1 second
                        console.log('Retrying warehouse fetch in 1 second...');
                        setTimeout(() => {
                            // Keep loader visible for retry
                            if (loader) loader.classList.remove('d-none');
                            
                            fetchWarehouses(userEmail)
                                .then(warehouseData => {
                                    // Hide loader
                                    if (loader) loader.classList.add('d-none');
                                    
                                    // Display warehouse options directly on the page
                                    displayWarehouseOptions(warehouseData, carrierName, rate);
                                })
                                .catch(retryError => {
                                    // Hide loader after final attempt
                                    if (loader) loader.classList.add('d-none');
                                    console.error('Retry failed:', retryError);
                                });
                        }, 1000);
                    });
            }
                ;
            
            return false;
        }
    });
    
    // Function to fetch warehouses using the PHP proxy
    function fetchWarehouses(userEmail) {
        return fetch(`warehouse-proxy.php?user_id=${encodeURIComponent(userEmail)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Handle different response formats
                if (data.warehouses) {
                    // New format with warehouses property
                    return data.warehouses;
                } else if (data.data) {
                    // Format with data property
                    return data.data;
                } else if (Array.isArray(data)) {
                    // Direct array format
                    return data;
                } else {
                    throw new Error('Unexpected response format from warehouse API');
                }
            });
    }
    
    // Function to display warehouse options in the page (not modal)
    function displayWarehouseOptions(warehouses, carrierName, rate) {
        // Create warehouse selection section if it doesn't exist
        let warehouseSection = document.getElementById('warehouseSelectionSection');
        
        if (!warehouseSection) {
            warehouseSection = document.createElement('div');
            warehouseSection.id = 'warehouseSelectionSection';
            warehouseSection.className = 'container mt-4';
            
            // Find the right place to insert this section
            const resultsSection = document.getElementById('resultsSection');
            if (resultsSection) {
                resultsSection.parentNode.insertBefore(warehouseSection, resultsSection.nextSibling);
            } else {
                // Fallback - append to body
                document.body.appendChild(warehouseSection);
            }
        }
        
        // Create content for warehouse selection
        warehouseSection.innerHTML = `
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Select Pickup Location</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Selected Freight Option:</strong> 
                                ${carrierName ? `<span class="ms-2">${carrierName}</span>` : ''}
                                ${rate ? `<span class="ms-2">₹${rate}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="warehouseCards">
                        ${renderWarehouseCards(warehouses)}
                    </div>
                </div>
            </div>
        `;
        
        // Scroll to warehouse section
        warehouseSection.scrollIntoView({ behavior: 'smooth' });
        
        // Add event listeners to warehouse cards
        const selectButtons = warehouseSection.querySelectorAll('.select-warehouse-btn');
        selectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const warehouseCard = button.closest('.warehouse-card');
                if (!warehouseCard) return;
                
                // Get warehouse data
                const warehouseId = warehouseCard.dataset.warehouseId;
                const warehouseCity = warehouseCard.dataset.city;
                const warehouseState = warehouseCard.dataset.state;
                
                // Open order form with this data
                openOrderForm(warehouseId, warehouseCity, warehouseState, carrierName, rate);
            });
        });
    }
    
    // Function to render warehouse cards
    function renderWarehouseCards(warehouses) {
        if (!warehouses || warehouses.length === 0) {
            return `
                <div class="col-12">
                    <div class="alert alert-warning">
                        No pickup locations found. Please try again later.
                    </div>
                </div>
            `;
        }
        
        return warehouses.map(warehouse => {
            const isDefault = warehouse.is_default === true || warehouse.is_default === 'true';
            
            return `
                <div class="col-md-4 mb-3">
                    <div class="card warehouse-card ${isDefault ? 'border-primary' : ''}" 
                         data-warehouse-id="${warehouse.id || ''}"
                         data-city="${warehouse.city || ''}"
                         data-state="${warehouse.state || ''}">
                        <div class="card-header ${isDefault ? 'bg-primary text-white' : 'bg-light'}">
                            <h5 class="card-title mb-0">
                                ${warehouse.name || 'Pickup Location'}
                                ${isDefault ? '<span class="badge bg-warning text-dark ms-2">Default</span>' : ''}
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><strong>Location ID:</strong> ${warehouse.id || ''}</p>
                            <p class="mb-0"><strong>City:</strong> ${warehouse.city || ''}</p>
                            <p class="mb-0"><strong>State:</strong> ${warehouse.state || ''}</p>
                            <p class="mb-0"><strong>Address:</strong> ${warehouse.address || ''}</p>
                            <p class="mb-0"><strong>Pincode:</strong> ${warehouse.pincode || ''}</p>
                            
                            <div class="d-grid gap-2 mt-3">
                                <button type="button" class="btn btn-primary select-warehouse-btn">
                                    Select This Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Function to open the order form with warehouse data
    function openOrderForm(warehouseId, warehouseCity, warehouseState, carrierName, rate) {
        // Safety check: only proceed if warehouse data is provided
        if (!warehouseId || !warehouseCity || !warehouseState) {
            console.error('Cannot open order form: Missing warehouse data');
            alert('Please select a pickup location first');
            return;
        }
        
        // For simplicity, we'll use URL parameters to pass the data
        // In a real app, you might use localStorage or session storage
        const orderFormUrl = new URL('create-order.html', window.location.href);
        
        // Add parameters
        orderFormUrl.searchParams.append('pickUpId', warehouseId);
        orderFormUrl.searchParams.append('pickUpCity', warehouseCity);
        orderFormUrl.searchParams.append('pickUpState', warehouseState);
        if (carrierName) orderFormUrl.searchParams.append('carrier', carrierName);
        if (rate) orderFormUrl.searchParams.append('rate', rate);
        
        // Add other common parameters from the freight form if available
        const sourcePincode = document.getElementById('sourcePincode')?.value;
        const destinationPincode = document.getElementById('destinationPincode')?.value;
        const invoiceAmount = document.getElementById('invoiceAmount')?.value;
        
        if (sourcePincode) orderFormUrl.searchParams.append('sourcePincode', sourcePincode);
        if (destinationPincode) orderFormUrl.searchParams.append('destinationPincode', destinationPincode);
        if (invoiceAmount) orderFormUrl.searchParams.append('invoiceAmount', invoiceAmount);
        
        // Redirect to the order form
        window.location.href = orderFormUrl.toString();
    }
    
    // Handle URL parameters in the order form page
    if (window.location.pathname.includes('create-order.html')) {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Set pickup location fields if provided
        if (urlParams.has('pickUpId')) {
            document.getElementById('pickUpId').value = urlParams.get('pickUpId');
        }
        
        if (urlParams.has('pickUpCity')) {
            document.getElementById('pickUpCity').value = urlParams.get('pickUpCity');
        }
        
        if (urlParams.has('pickUpState')) {
            document.getElementById('pickUpState').value = urlParams.get('pickUpState');
        }
        
        // Set return location fields (same as pickup by default)
        if (document.getElementById('sameAsPickup').checked) {
            document.getElementById('retrunId').value = document.getElementById('pickUpId').value;
            document.getElementById('returnCity').value = document.getElementById('pickUpCity').value;
            document.getElementById('returnState').value = document.getElementById('pickUpState').value;
        }
        
        // Set invoice amount if provided
        if (urlParams.has('invoiceAmount')) {
            document.getElementById('invoiceAmt').value = urlParams.get('invoiceAmount');
        }
        
        // Generate suggested order ID (date-based)
        if (!document.getElementById('orderIds').value) {
            const now = new Date();
            const year = now.getFullYear().toString().slice(-2);
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
            
            document.getElementById('orderIds').value = `ORD-${year}${month}${day}-${random}`;
        }
    }
});



