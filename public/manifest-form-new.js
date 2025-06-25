// Manifest Form - Handles creation and submission of manifest forms
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedPickupLocation = null;
    let selectedDropLocation = null;
    let billingAddressData = null;
    
    // Get data from URL parameters (passed from the freight page)
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set carrier and rate information if available
    if (urlParams.has('carrier')) {
        let carrierName = urlParams.get('carrier');
        
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
        
        // Get login type from URL parameters or default to b2b
        const loginType = urlParams.get('loginType') || 'b2b';
        
        // Construct API URL with timestamp to prevent caching
        const timestamp = new Date().getTime();
        const apiUrl = 'http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=' + 
                      loginType + '&location_type=picking&search_term=&page=1&page_size=5&_=' + timestamp;
        
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
                        const returnAddress = location.return_address || {};
                        
                        // Create a normalized location data object that handles all potential structures
                        const locationData = {
                            id: location.facility_id || location.client_warehouse_uuid || location._id || 'UNKNOWN',
                            name: location.facility_name || returnAddress.facility_name || location.company_name || 'Unknown Location',
                            address: returnAddress.address_line1 || location.address_line1 || location.address || '',
                            city: returnAddress.city || location.city || '',
                            state: returnAddress.state || location.state || '',
                            pincode: returnAddress.pin_code || location.pin_code || location.pincode || '',
                            contact: returnAddress.contact_person || location.contact_person || '',
                            phone: returnAddress.phone || location.phone || '',
                            raw: location // Store raw data for debugging
                        };
                        
                        console.log('Extracted pickup location data:', locationData);
                        
                        const option = document.createElement('option');
                        option.value = locationData.id;
                        option.textContent = locationData.name + (locationData.city ? ' - ' + locationData.city : '');
                        option.textContent += locationData.state ? ', ' + locationData.state : '';
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
        
        // Get login type from URL parameters or default to b2b
        const loginType = urlParams.get('loginType') || 'b2b';
        
        // Construct API URL with timestamp to prevent caching
        const timestamp = new Date().getTime();
        const apiUrl = 'http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=' + 
                      loginType + '&location_type=drop&search_term=&page=1&page_size=10&_=' + timestamp;
        
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
                        const returnAddress = location.return_address || {};
                        
                        // Create a normalized location data object that handles all potential structures
                        const locationData = {
                            id: location.facility_id || location.client_warehouse_uuid || location._id || 'UNKNOWN',
                            name: location.facility_name || returnAddress.facility_name || location.company_name || 'Unknown Location',
                            address: returnAddress.address_line1 || location.address_line1 || location.address || '',
                            city: returnAddress.city || location.city || '',
                            state: returnAddress.state || location.state || '',
                            pincode: returnAddress.pin_code || location.pin_code || location.pincode || '',
                            contact: returnAddress.contact_person || location.contact_person || '',
                            phone: returnAddress.phone || location.phone || '',
                            raw: location // Store raw data for debugging
                        };
                        
                        console.log('Extracted drop location data:', locationData);
                        
                        const option = document.createElement('option');
                        option.value = locationData.id;
                        option.textContent = locationData.name + (locationData.city ? ' - ' + locationData.city : '');
                        option.textContent += locationData.state ? ', ' + locationData.state : '';
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
});
