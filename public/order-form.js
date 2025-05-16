document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const orderForm = document.getElementById('createOrderForm');
    const createOrderBtn = document.getElementById('createOrderBtn');
    const orderLoader = document.getElementById('orderLoader');
    const orderError = document.getElementById('orderError');
    const orderSuccess = document.getElementById('orderSuccess');
    const boxDetailsContainer = document.getElementById('boxDetailsContainer');
    const sameAsPickupCheckbox = document.getElementById('sameAsPickup');
    
    // Initialize date input with current date
    const orderDateInput = document.getElementById('orderDate');
    if (orderDateInput) {
        const today = new Date();
        const yyyy = today.getFullYear();
        let mm = today.getMonth() + 1;
        let dd = today.getDate();
        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;
        orderDateInput.value = `${yyyy}-${mm}-${dd}`;
    }
    
    // Add event listener for Create Order button
    if (createOrderBtn) {
        createOrderBtn.addEventListener('click', function() {
            submitOrder();
        });
    }
    
    // Add event listener for Same as Pickup checkbox
    if (sameAsPickupCheckbox) {
        sameAsPickupCheckbox.addEventListener('change', function() {
            const pickUpId = document.getElementById('pickUpId');
            const pickUpCity = document.getElementById('pickUpCity');
            const pickUpState = document.getElementById('pickUpState');
            const retrunId = document.getElementById('retrunId');
            const returnCity = document.getElementById('returnCity');
            const returnState = document.getElementById('returnState');
            
            if (this.checked) {
                retrunId.value = pickUpId.value;
                returnCity.value = pickUpCity.value;
                returnState.value = pickUpState.value;
            } else {
                retrunId.value = '';
                returnCity.value = '';
                returnState.value = '';
            }
        });
    }
    
    // Add event listeners for Add Box buttons
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('add-box-btn')) {
            addNewBox();
        }
    });
    
    // Function to add a new box
    function addNewBox() {
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
        
        // Add event listener for the new remove button
        const removeBtn = newBoxRow.querySelector('.remove-box-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                newBoxRow.remove();
                
                // Renumber remaining boxes
                const remainingBoxes = boxDetailsContainer.querySelectorAll('.box-detail-row');
                remainingBoxes.forEach((box, idx) => {
                    const numberInput = box.querySelector('input[type="text"]');
                    if (numberInput) {
                        numberInput.value = idx + 1;
                    }
                    
                    // Update quantity field
                    if (qtyInput) {
                        qtyInput.value = remainingBoxes.length;
                    }
                });
            });
        }
    }
    
    // Function to submit order data to the API
    function submitOrder() {
        if (!orderForm || !orderLoader || !orderError) return;
        
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
            orderError.textContent = 'Please fill in all required fields.';
            orderError.classList.remove('d-none');
            return;
        }
        
        // Hide error and show loader
        orderError.classList.add('d-none');
        orderLoader.classList.remove('d-none');
        
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
        const boxItems = document.querySelectorAll('.box-detail-row');
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
        
        // Send data to the server via the proxy
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
            if (orderLoader) orderLoader.classList.add('d-none');
            
            // Show response in a popup
            const responseModal = new bootstrap.Modal(document.getElementById('responseModalContainer'));
            const responseSuccess = document.getElementById('responseSuccess');
            const responseError = document.getElementById('responseError');
            const responseJson = document.getElementById('responseJson');
            const continueBtn = document.getElementById('continueBtn');
            
            // Clear previous messages
            if (responseSuccess) responseSuccess.classList.add('d-none');
            if (responseError) responseError.classList.add('d-none');
            if (responseJson) responseJson.classList.add('d-none');
            
            // Format and display the response
            try {
                // Check if there's an error in the response
                if (data.detail || data.error) {
                    // Handle FastAPI validation errors (detail field)
                    if (data.detail && Array.isArray(data.detail)) {
                        const errorMessages = data.detail.map(err => `${err.msg} at ${err.loc.join('.')}`).join('<br>');
                        if (responseError) {
                            responseError.innerHTML = `<strong>Validation Error:</strong><br>${errorMessages}`;
                            responseError.classList.remove('d-none');
                        }
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
                    // Show success message
                    if (responseSuccess) {
                        responseSuccess.innerHTML = `
                            <h5><i class="fas fa-check-circle"></i> Order Created Successfully!</h5>
                            <p>Your order has been submitted. Order details:</p>
                            <ul>
                                <li><strong>Order ID:</strong> ${data.data || data.order_id || formData.orderIds}</li>
                                <li><strong>Customer:</strong> ${formData.buyer.fName} ${formData.buyer.lName || ''}</li>
                                <li><strong>Item:</strong> ${formData.itemName} (${formData.qty} boxes)</li>
                                <li><strong>Invoice Amount:</strong> ₹${formData.invoiceAmt}</li>
                            </ul>
                            <p class="mt-3 text-success">${data.message || 'Order processed successfully'}</p>
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
                            if (orderForm) {
                                orderForm.reset();
                                
                                // Set today's date
                                if (orderDateInput) {
                                    const today = new Date();
                                    const yyyy = today.getFullYear();
                                    let mm = today.getMonth() + 1;
                                    let dd = today.getDate();
                                    if (dd < 10) dd = '0' + dd;
                                    if (mm < 10) mm = '0' + mm;
                                    orderDateInput.value = `${yyyy}-${mm}-${dd}`;
                                }
                                
                                // Reset pickup location values
                                document.getElementById('pickUpId').value = '143442';
                                document.getElementById('pickUpCity').value = 'THANE';
                                document.getElementById('pickUpState').value = 'MAHARASHTRA';
                                document.getElementById('retrunId').value = '143442';
                                document.getElementById('returnCity').value = 'THANE';
                                document.getElementById('returnState').value = 'MAHARASHTRA';
                                
                                // Reset box details
                                const boxItems = boxDetailsContainer.querySelectorAll('.box-detail-row');
                                for (let i = boxItems.length - 1; i > 0; i--) {
                                    boxItems[i].remove();
                                }
                                
                                // Reset quantity
                                document.getElementById('qty').value = '1';
                                
                                // Enable create order button
                                if (createOrderBtn) createOrderBtn.disabled = false;
                            }
                        };
                    }
                }
                
                // Show the response modal
                responseModal.show();
                
            } catch (e) {
                console.error('Error displaying response:', e);
                if (responseError) {
                    responseError.textContent = `Error displaying response: ${e.message}`;
                    responseError.classList.remove('d-none');
                }
                responseModal.show();
            }
        })
        .catch(error => {
            // Hide loader
            if (orderLoader) orderLoader.classList.add('d-none');
            
            // Show error in a popup
            const responseModal = new bootstrap.Modal(document.getElementById('responseModalContainer'));
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
            
            console.error('Order submission error:', error);
        });
    }
});
