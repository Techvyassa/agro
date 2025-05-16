document.addEventListener('DOMContentLoaded', function() {
    // Freight form elements
    const freightForm = document.getElementById('freightForm');
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');
    const loader = document.getElementById('loader');
    const boxesContainer = document.getElementById('boxesContainer');
    const addBoxBtn = document.getElementById('addBoxBtn');
    const totalBoxesInput = document.getElementById('totalBoxes');
    const totalWeightInput = document.getElementById('totalWeight');
    
    // Create a response modal for showing API responses
    const responseModalContainer = document.createElement('div');
    responseModalContainer.id = 'responseModalContainer';
    responseModalContainer.className = 'modal fade';
    responseModalContainer.setAttribute('tabindex', '-1');
    responseModalContainer.setAttribute('aria-labelledby', 'responseModalLabel');
    responseModalContainer.setAttribute('aria-hidden', 'true');
    responseModalContainer.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">Order Creation Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="responseLoader" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Processing response...</p>
                    </div>
                    <div id="responseError" class="alert alert-danger d-none"></div>
                    <div id="responseSuccess" class="alert alert-success d-none"></div>
                    <div id="responseJson" class="bg-light p-3 rounded mt-3 d-none">
                        <h6 class="border-bottom pb-2">API Response Details:</h6>
                        <pre class="mt-2" style="max-height: 300px; overflow-y: auto;"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="continueBtn">Continue</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(responseModalContainer);
    
    // Add event listener for Create Order button
    const createOrderBtn = document.getElementById('createOrderBtn');
    if (createOrderBtn) {
        createOrderBtn.addEventListener('click', function() {
            submitOrder();
        });
    }
    
    // Function to submit order data to the API
    function submitOrder() {
        // Safely get form elements
        const orderForm = document.getElementById('createOrderForm');
        const orderLoader = document.getElementById('orderLoader');
        const orderError = document.getElementById('orderError');
        const orderSuccess = document.getElementById('orderSuccess');
        
        // Check if elements exist before proceeding
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
            if (orderError) {
                orderError.textContent = 'Please fill in all required fields.';
                orderError.classList.remove('d-none');
            }
            return;
        }
        
        // Hide error and show loader
        if (orderError) orderError.classList.add('d-none');
        if (orderLoader) orderLoader.classList.remove('d-none');
        
        // Create the payload in the exact format required by the API
        const formData = {
            orderIds: document.getElementById('orderIds')?.value || '',
            orderDate: document.getElementById('orderDate')?.value || '',
            pickUpId: parseInt(document.getElementById('pickUpId')?.value) || 0,
            pickUpCity: document.getElementById('pickUpCity')?.value || '',
            pickUpState: document.getElementById('pickUpState')?.value || '',
            retrunId: parseInt(document.getElementById('retrunId')?.value) || 0,
            returnCity: document.getElementById('returnCity')?.value || '',
            returnState: document.getElementById('returnState')?.value || '',
            invoiceAmt: parseInt(document.getElementById('invoiceAmt')?.value) || 0,
            itemName: document.getElementById('itemName')?.value || '',
            codAmt: parseInt(document.getElementById('codAmt')?.value) || 0,
            qty: parseInt(document.getElementById('qty')?.value) || 1,
            buyer: {
                fName: document.getElementById('buyerFName')?.value || '',
                lName: document.getElementById('buyerLName')?.value || '',
                emailId: document.getElementById('buyerEmail')?.value || null,
                buyerAddresses: {
                    address1: document.getElementById('buyerAddress')?.value || '',
                    mobileNo: document.getElementById('buyerMobile')?.value || '',
                    pinId: document.getElementById('buyerPincode')?.value || ''
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
        
        // Handle file uploads
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
    
    // Function to proceed with API call after file processing
    function proceedWithApiCall(formData) {
        console.log('Formatted data:', formData);
        
        // Send data to the server via the proxy - using exact same pattern as other working proxies
        fetch('order-proxy.php', {
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
            // Hide loader
            const orderLoader = document.getElementById('orderLoader');
            if (orderLoader) orderLoader.classList.add('d-none');
            
            // Show response in a popup
            const responseModalElement = document.getElementById('responseModalContainer');
            if (!responseModalElement) {
                console.error('Response modal container not found');
                alert('Order processed. Check console for details.');
                console.log(data);
                return;
            }
            
            const responseModal = new bootstrap.Modal(responseModalElement);
            const responseSuccess = document.getElementById('responseSuccess');
            const responseError = document.getElementById('responseError');
            const responseJson = document.getElementById('responseJson');
            const continueBtn = document.getElementById('continueBtn');
            
            // Clear previous messages (safely)
            if (responseSuccess) responseSuccess.classList.add('d-none');
            if (responseError) responseError.classList.add('d-none');
            if (responseJson) responseJson.classList.add('d-none');
            
            // Format and display the response
            try {
                // Check if there's an error in the response
                if (data.detail || data.error) {
                    // Handle FastAPI validation errors (detail field)
                    if (data.detail && Array.isArray(data.detail) && responseError) {
                        const errorMessages = data.detail.map(err => `${err.msg} at ${err.loc.join('.')}`).join('<br>');
                        responseError.innerHTML = `<strong>Validation Error:</strong><br>${errorMessages}`;
                        responseError.classList.remove('d-none');
                    } else if (responseError) {
                        responseError.textContent = `Error: ${data.error || 'API request failed'}`;
                        responseError.classList.remove('d-none');
                    }
                    
                    if (continueBtn) {
                        continueBtn.textContent = 'Try Again';
                        continueBtn.onclick = function() {
                            responseModal.hide();
                        };
                    }
                } else {
                    // Format based on your API response (data.data, success, message)
                    const orderId = data.data || data.order_id || formData.orderIds;
                    const successMessage = data.message || 'Order created successfully';
                    
                    // Show success message
                    if (responseSuccess) {
                        responseSuccess.innerHTML = `
                            <h5><i class="fas fa-check-circle"></i> Order Created Successfully!</h5>
                            <p>Your order has been submitted. Order details:</p>
                            <ul>
                                <li><strong>Order ID:</strong> ${orderId}</li>
                                <li><strong>Customer:</strong> ${formData.buyer.fName} ${formData.buyer.lName || ''}</li>
                                <li><strong>Item:</strong> ${formData.itemName} (${formData.qty} boxes)</li>
                                <li><strong>Invoice Amount:</strong> ₹${formData.invoiceAmt}</li>
                            </ul>
                            <p class="mt-3 text-success">${successMessage}</p>
                        `;
                        responseSuccess.classList.remove('d-none');
                    }
                    
                    // Show raw response data in pretty format
                    if (responseJson) {
                        responseJson.classList.remove('d-none');
                        const pre = responseJson.querySelector('pre');
                        if (pre) {
                            pre.textContent = JSON.stringify(data, null, 2);
                        }
                    }
                    
                    // Set continue button behavior
                    if (continueBtn) {
                        continueBtn.textContent = 'New Order';
                        continueBtn.onclick = function() {
                            responseModal.hide();
                            
                            // Reset the form for a new order
                            const orderForm = document.getElementById('createOrderForm');
                            const createOrderBtn = document.getElementById('createOrderBtn');
                            
                            if (orderForm) {
                                orderForm.reset();
                                
                                // Re-enable create order button
                                if (createOrderBtn) createOrderBtn.disabled = false;
                            }
                        };
                    }
                    
                    // Disable the original submit button to prevent duplicate submissions
                    const createOrderBtn = document.getElementById('createOrderBtn');
                    if (createOrderBtn) createOrderBtn.disabled = true;
                }
                
                // Show the response modal
                responseModal.show();
                
            } catch (e) {
                console.error('Error displaying response:', e);
                if (responseError) {
                    responseError.textContent = `Error displaying response: ${e.message}`;
                    responseError.classList.remove('d-none');
                    responseModal.show();
                } else {
                    alert(`Error displaying response: ${e.message}`);
                }
            }
        })
        .catch(error => {
            console.error('Order submission error:', error);
            
            // Safely hide loader
            const orderLoader = document.getElementById('orderLoader');
            if (orderLoader) orderLoader.classList.add('d-none');
            
            // Try to show error in popup modal
            try {
                const responseModalElement = document.getElementById('responseModalContainer');
                if (!responseModalElement) {
                    alert(`Error: ${error.message}`);
                    return;
                }
                
                const responseModal = new bootstrap.Modal(responseModalElement);
                const responseSuccess = document.getElementById('responseSuccess');
                const responseError = document.getElementById('responseError');
                const responseJson = document.getElementById('responseJson');
                
                // Clear previous messages
                if (responseSuccess) responseSuccess.classList.add('d-none');
                if (responseError) responseError.classList.add('d-none');
                if (responseJson) responseJson.classList.add('d-none');
                
                // Show error message
                if (responseError) {
                    responseError.textContent = `Error: ${error.message}`;
                    responseError.classList.remove('d-none');
                }
                
                responseModal.show();
            } catch (e) {
                // Fallback to simple alert if modal display fails
                alert(`Order submission error: ${error.message}`);
            }
        });
    }
});
