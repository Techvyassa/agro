// Script to handle dimensions data passed from freight page to manifest page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dimensions handler loaded');
    console.log('Current URL:', window.location.href);
    console.log('URL parameters:', window.location.search);
    
    // Extract URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Log all URL parameters for debugging
    urlParams.forEach((value, key) => {
        console.log(`URL param: ${key} = ${value}`);
    });
    
    // Get the dimensions, boxes and weight parameters using new format
    const dimensions = urlParams.get('dims');
    const boxCount = urlParams.get('boxes');
    const totalWeight = urlParams.get('weight');
    
    console.log('Dimensions from URL:', dimensions);
    console.log('Box Count from URL:', boxCount);
    console.log('Total Weight from URL:', totalWeight);
    
    // Store in hidden fields
    if (boxCount) {
        document.getElementById('total-boxes').value = boxCount;
        console.log('Set total-boxes hidden field to:', boxCount);
        
        // Also update box count field in the dimensions section if it exists
        const boxCountInput = document.querySelector('.dimension-box-count, input[placeholder="Box Count"]');
        if (boxCountInput) {
            boxCountInput.value = boxCount;
            console.log('Set box count input to:', boxCount);
        }
    }
    
    if (totalWeight) {
        document.getElementById('total-weight').value = totalWeight;
        console.log('Set total-weight hidden field to:', totalWeight);
        
        // Find and set the weight field
        // Try both weightGrams and direct by type and position
        const weightInput = document.getElementById('weightGrams');
        if (weightInput) {
            // Convert kg to grams
            const weightInGrams = parseFloat(totalWeight) * 1000;
            weightInput.value = weightInGrams;
            console.log('Set weightGrams input to:', weightInGrams);
        } else {
            // Try to find by section heading and input type
            const weightSection = document.querySelector('.card-header h5');
            if (weightSection && weightSection.textContent.trim() === 'Weight') {
                const section = weightSection.closest('.card');
                if (section) {
                    const weightInput = section.querySelector('input[type="number"]');
                    if (weightInput) {
                        // Convert kg to grams
                        const weightInGrams = parseFloat(totalWeight) * 1000;
                        weightInput.value = weightInGrams;
                        console.log('Set weight input by section to:', weightInGrams);
                    } else {
                        console.error('Weight input not found in weight section');
                    }
                }
            } else {
                // Last resort - try to find any input with placeholder or label containing 'gram'
                const weightInputs = document.querySelectorAll('input[type="number"]');
                let found = false;
                
                weightInputs.forEach(input => {
                    const label = input.previousElementSibling;
                    if ((label && label.textContent.toLowerCase().includes('gram')) ||
                        (input.placeholder && input.placeholder.toLowerCase().includes('gram'))) {
                        // Convert kg to grams
                        const weightInGrams = parseFloat(totalWeight) * 1000;
                        input.value = weightInGrams;
                        found = true;
                        console.log('Set weight input by label/placeholder to:', weightInGrams);
                    }
                });
                
                if (!found) {
                    console.error('Could not find weight input field');
                }
            }
        }
    }
    
    // If dimensions parameter exists, parse and populate the dimensions section
    if (dimensions) {
        try {
            // Store raw dimensions data
            document.getElementById('dimensions-data').textContent = dimensions;
            
            // Parse dimensions (format: L-W-H-W|L-W-H-W|...)
            // Where L=length, W=width, H=height, W=weight
            const dimensionSets = dimensions.split('|');
            
            // Get dimensions container
            const dimensionsContainer = document.getElementById('dimensionsContainer');
            if (!dimensionsContainer) {
                console.error('Dimensions container not found');
                return;
            }
            
            // Clear existing dimensions (except the first template row)
            const existingRows = dimensionsContainer.querySelectorAll('.dimension-row');
            for (let i = 1; i < existingRows.length; i++) {
                existingRows[i].remove();
            }
            
            // Debug the available dimension fields
            console.log('Looking for dimension inputs in the template row');
            const allInputs = dimensionsContainer.querySelectorAll('input');
            console.log(`Found ${allInputs.length} input fields in dimensionsContainer:`);
            allInputs.forEach((input, i) => {
                console.log(`Input ${i}:`, input.className, input.type);
            });
            
            // Get the first dimension row as template
            const templateRow = dimensionsContainer.querySelector('.dimension-row');
            if (!templateRow) {
                console.error('Dimension row template not found');
                return;
            }
            
            console.log('Template row found:', templateRow);
            
            // Fill in the first row and add additional rows for each dimension set
            dimensionSets.forEach((dimSet, index) => {
                // Parse dimension set (format: L-W-H-W)
                const parts = dimSet.split('-');
                if (parts.length < 3) {
                    console.error('Invalid dimension format:', dimSet);
                    return;
                }
                
                // Get length, width, height, and weight/count
                const length = parts[0];
                const width = parts[1];
                const height = parts[2];
                const count = parts.length > 3 ? parts[3] : 1; // Use weight as count if available
                
                console.log(`Processing dimension set ${index}:`, length, width, height, count);
                
                // If it's the first set, update the template row
                if (index === 0) {
                    // Find input fields by their position in the row rather than class names
                    const inputs = templateRow.querySelectorAll('input[type="number"]');
                    if (inputs.length >= 4) {
                        // Assuming the order is: length, width, height, box count
                        inputs[0].value = length;
                        inputs[1].value = width;
                        inputs[2].value = height;
                        
                        // For the box count, we use the one from dimensions if available,
                        // or the total boxCount if not
                        if (count && count !== '1') {
                            inputs[3].value = count;
                            console.log('Set box count from dimension data:', count);
                        } else if (boxCount && index === 0) {
                            // Only set from boxCount for the first row if no specific count
                            inputs[3].value = boxCount;
                            console.log('Set box count from URL parameter:', boxCount);
                        }
                        
                        console.log('Updated first row with values:', length, width, height, inputs[3].value);
                    } else {
                        console.error('Could not find all inputs in the first row', inputs.length);
                    }
                } 
                // For additional sets, clone the template and append
                else {
                    // Clone the template
                    const newRow = templateRow.cloneNode(true);
                    
                    // Find input fields by their position in the row
                    const inputs = newRow.querySelectorAll('input[type="number"]');
                    if (inputs.length >= 4) {
                        // Assuming the order is: length, width, height, box count
                        inputs[0].value = length;
                        inputs[1].value = width;
                        inputs[2].value = height;
                        
                        // For box count, use dimension-specific count if available
                        if (count && count !== '1') {
                            inputs[3].value = count;
                            console.log(`Set box count for row ${index} from dimension data:`, count);
                        } else if (boxCount && dimensionSets.length === 1) {
                            // If there's only one dimension set but multiple boxes, use the total
                            inputs[3].value = boxCount;
                            console.log(`Set box count for row ${index} from URL parameter:`, boxCount);
                        } else {
                            // Default to 1 if no specific count
                            inputs[3].value = 1;
                        }
                        
                        console.log(`Added new row ${index} with values:`, length, width, height, inputs[3].value);
                    }
                    
                    // Show the remove button for additional rows
                    const removeButton = newRow.querySelector('.remove-dimension');
                    if (removeButton) {
                        removeButton.style.display = 'block';
                        
                        // Add click handler to remove button
                        removeButton.addEventListener('click', function() {
                            newRow.remove();
                        });
                    }
                    
                    // Append to container
                    dimensionsContainer.appendChild(newRow);
                }
            });
            
            console.log('Dimensions populated successfully');
        } catch (error) {
            console.error('Error processing dimensions:', error);
        }
    }
});
