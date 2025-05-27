// Immediate fix for drop locations dropdown
function fixDropLocations() {
    console.log('Applying direct fix for drop locations');
    
    // Get the dropdown element
    const dropSelect = document.getElementById('dropLocation');
    if (!dropSelect) {
        console.error('Drop location dropdown not found');
        return;
    }
    
    // Clear existing options and show loading
    dropSelect.innerHTML = '<option value="">Loading drop locations...</option>';
    
    // Create the API URL for Delhivery drop locations with the correct login_type
    const apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=b2brc&location_type=drop&search_term=&page=1&page_size=100';
    
    // Fetch drop locations through our proxy
    fetch('location-proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ url: apiUrl })
    })
    .then(response => response.json())
    .then(apiResponse => {
        // Log the full response for debugging
        console.log('Drop Locations API Response:', apiResponse);
        
        // Reset dropdown with default option
        dropSelect.innerHTML = '<option value="">Select a drop location</option>';
        
        // Extract locations from results array
        const locations = apiResponse.results || [];
        
        if (locations.length > 0) {
            console.log(`Found ${locations.length} drop locations`);
            
            // Loop through each location and add to dropdown
            locations.forEach(location => {
                // Get the store name (primary display name)
                const storeName = location.store_code_name;
                
                // Get facility ID (dropdown value)
                const facilityId = location.facility_id;
                
                // Get address details
                const address = location.address || {};
                const city = address.city || '';
                const state = address.state || '';
                
                // Create display text: STORE NAME - CITY, STATE
                const displayText = storeName + 
                    (city ? ` - ${city}` : '') + 
                    (state ? `, ${state}` : '');
                
                // Create and add option to dropdown
                const option = document.createElement('option');
                option.value = facilityId;
                option.textContent = displayText;
                
                // Store full location data for reference
                option.dataset.location = JSON.stringify({
                    id: facilityId,
                    name: storeName,
                    address: address.address_line1 || '',
                    city: city,
                    state: state,
                    pincode: address.pin_code || '',
                    phone: address.phone || ''
                });
                
                // Add to dropdown
                dropSelect.appendChild(option);
                console.log(`Added drop location: ${displayText}`);
            });
            
            // Show success message
            const statusElement = document.getElementById('dropStatus');
            if (statusElement) {
                statusElement.innerHTML = `<span class="text-success">
                    <i class="fas fa-check-circle"></i> 
                    Loaded ${locations.length} drop locations
                    <span class="badge bg-success ms-1">API Data</span>
                </span>`;
            }
        } else {
            console.error('No drop locations found in API response');
            
            // Show error message
            const statusElement = document.getElementById('dropStatus');
            if (statusElement) {
                statusElement.innerHTML = `<span class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No drop locations found. Please try again.
                </span>`;
            }
        }
    })
    .catch(error => {
        console.error('Error fetching drop locations:', error);
        
        // Show error message
        const statusElement = document.getElementById('dropStatus');
        if (statusElement) {
            statusElement.innerHTML = `<span class="text-danger">
                <i class="fas fa-exclamation-circle"></i> 
                Error loading drop locations: ${error.message}
            </span>`;
        }
    });
}

// Call the fix function when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Fix drop locations after a short delay to ensure all elements are loaded
    setTimeout(fixDropLocations, 500);
});

// Also add a click handler to the "Load Locations" button
document.addEventListener('DOMContentLoaded', function() {
    const loadButton = document.querySelector('#dropLocation').nextElementSibling;
    if (loadButton && loadButton.classList.contains('btn-primary')) {
        loadButton.addEventListener('click', fixDropLocations);
    }
});
