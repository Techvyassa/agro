// Manifest Form - Handles creation and submission of manifest forms
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedPickupLocation = null;
    let selectedDropLocation = null;
    let billingAddressData = null;
    let loginType = 'b2b'; // Default login type
    
    // Get data from URL parameters (passed from the freight page)
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set carrier and rate information if available
    if (urlParams.has('carrier')) {
        let carrierName = urlParams.get('carrier');
        // Set the global loginType variable based on carrier
        loginType = urlParams.get('carrier').toLowerCase();
        console.log('Setting login type from carrier:', loginType);
        
        // Check if this is a special Delhivery service like B2B, B2C, etc.
        // If it doesn't explicitly have "Delhivery" in the name, add it for clarity
        if (!carrierName.toLowerCase().includes('delhivery')) {
            // Check if this might be a Delhivery service code (B2B, B2C, etc.)
            if (['b2b', 'b2c', 'one delhivery'].some(code => carrierName.toLowerCase().includes(code))) {
                carrierName = 'Delhivery ' + carrierName;
            }
        }
        
        document.getElementById('carrierName').textContent = carrierName;
        document.getElementById('carrier-id').value = carrierName;
    } else {
        // Default to Delhivery if no carrier specified
        document.getElementById('carrierName').textContent = 'Delhivery Service';
        document.getElementById('carrier-id').value = 'Delhivery';
    }
    
    if (urlParams.has('rate')) {
        document.getElementById('freightRate').textContent = urlParams.get('rate');
        document.getElementById('freight-rate').value = urlParams.get('rate');
    }
    
    if (urlParams.has('sourcePincode')) {
        document.getElementById('source-pincode').value = urlParams.get('sourcePincode');
    }
    
    if (urlParams.has('destinationPincode')) {
        document.getElementById('destination-pincode').value = urlParams.get('destinationPincode');
    }
    
    if (urlParams.has('email')) {
        document.getElementById('user-name').value = urlParams.get('email');
    }
    
    // Set up event listeners
    setupEventListeners();
    
    // Initial load of pickup and drop locations
    loadPickupLocations();
    loadDropLocations();
    
    // Setup repeatable form sections
    setupRepeatableSections();
    
    // Setup billing address dropdown change event
    document.getElementById('billingAddressSelect').addEventListener('change', updateBillingFields);
    
    // Function to set up all event listeners
    function setupEventListeners() {
        // Pickup and drop location change events
        document.getElementById('pickupLocation').addEventListener('change', function() {
            showLocationDetails('pickup');
        });
        
        document.getElementById('dropLocation').addEventListener('change', function() {
            showLocationDetails('drop');
        });
        
        // Load location buttons
        document.getElementById('loadPickupLocationsBtn').addEventListener('click', function() {
            loadPickupLocations(true); // true = force reload
        });
        
        document.getElementById('loadDropLocationsBtn').addEventListener('click', function() {
            loadDropLocations(true); // true = force reload
        });
        
        // Freight mode change event
        const freightModeRadios = document.querySelectorAll('input[name="freightMode"]');
        freightModeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Clear billing address when freight mode changes
                clearBillingAddress();
            });
        });
        
        // Fetch billing address button
        document.getElementById('fetchBillingAddressBtn').addEventListener('click', fetchBillingAddress);
        
        // Add buttons for repeatable sections
        document.getElementById('addInvoiceBtn').addEventListener('click', addInvoiceRow);
        document.getElementById('addDimensionBtn').addEventListener('click', addDimensionRow);
        document.getElementById('addShipmentBtn').addEventListener('click', addShipmentRow);
        
        // Create manifest button
        document.getElementById('createManifestBtn').addEventListener('click', createManifest);
    }
    
    // Function to load pickup locations from API
    function loadPickupLocations(forceReload = false) {
        const pickupSelect = document.getElementById('pickupLocation');
        const pickupStatus = document.getElementById('pickupStatus');
        const pickupLoader = document.getElementById('pickupLoader');
        
        if (!pickupSelect || !pickupStatus) return;
        
        // Show loader
        if (pickupLoader) pickupLoader.style.display = 'block';

        // Add search input above the pickup dropdown if not present
        if (!document.getElementById('pickupLocationSearch')) {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control mb-2';
            searchInput.placeholder = 'Search pickup location...';
            searchInput.id = 'pickupLocationSearch';
            pickupSelect.parentNode.insertBefore(searchInput, pickupSelect);
            searchInput.addEventListener('input', function() {
                pickupSelect.dataset.searchTerm = searchInput.value;
                loadPickupLocations(true);
            });
        }

        // Use the global loginType variable
        console.log('Using login type for pickup locations:', loginType);
        
        // Construct API URL with timestamp to prevent caching
        const timestamp = new Date().getTime();
        const searchTerm = pickupSelect.dataset.searchTerm ? pickupSelect.dataset.searchTerm.trim() : '';
const apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=' + 
                      loginType + '&location_type=picking' + (searchTerm ? ('&search_term=' + encodeURIComponent(searchTerm)) : '') + '&page=1&page_size=100&_=' + timestamp;
        
        // Show what URL we're using
        console.log('Loading pickup locations from:', apiUrl);
        
        // Create a proxy URL to avoid CORS issues
        const proxyUrl = 'location-proxy.php';
        
        // Fetch data using the proxy
        fetch(proxyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ url: apiUrl })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Pickup API response:', data);
            
            // Hide loader
            if (pickupLoader) pickupLoader.style.display = 'none';
            
            // Process the response based on the actual API structure
            if (data && data.results && Array.isArray(data.results)) {
                // Clear existing options and add a default option
                pickupSelect.innerHTML = '<option value="">Select a pickup location</option>';
                
                // Add locations to dropdown
                data.results.forEach(location => {
                    try {
                        // Extract location details from the API response with proper handling of nested structures
                        let address = location.address || {};
                        let returnAddress = address.return_address || {};
                        // Prefer facility_name from location, then address
                        const facilityName = location.facility_name || address.facility_name || 'Unknown Location';
                        // Prefer city/state from address, fallback to return_address
                        const city = address.city || returnAddress.city || '';
                        const state = address.state || returnAddress.state || '';
                        // Prefer pin_code from address, fallback to return_address
                        const pinCode = address.pin_code || returnAddress.pin_code || '';
                        const locationData = {
                            id: location.facility_id || location._id || 'UNKNOWN',
                            name: facilityName,
                            address: address.address_line1 || '',
                            city: city,
                            state: state,
                            pincode: pinCode,
                            contact: address.contact_person || '',
                            phone: address.phone || '',
                            raw: location // Store raw data for debugging
                        };
                        console.log('Extracted pickup location data:', locationData);
                        const option = document.createElement('option');
                        option.value = locationData.id;
                        option.textContent = `${facilityName}${city ? ' - ' + city : ''}${state ? ', ' + state : ''}${pinCode ? ' (PIN: ' + pinCode + ')' : ''}`;
                        option.dataset.location = JSON.stringify(locationData);
                        pickupSelect.appendChild(option);
                    } catch (e) {
                        console.error('Error processing location data:', e, location);
                    }
                });
                
                // Show success message
                pickupStatus.innerHTML = `<span class="text-success">
                    <i class="fas fa-check-circle"></i> 
                    Loaded ${data.results.length} pickup locations
                    <span class="badge bg-success ms-1">API Data</span>
                </span>`;
                
                // If there's only one location, select it automatically
                if (data.results.length === 1) {
                    pickupSelect.selectedIndex = 1;
                    showLocationDetails('pickup');
                }
            } else {
                // Show error and load fallback data
                pickupStatus.innerHTML = `<span class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Could not load locations from API. Using demo data.
                </span>`;
                loadFallbackPickupLocations();
            }
        })
        .catch(error => {
            console.error('Error fetching pickup locations:', error);
            
            // Hide loader
            if (pickupLoader) pickupLoader.style.display = 'none';
            
            // Show error and load fallback data
            pickupStatus.innerHTML = `<span class="text-danger">
                <i class="fas fa-exclamation-circle"></i> 
                Error loading locations: ${error.message}. Using demo data.
            </span>`;
            loadFallbackPickupLocations();
        });
    }
    
    // Function to load drop locations from API
    function loadDropLocations(forceReload = false) {
        const dropSelect = document.getElementById('dropLocation');
        const dropStatus = document.getElementById('dropStatus');
        const dropLoader = document.getElementById('dropLoader');
        
        if (!dropSelect || !dropStatus) return;
        
        // Show loader
        if (dropLoader) dropLoader.style.display = 'block';

        // Add search input above the drop dropdown if not present
        if (!document.getElementById('dropLocationSearch')) {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control mb-2';
            searchInput.placeholder = 'Search drop location...';
            searchInput.id = 'dropLocationSearch';
            dropSelect.parentNode.insertBefore(searchInput, dropSelect);
            searchInput.addEventListener('input', function() {
                dropSelect.dataset.searchTerm = searchInput.value;
                loadDropLocations(true);
            });
        }

        // Use the global loginType variable
        console.log('Using login type for drop locations:', loginType);
        
        // Construct API URL with timestamp to prevent caching
        const timestamp = new Date().getTime();
        const searchTerm = dropSelect.dataset.searchTerm ? dropSelect.dataset.searchTerm.trim() : '';
const apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=' + 
                  loginType + '&location_type=drop' + (searchTerm ? ('&search_term=' + encodeURIComponent(searchTerm)) : '') + '&page=1&page_size=100&_=' + timestamp;
        
        // Show what URL we're using
        console.log('Loading drop locations from:', apiUrl);
        
        // Create a proxy URL to avoid CORS issues
        const proxyUrl = 'location-proxy.php';
        
        // Fetch data using the proxy
        fetch(proxyUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ url: apiUrl })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Drop API response:', data);
            
            // Hide loader
            if (dropLoader) dropLoader.style.display = 'none';
            
            // Process the response based on the actual API structure
            if (data && data.results && Array.isArray(data.results)) {
                // Clear existing options and add a default option
                dropSelect.innerHTML = '<option value="">Select a drop location</option>';
                
                // Add locations to dropdown
                data.results.forEach(location => {
                    try {
                        // Extract location details from the API response with proper handling of nested structures
                        let address = location.address || {};
                        let returnAddress = address.return_address || {};
                        // Prefer facility_name from location, then address
                        const facilityName = location.facility_name || address.facility_name || 'Unknown Location';
                        // Prefer city/state from address, fallback to return_address
                        const city = address.city || returnAddress.city || '';
                        const state = address.state || returnAddress.state || '';
                        // Prefer pin_code from address, fallback to return_address
                        const pinCode = address.pin_code || returnAddress.pin_code || '';
                        const locationData = {
                            id: location.facility_id || location._id || 'UNKNOWN',
                            name: facilityName,
                            address: address.address_line1 || '',
                            city: city,
                            state: state,
                            pincode: pinCode,
                            contact: address.contact_person || '',
                            phone: address.phone || '',
                            raw: location // Store raw data for debugging
                        };
                        console.log('Extracted drop location data:', locationData);
                        const option = document.createElement('option');
                        option.value = locationData.id;
                        option.textContent = `${facilityName}${city ? ' - ' + city : ''}${state ? ', ' + state : ''}${pinCode ? ' (PIN: ' + pinCode + ')' : ''}`;
                        option.dataset.location = JSON.stringify(locationData);
                        dropSelect.appendChild(option);
                    } catch (e) {
                        console.error('Error processing location data:', e, location);
                    }
                });
                
                // Show success message
                dropStatus.innerHTML = `<span class="text-success">
                    <i class="fas fa-check-circle"></i> 
                    Loaded ${data.results.length} drop locations
                    <span class="badge bg-success ms-1">API Data</span>
                </span>`;
                
                // If there's only one location, select it automatically
                if (data.results.length === 1) {
                    dropSelect.selectedIndex = 1;
                    showLocationDetails('drop');
                }
            } else {
                // Show error and load fallback data
                dropStatus.innerHTML = `<span class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Could not load locations from API. Using demo data.
                </span>`;
                loadFallbackDropLocations();
            }
        })
        .catch(error => {
            console.error('Error fetching drop locations:', error);
            
            // Hide loader
            if (dropLoader) dropLoader.style.display = 'none';
            
            // Show error and load fallback data
            dropStatus.innerHTML = `<span class="text-danger">
                <i class="fas fa-exclamation-circle"></i> 
                Error loading locations: ${error.message}. Using demo data.
            </span>`;
            loadFallbackDropLocations();
        });
    }
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
    
    // Function to fetch billing addresses and populate dropdown
    function fetchBillingAddress() {
        // Determine which facility ID to use based on freight mode
        const isFreightOnDelivery = document.getElementById('freightModeFOD').checked;
        const freightMode = isFreightOnDelivery ? 'fod' : 'fop';
        
        let facilityId = null;
        
        if (isFreightOnDelivery && selectedDropLocation) {
            // FOD: Use drop location facility ID
            facilityId = selectedDropLocation.id;
            console.log('Original drop location facility_id:', facilityId);
        } else if (!isFreightOnDelivery && selectedPickupLocation) {
            // FOP: Use pickup location facility ID
            facilityId = selectedPickupLocation.id;
            console.log('Original pickup location facility_id:', facilityId);
        }
        
        // Extract the UUID part if it has the 'delhivery::clientwarehouse::' prefix
        if (facilityId && facilityId.includes('delhivery::clientwarehouse::')) {
            facilityId = facilityId.split('delhivery::clientwarehouse::')[1];
            console.log('Extracted UUID from facility_id:', facilityId);
        }
        
        if (!facilityId) {
            const locationTypeNeeded = isFreightOnDelivery ? 'drop' : 'pickup';
            document.getElementById('billingAddressStatus').innerHTML = 
                `<div class="alert alert-warning">Please select a ${locationTypeNeeded} location first.</div>`;
            return;
        }
        
        // Show loading indicator
        document.getElementById('billingAddressStatus').innerHTML = 
            `<div class="alert alert-info">Fetching billing addresses...</div>`;
        
        // Get the dropdown element
        const addressSelect = document.getElementById('billingAddressSelect');
        addressSelect.disabled = true;
        addressSelect.innerHTML = '<option value="">Loading addresses...</option>';
        
        // Construct API URL
        const apiUrl = `http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-billing-addresses?login_type=${loginType}&facility_id=${facilityId}&freight_mode=${freightMode}`;
        
        console.log('Fetching billing addresses from:', apiUrl);
        
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
                // Store the billing addresses array for later use
                const billingAddresses = data.results;
                
                // Clear dropdown and add options for each address
                addressSelect.innerHTML = '<option value="">Select a billing address</option>';
                
                billingAddresses.forEach((address, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    
                    // Get address details
                    const addressDetails = address.address_details || {};
                    const billingDetails = address.billing_details || {};
                    const shippingAddress = address.shipping_address || {};
                    
                    // Check if PAN or GST is available
                    const hasPan = billingDetails && billingDetails.pan_number;
                    const hasGst = billingDetails && billingDetails.gst_number;
                    const isValid = hasPan || hasGst;
                    
                    // Create a display name for the address
                    const displayName = addressDetails.company || shippingAddress.name || 'Address ' + (index + 1);
                    const location = shippingAddress.city || '';
                    const state = shippingAddress.state || '';
                    
                    // Add warning for invalid addresses
                    if (!isValid) {
                        option.textContent = `⚠️ ${displayName}${location ? ' - ' + location : ''}${state ? ', ' + state : ''} (Missing PAN & GST)`;
                        option.disabled = true;
                        option.className = 'text-danger';
                    } else {
                        option.textContent = `${displayName}${location ? ' - ' + location : ''}${state ? ', ' + state : ''}`;
                        if (hasPan && hasGst) {
                            option.textContent += ' (PAN & GST available)';
                        } else if (hasPan) {
                            option.textContent += ' (PAN available)';
                        } else if (hasGst) {
                            option.textContent += ' (GST available)';
                        }
                    }
                    
                    // Store the full address data in a data attribute
                    option.dataset.address = JSON.stringify(address);
                    option.dataset.valid = isValid ? 'true' : 'false';
                    
                    addressSelect.appendChild(option);
                });
                
                // Enable the dropdown
                addressSelect.disabled = false;
                
                // Show success message
                document.getElementById('billingAddressStatus').innerHTML = 
                    `<div class="alert alert-success">Found ${billingAddresses.length} billing addresses. Please select one.</div>`;
                
                // If there's only one address, select it automatically
                if (billingAddresses.length === 1) {
                    addressSelect.selectedIndex = 1;
                    updateBillingFields();
                }
            } else {
                // No billing addresses found
                addressSelect.innerHTML = '<option value="">No addresses available</option>';
                addressSelect.disabled = true;
                
                // Clear all billing fields
                clearBillingAddress();
                
                document.getElementById('billingAddressStatus').innerHTML = 
                    `<div class="alert alert-warning">No billing addresses found for this location.</div>`;
            }
        })
        .catch(error => {
            console.error('Error fetching billing addresses:', error);
            
            // Reset dropdown
            addressSelect.innerHTML = '<option value="">Error loading addresses</option>';
            addressSelect.disabled = true;
            
            // Clear all billing fields
            clearBillingAddress();
            
            document.getElementById('billingAddressStatus').innerHTML = 
                `<div class="alert alert-danger">Error loading billing addresses: ${error.message}</div>`;
        });
    }
    
    // Function to update billing fields based on selected address
    function updateBillingFields() {
        const addressSelect = document.getElementById('billingAddressSelect');
        
        if (addressSelect.selectedIndex > 0) {
            const selectedOption = addressSelect.options[addressSelect.selectedIndex];
            try {
                const addressData = JSON.parse(selectedOption.dataset.address);
                const isValid = selectedOption.dataset.valid === 'true';
                
                if (!isValid) {
                    // Don't allow selection of addresses without PAN or GST
                    document.getElementById('billingAddressStatus').innerHTML = 
                        `<div class="alert alert-danger">This address is missing both PAN and GST numbers. At least one is required.</div>`;
                    clearBillingAddress();
                    return;
                }
                
                billingAddressData = addressData; // Store for later use
                
                // Get address details from the selected address
                const addressDetails = addressData.address_details || {};
                const billingDetails = addressData.billing_details || {};
                const shippingAddress = addressData.shipping_address || {};
                
                // Populate billing address fields
                document.getElementById('billingName').value = addressData.store_code_name || 'N/A';
                document.getElementById('billingCompany').value = addressDetails.company || 'N/A';
                document.getElementById('billingConsignor').value = addressDetails.contact_person || shippingAddress.store_code_name || 'N/A';
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
            } catch (e) {
                console.error('Error parsing address data:', e);
                document.getElementById('billingAddressStatus').innerHTML = 
                    `<div class="alert alert-danger">Error loading address: ${e.message}</div>`;
                clearBillingAddress();
            }
        } else {
            // No address selected, clear fields
            clearBillingAddress();
            document.getElementById('billingAddressStatus').innerHTML = '';
        }
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
        setupRemoveButtons();
        updateRemoveButtonsVisibility();
        
        // Add event listeners to existing box count inputs
        document.querySelectorAll('.dimension-box-count').forEach(input => {
            input.addEventListener('change', updateTotalBoxes);
        });
        
        updateTotalBoxes(); // Initialize total boxes count
    }
    
    // Function to calculate and update total boxes
    function updateTotalBoxes() {
        const totalBoxesField = document.getElementById('totalBoxes');
        if (!totalBoxesField) return;
        
        let totalBoxes = 0;
        document.querySelectorAll('.dimension-row').forEach(row => {
            const boxCountInput = row.querySelector('.dimension-box-count');
            if (boxCountInput && boxCountInput.value) {
                totalBoxes += parseInt(boxCountInput.value) || 0;
            }
        });
        
        // Update the total boxes field
        totalBoxesField.value = totalBoxes;
        
        // Update all shipment row box counts to match the total
        document.querySelectorAll('.shipment-box-count').forEach(input => {
            input.value = totalBoxes;
        });
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
    // Function to add dimension row
    function addDimensionRow() {
        const container = document.getElementById('dimensionsContainer');
        const template = document.querySelector('.dimension-row');
        const newRow = template.cloneNode(true);
        
        // Clear input values but maintain default box count
        newRow.querySelector('.dimension-length').value = '';
        newRow.querySelector('.dimension-width').value = '';
        newRow.querySelector('.dimension-height').value = '';
        newRow.querySelector('.dimension-weight').value = '';
        newRow.querySelector('.dimension-box-count').value = '1';
        
        // Add event listener to remove button
        const removeButton = newRow.querySelector('.remove-dimension');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (document.querySelectorAll('.dimension-row').length > 1) {
                    newRow.remove();
                    updateRemoveButtonsVisibility();
                    updateTotalBoxes(); // Update total boxes when removing a row
                }
            });
        }
        
        // Add event listener to box count input to update total
        const boxCountInput = newRow.querySelector('.dimension-box-count');
        if (boxCountInput) {
            boxCountInput.addEventListener('change', updateTotalBoxes);
        }
        
        container.appendChild(newRow);
        updateRemoveButtonsVisibility();
        updateTotalBoxes(); // Update total after adding a row
    }
    
    // Function to add shipment row
    function addShipmentRow() {
        const container = document.getElementById('shipmentContainer');
        const template = document.querySelector('.shipment-row');
        const newRow = template.cloneNode(true);
        
        // Clear input values and set box count to total boxes
        newRow.querySelector('.shipment-order-id').value = '';
        const totalBoxes = document.getElementById('totalBoxes').value || '1';
        newRow.querySelector('.shipment-box-count').value = totalBoxes;
        newRow.querySelector('.shipment-description').value = '';
        
        // Add event listener to remove button
        const removeButton = newRow.querySelector('.remove-shipment');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (document.querySelectorAll('.shipment-row').length > 1) {
                    newRow.remove();
                    updateRemoveButtonsVisibility();
                }
            });
        }
        
        container.appendChild(newRow);
        updateRemoveButtonsVisibility();
    }
    
    // Function to collect invoices data
    function getInvoicesData() {
        const invoices = [];
        document.querySelectorAll('.invoice-row').forEach(row => {
            const invNum = row.querySelector('.invoice-number').value;
            const invAmt = parseFloat(row.querySelector('.invoice-amount').value);
            const ewaybill = row.querySelector('.invoice-eway').value;
            
            if (invNum && !isNaN(invAmt)) {
                invoices.push({
                    inv_num: invNum,
                    inv_amt: invAmt,
                    ewaybill: ewaybill || ""
                });
            }
        });
        return invoices;
    }
    
    // Function to collect dimensions data
    function getDimensionsData() {
        const dimensions = [];
        document.querySelectorAll('.dimension-row').forEach(row => {
            const length = parseFloat(row.querySelector('.dimension-length').value);
            const width = parseFloat(row.querySelector('.dimension-width').value);
            const height = parseFloat(row.querySelector('.dimension-height').value);
            const boxCount = parseInt(row.querySelector('.dimension-box-count').value);
            
            // Get weight if the element exists, otherwise use a default of 0
            let weight = 0;
            const weightInput = row.querySelector('.dimension-weight');
            if (weightInput) {
                weight = parseFloat(weightInput.value);
            }
            
            // Check only the required fields (weight is now optional)
            if (!isNaN(length) && !isNaN(width) && !isNaN(height) && !isNaN(boxCount)) {
                dimensions.push({
                    length_cm: length,
                    width_cm: width,
                    height_cm: height,
                    weight_kg: weight || 0, // Use 0 if weight is NaN
                    box_count: boxCount
                });
            }
        });
        updateTotalBoxes();
        return dimensions;
    }
    
    // Function to collect shipment data
    function getShipmentData() {
        const shipments = [];
        document.querySelectorAll('.shipment-row').forEach(row => {
            const orderId = row.querySelector('.shipment-order-id').value;
            const boxCount = parseInt(row.querySelector('.shipment-box-count').value);
            const description = row.querySelector('.shipment-description').value;
            
            if (orderId && !isNaN(boxCount) && description) {
                shipments.push({
                    order_id: orderId,
                    box_count: boxCount,
                    description: description
                });
            }
        });
        return shipments;
    }
    
    // Function to get document data
    function getDocumentData() {
        const invoices = getInvoicesData();
        const invoiceNums = invoices.map(inv => inv.inv_num);
        
        return [
            {
                doc_type: "INVOICE_COPY",
                doc_meta: {
                    invoice_num: invoiceNums
                }
            }
        ];
    }
    
    // Function to validate form
    function validateForm() {
        // Check if pickup and drop locations are selected
        if (!selectedPickupLocation || !selectedDropLocation) {
            alert('Please select both pickup and drop locations.');
            return false;
        }
        
        // Check if billing address is fetched
        if (!billingAddressData) {
            alert('Please fetch billing address before creating manifest.');
            return false;
        }
        
        // Check if weight is entered
        const weight = document.getElementById('weightGrams').value;
        if (!weight || isNaN(parseInt(weight))) {
            alert('Please enter a valid weight in grams.');
            return false;
        }
        
        // Check if at least one invoice is entered
        const invoices = getInvoicesData();
        if (invoices.length === 0) {
            alert('Please enter at least one invoice.');
            return false;
        }
        
        // Check if at least one dimension is entered
        const dimensions = getDimensionsData();
        if (dimensions.length === 0) {
            alert('Please enter at least one dimension.');
            return false;
        }
        
        // Check if at least one shipment is entered
        const shipments = getShipmentData();
        if (shipments.length === 0) {
            alert('Please enter at least one shipment detail.');
            return false;
        }
        
        // Check if invoice PDF is uploaded
        const invoiceCopy = document.getElementById('invoiceCopy');
        if (!invoiceCopy.files || invoiceCopy.files.length === 0) {
            alert('Please upload an invoice copy PDF.');
            return false;
        }
        
        return true;
    }
    
    // Function to create manifest
    function createManifest() {
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Get form data
        const freightMode = document.querySelector('input[name="freightMode"]:checked').value;
        const paymentMode = document.querySelector('input[name="paymentMode"]:checked').value;
        const weight = parseInt(document.getElementById('weightGrams').value);
        
        // Get facility ID based on freight mode
        let facilityId = null;
        
        if (freightMode === 'fod' && selectedDropLocation) {
            facilityId = selectedDropLocation.id;
        } else if (freightMode === 'fop' && selectedPickupLocation) {
            facilityId = selectedPickupLocation.id;
        }
        
        // Extract the UUID part if it has the 'delhivery::clientwarehouse::' prefix
        if (facilityId && facilityId.includes('delhivery::clientwarehouse::')) {
            facilityId = facilityId.split('delhivery::clientwarehouse::')[1];
        }
        
        // Get address details from billing address data
        const addressDetails = billingAddressData.address_details || {};
        const billingDetails = billingAddressData.billing_details || {};
        const shippingAddress = billingAddressData.shipping_address || {};
        
        // Create manifest payload
        const manifestPayload = {
            pickup_location_name: selectedPickupLocation.name,
            dropoff_store_code: selectedDropLocation.name, // Changed from ID to name as per API requirement
            rov_insurance: false,
            fm_pickup: freightMode === 'fop',
            freight_mode: freightMode,
            billing_store_id: freightMode === 'fod' ? selectedDropLocation.id : selectedPickupLocation.id,
            // Set billing_warehouse_id based on freight mode
            billing_warehouse_id: null,
            // Add COD amount if applicable
            cod_amount: document.getElementById('codAmount') ? parseInt(document.getElementById('codAmount').value || 0) : 0,
            billing_address: {
                name: selectedDropLocation.name || 'Unknown', // store_code_name
                company: addressDetails.company || 'Unknown Company',
                consignor: addressDetails.contact_person || 'Unknown Consignor',
                address: addressDetails.address || 'Unknown Address',
                city: shippingAddress.city || 'Unknown City',
                state: shippingAddress.state || 'Unknown State',
                pin: (shippingAddress.pin_code || '000000').toString(),
                phone: addressDetails.phone_number || 'Unknown Phone',
                pan_number: billingDetails?.pan_number || null,
                gst_number: billingDetails?.gst_number || null
            },
            invoices: getInvoicesData(),
            dimensions: getDimensionsData(),
            weight_g: weight,
            shipment_details: getShipmentData(),
            payment_mode: paymentMode,
            doc_data: getDocumentData()
        };
        
        console.log('Manifest Payload:', manifestPayload);
        
        // Show loading message
        const statusElement = document.createElement('div');
        statusElement.className = 'alert alert-info mt-3';
        statusElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating manifest and uploading file...';
        
        const submitButton = document.getElementById('createManifestBtn');
        submitButton.disabled = true;
        submitButton.parentNode.appendChild(statusElement);
        
        // Get the invoice file
        const invoiceFile = document.getElementById('invoiceCopy').files[0];
        if (!invoiceFile) {
            statusElement.className = 'alert alert-danger mt-3';
            statusElement.innerHTML = 'Error: No invoice file selected.';
            submitButton.disabled = false;
            return;
        }
        
        // Create FormData object for the API request
        const formData = new FormData();
        formData.append('login_type', loginType);
        formData.append('manifest_payload', JSON.stringify(manifestPayload));
        formData.append('file', invoiceFile);
        
        // Use local proxy to avoid CORS issues
        console.log('Sending manifest data through proxy...');
        fetch('manifest-proxy.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Manifest API response:', data);
            
            console.log('Full API Response:', data);
            
            if (data.status === 'success' || data.success === true) {
                // Show success message
                statusElement.className = 'alert alert-success mt-3';
                statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Manifest created successfully!';
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = 'freight.html';
                }, 2000);
            } else {
                // Extract detailed error information
                let errorMessage = data.message || data.error || 'Unknown error';
                let detailedError = '';
                
                // Check if there's a nested response with more details
                if (data.response) {
                    try {
                        // If response is a string that contains JSON
                        if (typeof data.response === 'string') {
                            const parsedResponse = JSON.parse(data.response);
                            if (parsedResponse.message) {
                                detailedError = parsedResponse.message;
                            }
                        } else if (data.response.message) {
                            detailedError = data.response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing API response:', e);
                        detailedError = data.response;
                    }
                }
                
                // Show error message with details
                statusElement.className = 'alert alert-danger mt-3';
                statusElement.innerHTML = `
                    <div>
                        <i class="fas fa-exclamation-circle"></i> 
                        <strong>Error creating manifest:</strong> ${errorMessage}
                    </div>
                    ${detailedError ? `<div class="mt-2"><strong>Details:</strong> ${detailedError}</div>` : ''}
                    <div class="mt-2 small text-muted">Check browser console for complete response</div>
                `;
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error creating manifest:', error);
            
            // Show error message
            statusElement.className = 'alert alert-danger mt-3';
            statusElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error creating manifest: ' + 
                error.message;
            submitButton.disabled = false;
        });
    }
});
