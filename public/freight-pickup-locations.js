// Freight Pickup Locations Manager
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedPickupLocation = null;
    
    // Initialize the pickup location dropdown and handlers
    initPickupLocationSelector();
    
    // Function to initialize the pickup location selector
    function initPickupLocationSelector() {
        // First check if we're on the create-order page with pickup location section
        const pickupSection = document.querySelector('.card-header h5');
        if (!pickupSection || !pickupSection.textContent.includes('Pickup Location')) {
            return; // Not on the right page or section not found
        }
        
        // Find or create the pickup location dropdown
        let pickupRow = document.querySelector('#pickupLocationRow');
        if (!pickupRow) {
            // Create the row for pickup location dropdown if it doesn't exist
            const pickupCard = document.querySelector('.card-header:has(h5:contains("Pickup Location"))').closest('.card');
            if (pickupCard) {
                const cardBody = pickupCard.querySelector('.card-body');
                if (cardBody) {
                    // Create new row for the dropdown at the top of the card
                    pickupRow = document.createElement('div');
                    pickupRow.id = 'pickupLocationRow';
                    pickupRow.className = 'row g-3 mb-3';
                    pickupRow.innerHTML = `
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Select Pickup Location</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select id="pickupLocationSelect" class="form-select mb-2">
                                                <option value="">Loading pickup locations...</option>
                                            </select>
                                            <div id="pickupStatus" class="small mb-2"></div>
                                        </div>
                                    </div>
                                    <div id="pickupDetails" class="d-none">
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6>Location Details</h6>
                                                <div id="pickupLocationDetails"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Insert at the beginning of the card body
                    cardBody.insertBefore(pickupRow, cardBody.firstChild);
                    
                    // Load the pickup locations
                    loadPickupLocations();
                }
            }
        } else {
            // If row exists, just load the locations
            loadPickupLocations();
        }
    }
    
    // Function to load pickup locations from API
    function loadPickupLocations() {
        const pickupSelect = document.getElementById('pickupLocationSelect');
        const pickupStatus = document.getElementById('pickupStatus');
        
        if (!pickupSelect || !pickupStatus) return;
        
        // Clear existing options
        pickupSelect.innerHTML = '<option value="">Loading pickup locations...</option>';
        
        // Show loading message
        pickupStatus.innerHTML = `<div class="text-info">
            <i class="fas fa-spinner fa-spin"></i> Loading pickup locations...
        </div>`;
        
        // Get user email from the page if possible, otherwise use a default
        let userEmail = '';
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('user_id')) {
            userEmail = urlParams.get('user_id');
        } else {
            // Try to find an email input field that might contain the user's email
            const emailField = document.querySelector('input[type="email"]');
            if (emailField && emailField.value) {
                userEmail = emailField.value;
            } else {
                // Default email for testing
                userEmail = 'user@example.com';
            }
        }
        
        // Fetch pickup locations using the warehouse proxy with real-time data
        fetch(`warehouse-proxy.php?user_id=${encodeURIComponent(userEmail)}&t=${new Date().getTime()}`)
            .then(response => response.json())
            .then(data => {
                // Clear loading option
                pickupSelect.innerHTML = '<option value="">-- Select Pickup Location --</option>';
                
                let warehouses = [];
                
                // Check if we got warehouses from the response
                if (data.warehouses && Array.isArray(data.warehouses)) {
                    warehouses = data.warehouses;
                }
                
                if (warehouses.length > 0) {
                    // Add locations to dropdown
                    warehouses.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.id;
                        
                        // Standardize the location structure
                        const standardizedLocation = {
                            id: location.id,
                            name: location.name,
                            address: location.address,
                            city: location.city,
                            state: location.state,
                            pincode: location.zip || location.pincode, // Handle different field names
                            contact: location.contact,
                            phone: location.phone,
                            email: location.email,
                            is_default: location.is_default || false
                        };
                        
                        option.textContent = standardizedLocation.name + ' (' + standardizedLocation.city + ', ' + standardizedLocation.state + ')';
                        
                        // Store location data as a data attribute
                        option.dataset.location = JSON.stringify(standardizedLocation);
                        
                        pickupSelect.appendChild(option);
                    });
                    
                    // Show success message with indication if it's real or sample data
                    const dataSource = data.source === 'api' ? 'real-time' : 'sample';
                    const messageClass = data.source === 'api' ? 'text-success' : 'text-warning';
                    const icon = data.source === 'api' ? 'check-circle' : 'exclamation-triangle';
                    
                    pickupStatus.innerHTML = `<div class="${messageClass}">
                        <i class="fas fa-${icon}"></i> 
                        Loaded ${warehouses.length} pickup locations (${dataSource} data)
                    </div>`;
                    
                    // Select the first option and show its details
                    if (pickupSelect.options.length > 1) {
                        pickupSelect.selectedIndex = 1;
                        showPickupLocationDetails();
                    }
                    
                    // Add change handler for pickup location
                    pickupSelect.addEventListener('change', showPickupLocationDetails);
                } else {
                    // Show error message
                    pickupStatus.innerHTML = `<div class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i> 
                        No pickup locations found
                    </div>`;
                }
            })
            .catch(error => {
                console.error('Error loading pickup locations:', error);
                pickupSelect.innerHTML = '<option value="">-- Select Pickup Location --</option>';
                
                // Show error message
                pickupStatus.innerHTML = `<div class="text-danger">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Error loading pickup locations
                </div>`;
            });
    }
    
    // Function to show pickup location details
    function showPickupLocationDetails() {
        const pickupSelect = document.getElementById('pickupLocationSelect');
        const pickupDetails = document.getElementById('pickupDetails');
        const pickupLocationDetails = document.getElementById('pickupLocationDetails');
        
        // Also get the form fields we need to update
        const pickUpIdField = document.getElementById('pickUpId');
        const pickUpCityField = document.getElementById('pickUpCity');
        const pickUpStateField = document.getElementById('pickUpState');
        
        if (!pickupSelect || !pickupDetails || !pickupLocationDetails) return;
        
        if (pickupSelect.selectedIndex > 0) {
            const selectedOption = pickupSelect.options[pickupSelect.selectedIndex];
            try {
                const locationData = JSON.parse(selectedOption.dataset.location);
                
                // Store selected location
                selectedPickupLocation = locationData;
                
                // Show location details
                pickupDetails.classList.remove('d-none');
                pickupLocationDetails.innerHTML = `
                    <p class="mb-1"><strong>Location ID:</strong> ${locationData.id}</p>
                    <p class="mb-1"><strong>Name:</strong> ${locationData.name}</p>
                    <p class="mb-1"><strong>Address:</strong> ${locationData.address}</p>
                    <p class="mb-1"><strong>City:</strong> ${locationData.city}</p>
                    <p class="mb-1"><strong>State:</strong> ${locationData.state}</p>
                    <p class="mb-1"><strong>Pincode:</strong> ${locationData.pincode}</p>
                    ${locationData.contact ? `<p class="mb-1"><strong>Contact:</strong> ${locationData.contact}</p>` : ''}
                    ${locationData.phone ? `<p class="mb-1"><strong>Phone:</strong> ${locationData.phone}</p>` : ''}
                    ${locationData.is_default ? '<span class="badge bg-success">Default Location</span>' : ''}
                `;
                
                // Update the form fields
                if (pickUpIdField) pickUpIdField.value = locationData.id;
                if (pickUpCityField) pickUpCityField.value = locationData.city;
                if (pickUpStateField) pickUpStateField.value = locationData.state;
                
                // If "Same as Pickup" is checked for return location, update return fields
                const sameAsPickup = document.getElementById('sameAsPickup');
                if (sameAsPickup && sameAsPickup.checked) {
                    updateReturnLocation();
                }
            } catch (e) {
                console.error('Error parsing location data:', e);
                pickupDetails.classList.add('d-none');
                selectedPickupLocation = null;
            }
        } else {
            pickupDetails.classList.add('d-none');
            selectedPickupLocation = null;
        }
    }
    
    // Function to update return location fields
    function updateReturnLocation() {
        if (!selectedPickupLocation) return;
        
        // Get return location fields
        const retrunIdField = document.getElementById('retrunId');
        const returnCityField = document.getElementById('returnCity');
        const returnStateField = document.getElementById('returnState');
        
        // Update return location fields with pickup location data
        if (retrunIdField) retrunIdField.value = selectedPickupLocation.id;
        if (returnCityField) returnCityField.value = selectedPickupLocation.city;
        if (returnStateField) returnStateField.value = selectedPickupLocation.state;
    }
    
    // Handle "Same as Pickup" checkbox
    const sameAsPickup = document.getElementById('sameAsPickup');
    if (sameAsPickup) {
        sameAsPickup.addEventListener('change', function() {
            if (this.checked && selectedPickupLocation) {
                updateReturnLocation();
            }
        });
    }
});
