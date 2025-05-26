    // Function to load fallback pickup locations (demo data)
    function loadFallbackPickupLocations() {
        const pickupSelect = document.getElementById('pickupLocation');
        
        if (!pickupSelect) return;
        
        // Clear existing options and add a default option
        pickupSelect.innerHTML = '<option value="">Select a pickup location</option>';
        
        // Add fallback locations
        const fallbackLocations = [
            {
                id: "DEL001",
                name: "Delhi Warehouse",
                address: "123 Industrial Area, Phase 1",
                city: "Delhi",
                state: "Delhi",
                pincode: "110001",
                contact: "Warehouse Manager",
                phone: "9876543210"
            },
            {
                id: "MUM001",
                name: "Mumbai Distribution Center",
                address: "456 MIDC, Andheri East",
                city: "Mumbai",
                state: "Maharashtra",
                pincode: "400093",
                contact: "Operations Head",
                phone: "9876543211"
            }
        ];
        
        // Add locations to dropdown
        fallbackLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.name + ' - ' + location.city + ', ' + location.state;
            option.dataset.location = JSON.stringify(location);
            
            pickupSelect.appendChild(option);
        });
    }
    
    // Function to load fallback drop locations (demo data)
    function loadFallbackDropLocations() {
        const dropSelect = document.getElementById('dropLocation');
        
        if (!dropSelect) return;
        
        // Clear existing options and add a default option
        dropSelect.innerHTML = '<option value="">Select a drop location</option>';
        
        // Add fallback locations
        const fallbackLocations = [
            {
                id: "CHN001",
                name: "Chennai Distribution Hub",
                address: "101 Industrial Estate, Guindy",
                city: "Chennai",
                state: "Tamil Nadu",
                pincode: "600032",
                contact: "Facility Manager",
                phone: "9876543213"
            },
            {
                id: "KOL001",
                name: "Kolkata Warehouse",
                address: "202 Salt Lake City",
                city: "Kolkata",
                state: "West Bengal",
                pincode: "700091",
                contact: "Operations Head",
                phone: "9876543214"
            }
        ];
        
        // Add locations to dropdown
        fallbackLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.name + ' - ' + location.city + ', ' + location.state;
            option.dataset.location = JSON.stringify(location);
            
            dropSelect.appendChild(option);
        });
    }
    
    // Function to show location details
    function showLocationDetails(locationType) {
        const select = document.getElementById(`${locationType}Location`);
        const details = document.getElementById(`${locationType}Details`);
        const locationDetails = document.getElementById(`${locationType}LocationDetails`);
        
        if (!select || !details || !locationDetails) return;
        
        if (select.selectedIndex > 0) {
            const selectedOption = select.options[select.selectedIndex];
            try {
                const locationData = JSON.parse(selectedOption.dataset.location);
                
                // Store selected location
                if (locationType === 'pickup') {
                    selectedPickupLocation = locationData;
                } else {
                    selectedDropLocation = locationData;
                }
                
                // Show location details
                details.classList.remove('d-none');
                locationDetails.innerHTML = `
                    <p class="mb-1"><strong>Location ID:</strong> ${locationData.id || 'N/A'}</p>
                    <p class="mb-1"><strong>Name:</strong> ${locationData.name || 'N/A'}</p>
                    <p class="mb-1"><strong>Address:</strong> ${locationData.address || 'N/A'}</p>
                    <p class="mb-1"><strong>City:</strong> ${locationData.city || 'N/A'}</p>
                    <p class="mb-1"><strong>State:</strong> ${locationData.state || 'N/A'}</p>
                    <p class="mb-1"><strong>Pincode:</strong> ${locationData.pincode || 'N/A'}</p>
                    <p class="mb-1"><strong>Contact:</strong> ${locationData.contact || 'N/A'}</p>
                    <p class="mb-1"><strong>Phone:</strong> ${locationData.phone || 'N/A'}</p>
                    <div class="mt-2">
                        <span class="badge bg-info">API: Delhivery Warehouse ${locationType === 'pickup' ? 'Picking' : 'Drop'} Location</span>
                    </div>
                `;
                
                // Clear billing address when location changes
                clearBillingAddress();
            } catch (e) {
                console.error(`Error parsing ${locationType} location data:`, e);
                details.classList.add('d-none');
                if (locationType === 'pickup') {
                    selectedPickupLocation = null;
                } else {
                    selectedDropLocation = null;
                }
            }
        } else {
            details.classList.add('d-none');
            if (locationType === 'pickup') {
                selectedPickupLocation = null;
            } else {
                selectedDropLocation = null;
            }
        }
    }
    
    // Function to fetch billing address
    function fetchBillingAddress() {
        // Determine which facility ID to use based on freight mode
        const isFreightOnDelivery = document.getElementById('freightModeFOD').checked;
        const freightMode = isFreightOnDelivery ? 'fod' : 'fop';
        
        let facilityId = null;
        
        if (isFreightOnDelivery && selectedDropLocation) {
            // FOD: Use drop location facility ID
            facilityId = selectedDropLocation.id;
        } else if (!isFreightOnDelivery && selectedPickupLocation) {
            // FOP: Use pickup location facility ID
            facilityId = selectedPickupLocation.id;
        }
        
        if (!facilityId) {
            const locationTypeNeeded = isFreightOnDelivery ? 'drop' : 'pickup';
            document.getElementById('billingAddressStatus').innerHTML = 
                `<div class="alert alert-warning">Please select a ${locationTypeNeeded} location first.</div>`;
            return;
        }
        
        // Show loading indicator
        document.getElementById('billingAddressStatus').innerHTML = 
            `<div class="alert alert-info">Fetching billing address...</div>`;
        
        // Construct API URL
        const apiUrl = `http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-billing-addresses?login_type=main&facility_id=${facilityId}&freight_mode=${freightMode}`;
        
        console.log('Fetching billing address from:', apiUrl);
        
        // Create a proxy URL to avoid CORS issues
        const proxyUrl = 'billing-address-proxy.php';
        
        // Fetch data using the proxy
        fetch(proxyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ url: apiUrl })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Billing address API response:', data);
            
            if (data && data.results && Array.isArray(data.results) && data.results.length > 0) {
                // Use the first billing address from results
                const addressData = data.results[0];
                billingAddressData = addressData; // Store for later use
                
                // Get address details from the response
                const addressDetails = addressData.address_details || {};
                const billingDetails = addressData.billing_details || {};
                const shippingAddress = addressData.shipping_address || {};
                
                // Populate billing address fields
                document.getElementById('billingName').value = addressDetails.contact_person || shippingAddress.name || 'N/A';
                document.getElementById('billingCompany').value = addressDetails.company || 'N/A';
                document.getElementById('billingConsignor').value = addressDetails.company || 'N/A';
                document.getElementById('billingAddress').value = shippingAddress.line1 || addressDetails.address || 'N/A';
                document.getElementById('billingCity').value = shippingAddress.city || 'N/A';
                document.getElementById('billingState').value = shippingAddress.state || 'N/A';
                document.getElementById('billingPin').value = shippingAddress.pin_code || 'N/A';
                document.getElementById('billingPhone').value = addressDetails.phone_number || 'N/A';
                
                // Handle optional fields
                if (billingDetails) {
                    document.getElementById('billingPan').value = billingDetails.pan_number || 'N/A';
                    document.getElementById('billingGst').value = billingDetails.gst_number || 'N/A';
                } else {
                    document.getElementById('billingPan').value = 'N/A';
                    document.getElementById('billingGst').value = 'N/A';
                }
                
                // Show success message
                document.getElementById('billingAddressStatus').innerHTML = 
                    `<div class="alert alert-success">Billing address loaded successfully!</div>`;
            } else {
                // No billing address found
                document.getElementById('billingAddressStatus').innerHTML = 
                    `<div class="alert alert-warning">No billing address found for this location.</div>`;
            }
        })
        .catch(error => {
            console.error('Error fetching billing address:', error);
            document.getElementById('billingAddressStatus').innerHTML = 
                `<div class="alert alert-danger">Error loading billing address: ${error.message}</div>`;
        });
    }
    
    // Function to clear billing address fields
    function clearBillingAddress() {
        document.getElementById('billingName').value = '';
        document.getElementById('billingCompany').value = '';
        document.getElementById('billingConsignor').value = '';
        document.getElementById('billingAddress').value = '';
        document.getElementById('billingCity').value = '';
        document.getElementById('billingState').value = '';
        document.getElementById('billingPin').value = '';
        document.getElementById('billingPhone').value = '';
        document.getElementById('billingPan').value = '';
        document.getElementById('billingGst').value = '';
        document.getElementById('billingAddressStatus').innerHTML = '';
        
        billingAddressData = null;
    }
    
    // Function to set up repeatable form sections
    function setupRepeatableSections() {
        // Set up remove buttons for initial rows
        setupRemoveButtons();
    }
    
    // Function to set up remove buttons for all repeatable rows
    function setupRemoveButtons() {
        // Setup invoice remove buttons
        document.querySelectorAll('.remove-invoice').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.invoice-row');
                if (document.querySelectorAll('.invoice-row').length > 1) {
                    row.remove();
                }
            });
        });
        
        // Setup dimension remove buttons
        document.querySelectorAll('.remove-dimension').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.dimension-row');
                if (document.querySelectorAll('.dimension-row').length > 1) {
                    row.remove();
                }
            });
        });
        
        // Setup shipment remove buttons
        document.querySelectorAll('.remove-shipment').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('.shipment-row');
                if (document.querySelectorAll('.shipment-row').length > 1) {
                    row.remove();
                }
            });
        });
        
        // Show/hide remove buttons based on row count
        updateRemoveButtonsVisibility();
    }
    
    // Function to update visibility of remove buttons
    function updateRemoveButtonsVisibility() {
        // Invoice rows
        const invoiceRows = document.querySelectorAll('.invoice-row');
        invoiceRows.forEach(row => {
            const removeButton = row.querySelector('.remove-invoice');
            if (removeButton) {
                removeButton.style.display = invoiceRows.length > 1 ? 'block' : 'none';
            }
        });
        
        // Dimension rows
        const dimensionRows = document.querySelectorAll('.dimension-row');
        dimensionRows.forEach(row => {
            const removeButton = row.querySelector('.remove-dimension');
            if (removeButton) {
                removeButton.style.display = dimensionRows.length > 1 ? 'block' : 'none';
            }
        });
        
        // Shipment rows
        const shipmentRows = document.querySelectorAll('.shipment-row');
        shipmentRows.forEach(row => {
            const removeButton = row.querySelector('.remove-shipment');
            if (removeButton) {
                removeButton.style.display = shipmentRows.length > 1 ? 'block' : 'none';
            }
        });
    }
    
    // Function to add invoice row
    function addInvoiceRow() {
        const container = document.getElementById('invoicesContainer');
        const template = document.querySelector('.invoice-row');
        const newRow = template.cloneNode(true);
        
        // Clear input values
        newRow.querySelectorAll('input').forEach(input => {
            input.value = '';
        });
        
        // Add event listener to remove button
        const removeButton = newRow.querySelector('.remove-invoice');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (document.querySelectorAll('.invoice-row').length > 1) {
                    newRow.remove();
                    updateRemoveButtonsVisibility();
                }
            });
        }
        
        container.appendChild(newRow);
        updateRemoveButtonsVisibility();
    }
