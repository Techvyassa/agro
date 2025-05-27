// Simple data transfer script to ensure dimensions and weight are passed between pages
(function() {
    // Check if we're on the freight page
    if (window.location.href.includes('freight.html')) {
        console.log('Data transfer script loaded on freight page');
        
        // Add event listeners to all relevant buttons that might redirect to create-manifest
        document.addEventListener('click', function(event) {
            // Check if a carrier selection button was clicked
            if (event.target.classList.contains('select-carrier-btn') || 
                event.target.closest('.select-carrier-btn')) {
                
                console.log('Carrier selection detected, saving dimensions and weight data');
                
                // Collect dimensions data
                const dimensionData = {
                    dimensions: [],
                    totalBoxes: 0,
                    totalWeight: 0
                };
                
                // Get all dimension rows
                const dimensionRows = document.querySelectorAll('.dimension-row');
                dimensionRows.forEach(row => {
                    const inputs = row.querySelectorAll('input[type="number"]');
                    if (inputs.length >= 3) {
                        const length = inputs[0].value || '';
                        const width = inputs[1].value || '';
                        const height = inputs[2].value || '';
                        const boxCount = (inputs.length > 3) ? (inputs[3].value || '1') : '1';
                        
                        if (length && width && height) {
                            dimensionData.dimensions.push({
                                length: length,
                                width: width,
                                height: height,
                                boxCount: boxCount
                            });
                            
                            dimensionData.totalBoxes += parseInt(boxCount);
                        }
                    }
                });
                
                // Get total weight
                const weightDisplay = document.getElementById('totalWeightDisplay');
                if (weightDisplay) {
                    const weightText = weightDisplay.textContent;
                    const weightValue = parseFloat(weightText.replace(/[^0-9.]/g, ''));
                    dimensionData.totalWeight = weightValue;
                }
                
                // Store data in localStorage
                localStorage.setItem('freightDimensionData', JSON.stringify(dimensionData));
                console.log('Saved dimension data:', dimensionData);
            }
        });
    }
    
    // Check if we're on the create-manifest page
    if (window.location.href.includes('create-manifest.html')) {
        console.log('Data transfer script loaded on create-manifest page');
        
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get stored data
            const storedDataString = localStorage.getItem('freightDimensionData');
            if (storedDataString) {
                try {
                    const storedData = JSON.parse(storedDataString);
                    console.log('Retrieved stored dimension data:', storedData);
                    
                    // Apply dimensions
                    if (storedData.dimensions && storedData.dimensions.length > 0) {
                        const dimensionsContainer = document.getElementById('dimensionsContainer');
                        if (dimensionsContainer) {
                            // Get template row
                            const templateRow = dimensionsContainer.querySelector('.dimension-row');
                            if (templateRow) {
                                // Clear existing rows except the first one
                                const existingRows = dimensionsContainer.querySelectorAll('.dimension-row');
                                for (let i = 1; i < existingRows.length; i++) {
                                    existingRows[i].remove();
                                }
                                
                                // Fill in dimensions
                                storedData.dimensions.forEach((dim, index) => {
                                    if (index === 0) {
                                        // Update first row
                                        const inputs = templateRow.querySelectorAll('input[type="number"]');
                                        if (inputs.length >= 4) {
                                            inputs[0].value = dim.length;
                                            inputs[1].value = dim.width;
                                            inputs[2].value = dim.height;
                                            inputs[3].value = dim.boxCount;
                                        }
                                    } else {
                                        // Clone template for additional rows
                                        const newRow = templateRow.cloneNode(true);
                                        const inputs = newRow.querySelectorAll('input[type="number"]');
                                        if (inputs.length >= 4) {
                                            inputs[0].value = dim.length;
                                            inputs[1].value = dim.width;
                                            inputs[2].value = dim.height;
                                            inputs[3].value = dim.boxCount;
                                        }
                                        
                                        // Show remove button
                                        const removeBtn = newRow.querySelector('.remove-dimension');
                                        if (removeBtn) {
                                            removeBtn.style.display = 'block';
                                            removeBtn.addEventListener('click', function() {
                                                newRow.remove();
                                            });
                                        }
                                        
                                        // Add to container
                                        dimensionsContainer.appendChild(newRow);
                                    }
                                });
                            }
                        }
                    }
                    
                    // Apply weight
                    if (storedData.totalWeight) {
                        // Convert kg to grams
                        const weightInGrams = storedData.totalWeight * 1000;
                        
                        // Find weight input
                        const weightInput = document.querySelector('input[type="number"][class*="weight"], input[id*="weight"]');
                        if (weightInput) {
                            weightInput.value = weightInGrams;
                        } else {
                            // Try by section
                            const weightSection = document.querySelector('h5, .card-header').textContent.includes('Weight');
                            if (weightSection) {
                                const section = weightSection.closest('.card');
                                const input = section.querySelector('input[type="number"]');
                                if (input) input.value = weightInGrams;
                            }
                        }
                    }
                    
                    console.log('Successfully applied dimension and weight data');
                } catch (error) {
                    console.error('Error applying stored data:', error);
                }
            } else {
                console.log('No stored dimension data found');
            }
        });
    }
})();
