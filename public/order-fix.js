// Fix for the null reference error in freight-real.js
document.addEventListener('DOMContentLoaded', function() {
    // Get the Create Order button
    const createOrderBtn = document.getElementById('createOrderBtn');
    
    if (createOrderBtn) {
        // Remove any existing click listeners
        const newButton = createOrderBtn.cloneNode(true);
        createOrderBtn.parentNode.replaceChild(newButton, createOrderBtn);
        
        // Add our own click listener that includes safety checks
        newButton.addEventListener('click', function() {
            // Get the form
            const orderForm = document.getElementById('createOrderForm');
            if (!orderForm) {
                console.error('Order form not found');
                return;
            }
            
            // Validate form
            const requiredFields = orderForm.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                const orderError = document.getElementById('orderError');
                if (orderError) {
                    orderError.textContent = 'Please fill in all required fields.';
                    orderError.classList.remove('d-none');
                } else {
                    alert('Please fill in all required fields.');
                }
                return;
            }
            
            // Hide error and show loader
            const orderError = document.getElementById('orderError');
            const orderLoader = document.getElementById('orderLoader');
            
            if (orderError) orderError.classList.add('d-none');
            if (orderLoader) orderLoader.classList.remove('d-none');
            
            // Collect all form data
            const formData = new FormData(orderForm);
            const jsonData = {};
            
            // Convert FormData to JSON
            for (const [key, value] of formData.entries()) {
                // Handle nested fields with square brackets (e.g. buyer[fName])
                if (key.includes('[')) {
                    const matches = key.match(/(\w+)\[(\w+)\](?:\[(\w+)\])?/);
                    if (matches) {
                        const mainKey = matches[1];
                        const subKey = matches[2];
                        
                        if (!jsonData[mainKey]) {
                            jsonData[mainKey] = {};
                        }
                        
                        if (matches[3]) {
                            const subSubKey = matches[3];
                            if (!jsonData[mainKey][subKey]) {
                                jsonData[mainKey][subKey] = {};
                            }
                            jsonData[mainKey][subKey][subSubKey] = value;
                        } else {
                            jsonData[mainKey][subKey] = value;
                        }
                    }
                } else {
                    jsonData[key] = value;
                }
            }
            
            // Fix orderItems format (convert from object to array)
            if (jsonData.orderItems && !Array.isArray(jsonData.orderItems)) {
                const items = [];
                for (const key in jsonData.orderItems) {
                    if (jsonData.orderItems.hasOwnProperty(key)) {
                        const item = jsonData.orderItems[key];
                        // Convert numeric strings to numbers
                        if (item.noOfBox) item.noOfBox = parseInt(item.noOfBox);
                        if (item.physical_weight) item.physical_weight = parseFloat(item.physical_weight);
                        if (item.phyWeight) item.phyWeight = parseFloat(item.phyWeight);
                        if (item.length) item.length = parseInt(item.length);
                        if (item.breadth) item.breadth = parseInt(item.breadth);
                        if (item.height) item.height = parseInt(item.height);
                        
                        items.push(item);
                    }
                }
                jsonData.orderItems = items;
            }
            
            // Convert pickUpId and retrunId to integers
            if (jsonData.pickUpId) jsonData.pickUpId = parseInt(jsonData.pickUpId);
            if (jsonData.retrunId) jsonData.retrunId = parseInt(jsonData.retrunId);
            
            // Handle file upload if needed
            const invoiceFileUpload = document.getElementById('invoiceFileUpload');
            
            const processApiCall = function(data) {
                // Send data to the API
                fetch('order-proxy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(responseData => {
                    // Hide loader
                    if (orderLoader) orderLoader.classList.add('d-none');
                    
                    // Check if response modal exists
                    const responseModalEl = document.getElementById('responseModalContainer');
                    if (!responseModalEl) {
                        // Fallback to alert if modal doesn't exist
                        if (responseData.success) {
                            alert(`Order created successfully! Order ID: ${responseData.data || responseData.order_id || jsonData.orderIds}`);
                        } else {
                            alert(`Error: ${responseData.error || responseData.message || 'Unknown error'}`);
                        }
                        return;
                    }
                    
                    // Show response in modal
                    const responseModal = new bootstrap.Modal(responseModalEl);
                    const responseSuccess = document.getElementById('responseSuccess');
                    const responseError = document.getElementById('responseError');
                    const responseJson = document.getElementById('responseJson');
                    
                    // Clear previous messages (safely)
                    if (responseSuccess) responseSuccess.classList.add('d-none');
                    if (responseError) responseError.classList.add('d-none');
                    if (responseJson) responseJson.classList.add('d-none');
                    
                    // Handle success or error
                    if (responseData.success) {
                        // Success
                        if (responseSuccess) {
                            responseSuccess.innerHTML = `
                                <h5><i class="fas fa-check-circle"></i> Order Created Successfully!</h5>
                                <p>Your order has been submitted. Order details:</p>
                                <ul>
                                    <li><strong>Order ID:</strong> ${responseData.data || responseData.order_id || jsonData.orderIds}</li>
                                    <li><strong>Customer:</strong> ${jsonData.buyer?.fName || ''} ${jsonData.buyer?.lName || ''}</li>
                                    <li><strong>Item:</strong> ${jsonData.itemName || ''} (${jsonData.qty || 1} boxes)</li>
                                    <li><strong>Invoice Amount:</strong> ₹${jsonData.invoiceAmt || 0}</li>
                                </ul>
                                <p class="mt-3 text-success">${responseData.message || 'Order created successfully!'}</p>
                            `;
                            responseSuccess.classList.remove('d-none');
                        }
                        
                        // Show raw response
                        if (responseJson) {
                            responseJson.classList.remove('d-none');
                            const pre = responseJson.querySelector('pre');
                            if (pre) {
                                pre.textContent = JSON.stringify(responseData, null, 2);
                            }
                        }
                    } else {
                        // Error
                        if (responseError) {
                            responseError.textContent = `Error: ${responseData.error || responseData.message || 'Unknown error'}`;
                            responseError.classList.remove('d-none');
                        }
                    }
                    
                    // Show modal
                    responseModal.show();
                })
                .catch(error => {
                    console.error('Order submission error:', error);
                    
                    // Hide loader safely
                    if (orderLoader) {
                        orderLoader.classList.add('d-none');
                    }
                    
                    // Try to show error in modal
                    try {
                        const responseModalEl = document.getElementById('responseModalContainer');
                        if (!responseModalEl) {
                            // Fallback to alert
                            alert(`Error: ${error.message}`);
                            return;
                        }
                        
                        const responseModal = new bootstrap.Modal(responseModalEl);
                        const responseSuccess = document.getElementById('responseSuccess');
                        const responseError = document.getElementById('responseError');
                        const responseJson = document.getElementById('responseJson');
                        
                        // Clear previous messages
                        if (responseSuccess) responseSuccess.classList.add('d-none');
                        if (responseError) responseError.classList.add('d-none');
                        if (responseJson) responseJson.classList.add('d-none');
                        
                        // Show error
                        if (responseError) {
                            responseError.textContent = `Error: ${error.message}`;
                            responseError.classList.remove('d-none');
                            responseModal.show();
                        } else {
                            alert(`Error: ${error.message}`);
                        }
                    } catch (e) {
                        // Ultimate fallback
                        alert(`Error submitting order: ${error.message}`);
                    }
                });
            };
            
            // Handle file upload if needed
            if (invoiceFileUpload && invoiceFileUpload.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    jsonData.invoiceFile = e.target.result;
                    processApiCall(jsonData);
                };
                reader.readAsDataURL(invoiceFileUpload.files[0]);
            } else {
                // Use default placeholder
                jsonData.invoiceFile = "data:image/png;base64,iVBORw0";
                processApiCall(jsonData);
            }
        });
    }
});
