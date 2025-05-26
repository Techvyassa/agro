// Manifest Creator - Handles creation of delivery manifests
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedPickupLocation = null;
    let selectedDropLocation = null;
    
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
    
    // Set up event listeners for dropdown changes
    document.getElementById('pickupLocation').addEventListener('change', function() {
        showLocationDetails('pickup');
    });
    
    document.getElementById('dropLocation').addEventListener('change', function() {
        showLocationDetails('drop');
    });
    
    // Set up event listeners for load location buttons
    document.getElementById('loadPickupLocationsBtn').addEventListener('click', function() {
        loadPickupLocations(true); // true = force reload
    });
    
    document.getElementById('loadDropLocationsBtn').addEventListener('click', function() {
        loadDropLocations(true); // true = force reload
    });
    
    // Set up event listener for create manifest button
    document.getElementById('createManifestBtn').addEventListener('click', createManifest);
    
    // Initial load of pickup and drop locations
    loadPickupLocations();
    loadDropLocations();
    
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
        const apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=' + 
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
                            id: location.facility_id || 'UNKNOWN',
                            name: location.facility_name || returnAddress.facility_name || location.company_name || 'Unknown Location',
                            address: returnAddress.address_line1 || location.address_line1 || location.address || '',
                            city: returnAddress.city || location.city || '',
                            state: returnAddress.state || location.state || '',
                            pincode: returnAddress.pin_code || location.pin_code || location.pincode || '',
                            contact: returnAddress.contact_person || location.contact_person || '',
                            phone: returnAddress.phone || location.phone || '',
                            gst: location.gst_number || '',
                            company_name: returnAddress.company_name || location.company_name || '',
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
        const apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=' + 
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
                            id: location.facility_id || 'UNKNOWN',
                            name: location.facility_name || returnAddress.facility_name || location.company_name || 'Unknown Location',
                            address: returnAddress.address_line1 || location.address_line1 || location.address || '',
                            city: returnAddress.city || location.city || '',
                            state: returnAddress.state || location.state || '',
                            pincode: returnAddress.pin_code || location.pin_code || location.pincode || '',
                            contact: returnAddress.contact_person || location.contact_person || '',
                            phone: returnAddress.phone || location.phone || '',
                            gst: location.gst_number || '',
                            company_name: returnAddress.company_name || location.company_name || '',
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
                phone: "9876543210",
                is_default: true
            },
            {
                id: "MUM001",
                name: "Mumbai Distribution Center",
                address: "456 MIDC, Andheri East",
                city: "Mumbai",
                state: "Maharashtra",
                pincode: "400093",
                contact: "Operations Head",
                phone: "9876543211",
                is_default: false
            },
            {
                id: "BLR001",
                name: "Bangalore Logistics Hub",
                address: "789 Electronic City",
                city: "Bangalore",
                state: "Karnataka",
                pincode: "560100",
                contact: "Facility Manager",
                phone: "9876543212",
                is_default: false
            }
        ];
        
        // Add locations to dropdown
        fallbackLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.name + ' - ' + location.city + ', ' + location.state;
            option.dataset.location = JSON.stringify(location);
            
            // Mark default location if applicable
            if (location.is_default) {
                option.textContent += ' (Default)';
            }
            
            pickupSelect.appendChild(option);
        });
        
        // Select default location if exists
        const defaultOption = Array.from(pickupSelect.options).find(option => {
            try {
                const locationData = JSON.parse(option.dataset.location || '{}');
                return locationData.is_default === true;
            } catch (e) {
                return false;
            }
        });
        
        if (defaultOption) {
            defaultOption.selected = true;
            showLocationDetails('pickup');
        }
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
                phone: "9876543213",
                is_default: true
            },
            {
                id: "KOL001",
                name: "Kolkata Warehouse",
                address: "202 Salt Lake City",
                city: "Kolkata",
                state: "West Bengal",
                pincode: "700091",
                contact: "Operations Head",
                phone: "9876543214",
                is_default: false
            },
            {
                id: "HYD001",
                name: "Hyderabad Logistics Center",
                address: "303 Hi-Tech City",
                city: "Hyderabad",
                state: "Telangana",
                pincode: "500081",
                contact: "Warehouse Manager",
                phone: "9876543215",
                is_default: false
            },
            {
                id: "PUN001",
                name: "Pune Distribution Center",
                address: "404 Hinjewadi Phase 2",
                city: "Pune",
                state: "Maharashtra",
                pincode: "411057",
                contact: "Operations Executive",
                phone: "9876543216",
                is_default: false
            }
        ];
        
        // Add locations to dropdown
        fallbackLocations.forEach(location => {
            const option = document.createElement('option');
            option.value = location.id;
            option.textContent = location.name + ' - ' + location.city + ', ' + location.state;
            option.dataset.location = JSON.stringify(location);
            
            // Mark default location if applicable
            if (location.is_default) {
                option.textContent += ' (Default)';
            }
            
            dropSelect.appendChild(option);
        });
        
        // Select default location if exists
        const defaultOption = Array.from(dropSelect.options).find(option => {
            try {
                const locationData = JSON.parse(option.dataset.location || '{}');
                return locationData.is_default === true;
            } catch (e) {
                return false;
            }
        });
        
        if (defaultOption) {
            defaultOption.selected = true;
            showLocationDetails('drop');
        }
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
                
                // Prepare display values - use raw data if available for the most accurate info
                const rawData = locationData.raw || {};
                const returnAddress = rawData.return_address || {};
                
                // Get the most accurate data possible
                const id = locationData.id || 'N/A';
                const name = locationData.name || 'Unknown Location';
                const address = (rawData.address_line1 || returnAddress.address_line1 || locationData.address || '').replace('[object Object]', '');
                const city = rawData.city || returnAddress.city || locationData.city || 'N/A';
                const state = rawData.state || returnAddress.state || locationData.state || 'N/A';
                const pincode = rawData.pin_code || returnAddress.pin_code || locationData.pincode || 'N/A';
                const contact = rawData.contact_person || returnAddress.contact_person || locationData.contact || 'N/A';
                const phone = rawData.phone || returnAddress.phone || locationData.phone || 'N/A';
                const gst = rawData.gst_number || locationData.gst || 'N/A';
                
                // Show location details
                details.classList.remove('d-none');
                locationDetails.innerHTML = `
                    <p class="mb-1"><strong>Location ID:</strong> ${id}</p>
                    <p class="mb-1"><strong>Name:</strong> ${name}</p>
                    <p class="mb-1"><strong>Address:</strong> ${address}</p>
                    <p class="mb-1"><strong>City:</strong> ${city}</p>
                    <p class="mb-1"><strong>State:</strong> ${state}</p>
                    <p class="mb-1"><strong>Pincode:</strong> ${pincode}</p>
                    <p class="mb-1"><strong>Contact:</strong> ${contact}</p>
                    <p class="mb-1"><strong>Phone:</strong> ${phone}</p>
                    ${gst !== 'N/A' ? `<p class="mb-1"><strong>GST Number:</strong> ${gst}</p>` : ''}
                    <div class="mt-2">
                        <span class="badge bg-info">API: Delhivery Warehouse ${locationType === 'pickup' ? 'Picking' : 'Drop'} Location</span>
                    </div>
                `;
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
        
        // Enable/disable the create manifest button based on selections
        enableCreateButton();
    }
    
    // Function to enable/disable create manifest button
    function enableCreateButton() {
        const createButton = document.getElementById('createManifestBtn');
        if (!createButton) return;
        
        if (selectedPickupLocation && selectedDropLocation) {
            createButton.disabled = false;
        } else {
            createButton.disabled = true;
        }
    }
    
    // Function to create manifest
    function createManifest() {
        // Validate selections
        if (!selectedPickupLocation || !selectedDropLocation) {
            alert('Please select both pickup and drop locations before creating a manifest.');
            return;
        }
        
        // Get carrier info
        const carrierName = document.getElementById('carrierName').textContent;
        const freightRate = document.getElementById('freightRate').textContent;
        
        // Build manifest data
        const manifestData = {
            carrier: carrierName,
            rate: freightRate,
            pickup_location: selectedPickupLocation,
            drop_location: selectedDropLocation,
            created_at: new Date().toISOString(),
            source_pincode: document.getElementById('source-pincode').value,
            destination_pincode: document.getElementById('destination-pincode').value
        };
        
        // In a real application, you would send this data to a server endpoint
        console.log('Creating manifest with data:', manifestData);
        
        // For now, just show an alert
        alert('Manifest created successfully!\n\nPickup: ' + selectedPickupLocation.name + 
              '\nDrop: ' + selectedDropLocation.name);
        
        // Redirect back to freight page
        window.location.href = 'freight.html';
    }
    
    // Initialize - disable create button until both locations are selected
    document.getElementById('createManifestBtn').disabled = true;
});
