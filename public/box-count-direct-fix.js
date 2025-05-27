// Direct fix for box count in Shipment Details section
document.addEventListener('DOMContentLoaded', function() {
    // This script runs after the page loads
    console.log('Box Count Direct Fix loaded');
    
    // Function to set the box count directly from URL parameter
    function setBoxCountFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const boxCount = urlParams.get('boxes');
        
        if (boxCount) {
            console.log('Found boxes parameter in URL:', boxCount);
            
            // Target specifically the Box Count input in the Shipment Details section
            // Based on the screenshot structure
            const shipmentDetails = Array.from(document.querySelectorAll('.card-header h5'))
                .find(h => h.textContent.trim() === 'Shipment Details');
                
            if (shipmentDetails) {
                const card = shipmentDetails.closest('.card');
                if (card) {
                    // Get all inputs in this card
                    const inputs = card.querySelectorAll('input[type="number"]');
                    
                    // In the observed structure, the second input is the Box Count field
                    if (inputs.length >= 2) {
                        inputs[1].value = boxCount;
                        console.log('Direct fix: Set Box Count to', boxCount);
                    }
                }
            }
        }
    }
    
    // Try to set the box count immediately
    setBoxCountFromUrl();
    
    // And also after a delay to ensure DOM is fully loaded
    setTimeout(setBoxCountFromUrl, 1000);
});
