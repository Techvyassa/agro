// Script to handle box count from URL parameter to shipment details section
document.addEventListener('DOMContentLoaded', function() {
    console.log('Shipment box handler loaded');
    
    // Extract URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Get the boxes parameter
    const boxCount = urlParams.get('boxes');
    console.log('Box Count from URL:', boxCount);
    
    if (boxCount) {
        // Apply the box count to the Shipment Details section
        function updateBoxCount() {
            console.log('Attempting to update box count...');
            
            // Approach 1: Find by section title and position
            const shipmentSections = document.querySelectorAll('.card-header h5');
            shipmentSections.forEach(section => {
                if (section.textContent.includes('Shipment Details')) {
                    const card = section.closest('.card');
                    if (card) {
                        const inputs = card.querySelectorAll('input[type="number"]');
                        inputs.forEach((input, index) => {
                            // Get the field label or previous cell
                            const label = input.previousElementSibling;
                            const cell = input.closest('td');
                            const prevCell = cell ? cell.previousElementSibling : null;
                            
                            if ((label && label.textContent.includes('Box Count')) || 
                                (prevCell && prevCell.textContent.includes('Box Count'))) {
                                input.value = boxCount;
                                console.log('Set Box Count field to:', boxCount);
                            }
                        });
                    }
                }
            });
            
            // Approach 2: Direct targeting based on the DOM structure visible in screenshot
            const shipmentDetailsCard = document.querySelector('.card:has(h5:contains("Shipment Details"))') || 
                                        document.querySelector('div.card:nth-child(5)');
            
            if (shipmentDetailsCard) {
                const boxCountInputs = shipmentDetailsCard.querySelectorAll('input[type="number"]');
                // The box count is typically the second number input in the shipment details section
                if (boxCountInputs.length > 0) {
                    // Try to identify which input is for box count
                    boxCountInputs.forEach(input => {
                        const row = input.closest('tr');
                        const headerRow = row ? row.previousElementSibling : null;
                        
                        if (headerRow && headerRow.textContent.includes('Box Count')) {
                            input.value = boxCount;
                            console.log('Set Box Count by row header to:', boxCount);
                        } else if (input.parentElement && input.parentElement.previousElementSibling && 
                                   input.parentElement.previousElementSibling.textContent.includes('Box Count')) {
                            input.value = boxCount;
                            console.log('Set Box Count by previous element to:', boxCount);
                        }
                    });
                    
                    // If we couldn't identify by labels, just set the second input
                    // (common pattern for shipment details: Order ID, Box Count, Description)
                    if (boxCountInputs.length >= 2) {
                        boxCountInputs[1].value = boxCount;
                        console.log('Set second number input to Box Count:', boxCount);
                    }
                }
            }
            
            // Approach 3: Find based on adjacent text content
            const allRows = document.querySelectorAll('tr, div.row');
            allRows.forEach(row => {
                const boxLabel = Array.from(row.children).find(cell => 
                    cell.textContent.trim() === 'Box Count');
                
                if (boxLabel) {
                    const nextCell = boxLabel.nextElementSibling;
                    if (nextCell) {
                        const input = nextCell.querySelector('input[type="number"]');
                        if (input) {
                            input.value = boxCount;
                            console.log('Set Box Count by adjacent label to:', boxCount);
                        }
                    }
                }
            });
            
            // Direct approach for the specific structure seen in the screenshot
            const boxCountInput = document.querySelector('input[value="1"][type="number"]:not([placeholder*="Length"]):not([placeholder*="Width"]):not([placeholder*="Height"])');
            if (boxCountInput) {
                boxCountInput.value = boxCount;
                console.log('Set Box Count using direct selector:', boxCount);
            }
        }
        
        // Try immediately
        updateBoxCount();
        
        // And also try after a short delay to ensure DOM is fully loaded
        setTimeout(updateBoxCount, 500);
        
        // And one more time after a longer delay for any async DOM changes
        setTimeout(updateBoxCount, 1500);
    }
});
