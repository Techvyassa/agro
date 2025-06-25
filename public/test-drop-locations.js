// Direct script to test drop location dropdown population
document.addEventListener('DOMContentLoaded', function() {
    // Get the drop location dropdown
    const dropSelect = document.getElementById('dropLocation');
    
    if (!dropSelect) {
        console.error('Drop location dropdown not found');
        return;
    }
    
    // Clear dropdown and add loading message
    dropSelect.innerHTML = '<option value="">Loading drop locations...</option>';
    
    // Construct API URL
    const apiUrl = 'http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/delhivery-warehouses?login_type=b2b&location_type=drop&page=1&page_size=100';
    
    console.log('Fetching drop locations from:', apiUrl);
    
    // Using location-proxy.php to handle CORS
    fetch('location-proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ url: apiUrl })
    })
    .then(response => response.json())
    .then(data => {
        console.log('API Response:', data);
        
        // Clear dropdown and add default option
        dropSelect.innerHTML = '<option value="">Select a drop location</option>';
        
        // Extract locations from the response
        if (data && data.results && Array.isArray(data.results)) {
            const locations = data.results;
            console.log(`Found ${locations.length} drop locations`);
            
            // Process each location
            locations.forEach((location, index) => {
                // Get location details
                const storeName = location.store_code_name || `Location ${index + 1}`;
                const facilityId = location.facility_id || '';
                const address = location.address || {};
                const city = address.city || '';
                const state = address.state || '';
                
                // Create option element
                const option = document.createElement('option');
                option.value = facilityId;
                
                // Set display text
                option.textContent = `${storeName} - ${city}, ${state}`;
                
                // Add to dropdown
                dropSelect.appendChild(option);
                console.log(`Added location: ${option.textContent}`);
            });
            
            // Add a message to show success
            const statusElement = document.getElementById('dropStatus');
            if (statusElement) {
                statusElement.innerHTML = `<span class="text-success">
                    <i class="fas fa-check-circle"></i> 
                    Loaded ${locations.length} drop locations
                </span>`;
            }
        } else {
            console.error('No locations found in API response');
        }
    })
    .catch(error => {
        console.error('Error fetching drop locations:', error);
    });
});
