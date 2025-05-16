document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as default
    const orderDateInput = document.getElementById('orderDate');
    if (orderDateInput) {
        const today = new Date();
        orderDateInput.valueAsDate = today;
    }
    
    // Form elements
    const orderForm = document.getElementById('createOrderForm');
    const createOrderBtn = document.getElementById('createOrderBtn');
    const orderLoader = document.getElementById('orderLoader');
    const orderError = document.getElementById('orderError');
    
    // Popup elements
    const responsePopup = document.getElementById('responsePopup');
    const responseOverlay = document.getElementById('responseOverlay');
    const responseTitle = document.getElementById('responseTitle');
    const responseContent = document.getElementById('responseContent');
    const responseJsonContent = document.getElementById('responseJsonContent');
    const closeResponseBtn = document.getElementById('closeResponseBtn');
    const newOrderBtn = document.getElementById('newOrderBtn');
    
    // Add event listener for Create Order button
    if (createOrderBtn) {
        createOrderBtn.addEventListener('click', submitOrder);
    }
    
    // Close response popup
    if (closeResponseBtn) {
        closeResponseBtn.addEventListener('click', function() {
            hideResponsePopup();
        });
    }
    
    // New order button (now behaves like Close button)
    if (newOrderBtn) {
        newOrderBtn.addEventListener('click', function() {
            hideResponsePopup();
        });
    }
    
    // Same as pickup checkbox
    const sameAsPickupCheckbox = document.getElementById('sameAsPickup');
    if (sameAsPickupCheckbox) {
        sameAsPickupCheckbox.addEventListener('change', function() {
            updateReturnLocation();
        });
    }
    
    // Add event listeners for Add Box buttons
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('add-box-btn')) {
            addNewBox();
        }
        
        if (e.target && e.target.classList.contains('remove-box-btn')) {
            removeBox(e.target.closest('.box-detail-row'));
        }
    });
    
    // Update return location based on pickup
    function updateReturnLocation() {
        if (sameAsPickupCheckbox && sameAsPickupCheckbox.checked) {
            document.getElementById('retrunId').value = document.getElementById('pickUpId').value;
            document.getElementById('returnCity').value = document.getElementById('pickUpCity').value;
            document.getElementById('returnState').value = document.getElementById('pickUpState').value;
        }
    }
    
    // Add a new box
    function addNewBox() {
        const boxDetailsContainer = document.getElementById('boxDetailsContainer');
        if (!boxDetailsContainer) return;
        
        const boxRows = boxDetailsContainer.querySelectorAll('.box-detail-row');
        const newIndex = boxRows.length;
        
        const newBoxRow = document.createElement('div');
        newBoxRow.className = 'box-detail-row border p-3 mb-3 rounded bg-light';
        newBoxRow.innerHTML = `
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label">Box #</label>
                    <input type="text" class="form-control" value="${newIndex + 1}" readonly>
                    <input type="hidden" class="noOfBox" name="orderItems[${newIndex}][noOfBox]" value="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Weight (kg)*</label>
                    <input type="number" class="form-control box-weight" name="orderItems[${newIndex}][physical_weight]" value="5" required>
                    <input type="hidden" class="phyWeight" name="orderItems[${newIndex}][phyWeight]" value="5">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Length (cm)*</label>
                    <input type="number" class="form-control" name="orderItems[${newIndex}][length]" value="11" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Width (cm)*</label>
                    <input type="number" class="form-control" name="orderItems[${newIndex}][breadth]" value="12" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Height (cm)*</label>
                    <input type="number" class="form-control" name="orderItems[${newIndex}][height]" value="14" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Actions</label>
                    <button type="button" class="btn btn-danger w-100 remove-box-btn">Remove</button>
                </div>
            </div>
        `;
        
        boxDetailsContainer.appendChild(newBoxRow);
        
        // Update quantity field
        const qtyInput = document.getElementById('qty');
        if (qtyInput) {
            qtyInput.value = boxRows.length + 1;
        }
    }
    
    // Remove a box
    function removeBox(boxRow) {
        if (!boxRow) return;
        
        const boxDetailsContainer = document.getElementById('boxDetailsContainer');
        if (!boxDetailsContainer) return;
        
        boxRow.remove();
        
        // Renumber remaining boxes and update names
        const remainingBoxes = boxDetailsContainer.querySelectorAll('.box-detail-row');
        remainingBoxes.forEach((box, idx) => {
            // Update box number display
            box.querySelector('input[type="text"]').value = idx + 1;
            
            // Update input names with new indices
            const inputs = box.querySelectorAll('input[name^="orderItems["]');
            inputs.forEach(input => {
                const nameParts = input.name.split(/[\[\]]+/);
                if (nameParts.length >= 3) {
                    input.name = `orderItems[${idx}][${nameParts[2]}]`;
                }
            });
        });
        
        // Update quantity field
        const qtyInput = document.getElementById('qty');
        if (qtyInput) {
            qtyInput.value = remainingBoxes.length;
        }
    }
    
    // Reset the form
    function resetForm() {
        if (!orderForm) return;
        
        // Reset the form
        orderForm.reset();
        
        // Set today's date
        if (orderDateInput) {
            orderDateInput.valueAsDate = new Date();
        }
        
        // Reset pickup/return location values
        document.getElementById('pickUpId').value = '143442';
        document.getElementById('pickUpCity').value = 'THANE';
        document.getElementById('pickUpState').value = 'MAHARASHTRA';
        document.getElementById('retrunId').value = '143442';
        document.getElementById('returnCity').value = 'THANE';
        document.getElementById('returnState').value = 'MAHARASHTRA';
        
        // Reset box details - remove all except first
        const boxDetailsContainer = document.getElementById('boxDetailsContainer');
        if (boxDetailsContainer) {
            const boxItems = boxDetailsContainer.querySelectorAll('.box-detail-row');
            for (let i = boxItems.length - 1; i > 0; i--) {
                boxItems[i].remove();
            }
            
            // Reset first box values
            const firstBox = boxDetailsContainer.querySelector('.box-detail-row');
            if (firstBox) {
                firstBox.querySelector('.box-weight').value = '5';
                firstBox.querySelector('input[name="orderItems[0][length]"]').value = '11';
                firstBox.querySelector('input[name="orderItems[0][breadth]"]').value = '12';
                firstBox.querySelector('input[name="orderItems[0][height]"]').value = '14';
            }
        }
        
        // Reset quantity
        document.getElementById('qty').value = '1';
        
        // Clear any errors
        if (orderError) {
            orderError.classList.add('d-none');
            orderError.textContent = '';
        }
    }
    
    // Hide response popup
    function hideResponsePopup() {
        if (responsePopup) responsePopup.style.display = 'none';
        if (responseOverlay) responseOverlay.style.display = 'none';
    }
    
    // Show response popup
    function showResponsePopup() {
        if (responsePopup) responsePopup.style.display = 'block';
        if (responseOverlay) responseOverlay.style.display = 'block';
    }
    
    // Show loading in popup
    function showLoadingInPopup() {
        if (responseTitle) responseTitle.textContent = 'Processing Order';
        if (responseContent) {
            responseContent.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Creating your order...</p>
                </div>
            `;
        }
        showResponsePopup();
    }
    
    // Show error in popup
    function showErrorInPopup(message) {
        if (responseTitle) responseTitle.textContent = 'Error';
        if (responseContent) {
            responseContent.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                    <p>${message}</p>
                </div>
            `;
        }
        showResponsePopup();
    }
    
    // Handle order form submission
    function submitOrder() {
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
            showErrorInPopup('Please fill in all required fields.');
            return;
        }
        
        // Show loading in popup
        showLoadingInPopup();
        
        // Create the payload in the exact format required by the API
        const formData = {
            orderIds: document.getElementById('orderIds').value,
            orderDate: document.getElementById('orderDate').value,
            pickUpId: parseInt(document.getElementById('pickUpId').value) || 0,
            pickUpCity: document.getElementById('pickUpCity').value,
            pickUpState: document.getElementById('pickUpState').value,
            retrunId: parseInt(document.getElementById('retrunId').value) || 0,
            returnCity: document.getElementById('returnCity').value,
            returnState: document.getElementById('returnState').value,
            invoiceAmt: parseInt(document.getElementById('invoiceAmt').value) || 0,
            itemName: document.getElementById('itemName').value,
            codAmt: parseInt(document.getElementById('codAmt').value) || 0,
            qty: parseInt(document.getElementById('qty').value) || 1,
            buyer: {
                fName: document.getElementById('buyerFName').value,
                lName: document.getElementById('buyerLName').value,
                emailId: document.getElementById('buyerEmail').value || null,
                buyerAddresses: {
                    address1: document.getElementById('buyerAddress').value,
                    mobileNo: document.getElementById('buyerMobile').value,
                    pinId: document.getElementById('buyerPincode').value
                }
            },
            orderItems: []
        };
        
        // Get all box items from the form
        const boxDetailsContainer = document.getElementById('boxDetailsContainer');
        if (boxDetailsContainer) {
            const boxItems = boxDetailsContainer.querySelectorAll('.box-detail-row');
            boxItems.forEach((box, index) => {
                const weightInput = box.querySelector('.box-weight');
                const lengthInput = box.querySelector('input[name^="orderItems["][name$="[length]"]');
                const widthInput = box.querySelector('input[name^="orderItems["][name$="[breadth]"]');
                const heightInput = box.querySelector('input[name^="orderItems["][name$="[height]"]');
                
                if (weightInput && lengthInput && widthInput && heightInput) {
                    formData.orderItems.push({
                        noOfBox: 1,
                        physical_weight: weightInput.value,
                        length: parseInt(lengthInput.value) || 10,
                        breadth: parseInt(widthInput.value) || 10,
                        height: parseInt(heightInput.value) || 10,
                        phyWeight: parseFloat(weightInput.value) || 5
                    });
                }
            });
        }
        
        // Log for debugging
        console.log('Submitting order to: order-proxy.php');
        
        // Handle file upload
        const invoiceFileUpload = document.getElementById('invoiceFileUpload');
        if (invoiceFileUpload && invoiceFileUpload.files.length > 0) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Use the base64 data URL
                formData.invoiceFile = e.target.result;
                
                // Continue with API call after file is read
                proceedWithApiCall(formData);
            };
            reader.readAsDataURL(invoiceFileUpload.files[0]);
        } else {
            // No file to process, continue with API call
            formData.invoiceFile = "data:image/png;base64,iVBORw0"; // Default placeholder
            proceedWithApiCall(formData);
        }
    }
    
    // Process API call
    function proceedWithApiCall(formData) {
        console.log('Formatted data:', formData);
        
        // Get user ID from URL query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user_id') || formData.buyer.emailId || 'default@example.com';
        
        // Send data to the server via the proxy with user_id as query parameter
        fetch(`order-proxy.php?user_id=${encodeURIComponent(userId)}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            // Store order data with response in database
            const orderWithResponse = {
                ...formData,
                response: data
            };
            
            // Call the direct PHP endpoint to save in database
            fetch('store-order-data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderWithResponse)
            })
            .then(dbResponse => dbResponse.json())
            .then(dbData => {
                console.log('Order stored in database:', dbData);
            })
            .catch(dbError => {
                console.error('Error storing order in database:', dbError);
            });
            
            // Prepare response popup
            if (responseTitle) {
                // Set title based on response type
                if (data.success) {
                    responseTitle.textContent = 'Order Created Successfully';
                } else if (data.responseCode === 202) {
                    responseTitle.textContent = 'Order Already Exists';
                } else {
                    responseTitle.textContent = 'Error Creating Order';
                }
            }
            
            // Format response content
            if (responseContent) {
                if (data.success) {
                    // Success content
                    responseContent.innerHTML = `
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> ${data.message || 'Order created successfully!'}</h5>
                            <p>Your order has been submitted with the following details:</p>
                            <ul>
                                <li><strong>Order ID:</strong> ${data.data || data.order_id || formData.orderIds}</li>
                                <li><strong>Customer:</strong> ${formData.buyer.fName} ${formData.buyer.lName || ''}</li>
                                <li><strong>Item:</strong> ${formData.itemName} (${formData.qty} boxes)</li>
                                <li><strong>Invoice Amount:</strong> ₹${formData.invoiceAmt}</li>
                            </ul>
                        </div>
                    `;
                } else if (data.responseCode === 202) {
                    // Order already exists message
                    responseContent.innerHTML = `
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-circle"></i> Order Already Exists</h5>
                            <p>${data.message || 'An order with this ID already exists in the system.'}</p>
                            <p>Please try again with a different Order ID.</p>
                        </div>
                    `;
                } else {
                    // Error content
                    responseContent.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
                            <p>${data.error || data.message || 'An error occurred while processing your order.'}</p>
                        </div>
                    `;
                }
            }
            
            // Add JSON response
            if (responseJsonContent) {
                responseJsonContent.textContent = JSON.stringify(data, null, 2);
            }
            
            // Show the popup
            showResponsePopup();
        })
        .catch(error => {
            console.error('Order submission error:', error);
            
            // Handle error in popup
            showErrorInPopup(error.message);
        });
    }
});
