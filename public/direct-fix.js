// Direct fix for the drop location dropdown issue
document.addEventListener('DOMContentLoaded', function() {
    // Fix the drop location dropdown after page load
    setTimeout(fixDropLocations, 500);
    
    // Also add a click handler to the refresh button
    const refreshButton = document.querySelector('#dropLocation + .btn-primary');
    if (refreshButton) {
        refreshButton.addEventListener('click', fixDropLocations);
        console.log('Added click handler to drop location refresh button');
    }
});

// Function to fix drop location dropdown
function fixDropLocations() {
    console.log('Applying direct drop location fix');
    
    // Get the dropdown element
    const dropSelect = document.getElementById('dropLocation');
    if (!dropSelect) {
        console.error('Drop location dropdown not found');
        return;
    }
    
    // Get status element
    const dropStatus = document.getElementById('dropStatus');
    
    // Show loading
    dropSelect.innerHTML = '<option value="">Loading locations...</option>';
    
    // Create the correct URL
    const apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=b2brc&location_type=drop&search_term=&page=1&page_size=100';
    
    // Log the URL we're using
    console.log('Fetching drop locations from:', apiUrl);
    
    // Fetch through proxy
    fetch('location-proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ url: apiUrl })
    })
    .then(response => response.json())
    .then(data => {
        // Log the raw response
        console.log('API response:', data);
        
        // Reset dropdown with default option
        dropSelect.innerHTML = '<option value="">Select a drop location</option>';
        
        // Process the response based on its structure
        let locations = [];
        
        // Try to extract locations from the response
        try {
            // If success is true and data exists
            if (data && data.success === true && data.data) {
                // If data is a string, parse it
                if (typeof data.data === 'string') {
                    const parsedData = JSON.parse(data.data);
                    if (parsedData.results && Array.isArray(parsedData.results)) {
                        locations = parsedData.results;
                    }
                }
                // If data is an object
                else if (data.data.results && Array.isArray(data.data.results)) {
                    locations = data.data.results;
                }
            }
            // Direct results array
            else if (data && data.results && Array.isArray(data.results)) {
                locations = data.results;
            }
        } catch (error) {
            console.error('Error processing API response:', error);
        }
        
        console.log(`Found ${locations.length} drop locations`);
        
        // Add locations to dropdown
        if (locations.length > 0) {
            locations.forEach((location, index) => {
                try {
                    // Get key information
                    const name = location.store_code_name || `Location ${index + 1}`;
                    const facilityId = location.facility_id || '';
                    const address = location.address || {};
                    const city = address.city || '';
                    const state = address.state || '';
                    
                    // Create option
                    const option = document.createElement('option');
                    option.value = facilityId;
                    
                    // Set display text
                    const displayText = name + 
                        (city ? ` - ${city}` : '') + 
                        (state ? `, ${state}` : '');
                    option.textContent = displayText;
                    
                    // Store location data
                    option.dataset.location = JSON.stringify({
                        id: facilityId,
                        name: name,
                        address: address.address_line1 || '',
                        city: city,
                        state: state,
                        pincode: address.pin_code || '',
                        phone: address.phone || ''
                    });
                    
                    // Add to dropdown
                    dropSelect.appendChild(option);
                    console.log(`Added location: ${displayText}`);
                } catch (error) {
                    console.error('Error adding location to dropdown:', error);
                }
            });
            
            // Show success message
            if (dropStatus) {
                dropStatus.innerHTML = `<span class="text-success">
                    <i class="fas fa-check-circle"></i> 
                    Loaded ${locations.length} drop locations
                    <span class="badge bg-success ms-1">API Data</span>
                </span>`;
            }
        } else {
            // No locations found
            // Add a demo location for testing
            const option = document.createElement('option');
            option.value = "demo";
            option.textContent = "Demo Location - Delhi, IN";
            dropSelect.appendChild(option);
            
            console.log('No locations found in API response, added demo location');
            
            // Show warning
            if (dropStatus) {
                dropStatus.innerHTML = `<span class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No locations found in API. Using demo data.
                </span>`;
            }
        }
    })
    .catch(error => {
        console.error('Error fetching drop locations:', error);
        
        // Show error
        if (dropStatus) {
            dropStatus.innerHTML = `<span class="text-danger">
                <i class="fas fa-exclamation-circle"></i> 
                Error: ${error.message}
            </span>`;
        }
        
        // Add a fallback option
        dropSelect.innerHTML = '<option value="">Select a drop location</option>';
        const option = document.createElement('option');
        option.value = "fallback";
        option.textContent = "Fallback Location - Delhi, IN";
        dropSelect.appendChild(option);
    });
}
