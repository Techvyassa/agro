// Simple script to display the API response in a popup
document.addEventListener('DOMContentLoaded', function() {
    const createOrderBtn = document.getElementById('createOrderBtn');
    
    if (createOrderBtn) {
        // Remove existing listeners and add our own
        const newBtn = createOrderBtn.cloneNode(true);
        createOrderBtn.parentNode.replaceChild(newBtn, createOrderBtn);
        
        newBtn.addEventListener('click', function() {
            const orderForm = document.getElementById('createOrderForm');
            const orderLoader = document.getElementById('orderLoader');
            
            if (!orderForm) {
                console.error('Order form not found');
                return;
            }
            
            // Basic validation
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
                alert('Please fill in all required fields');
                return;
            }
            
            // Show loader
            if (orderLoader) orderLoader.classList.remove('d-none');
            
            // Create FormData and convert to JSON
            const formData = new FormData(orderForm);
            const jsonData = {};
            
            // Process form data
            for (const [key, value] of formData.entries()) {
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
            
            // Convert fields that need to be numbers
            if (jsonData.pickUpId) jsonData.pickUpId = parseInt(jsonData.pickUpId);
            if (jsonData.retrunId) jsonData.retrunId = parseInt(jsonData.retrunId);
            if (jsonData.invoiceAmt) jsonData.invoiceAmt = parseInt(jsonData.invoiceAmt);
            if (jsonData.codAmt) jsonData.codAmt = parseInt(jsonData.codAmt);
            if (jsonData.qty) jsonData.qty = parseInt(jsonData.qty);
            
            // Fix orderItems format
            const orderItems = [];
            const boxRows = document.querySelectorAll('.box-detail-row');
            boxRows.forEach((box, index) => {
                const weight = box.querySelector('.box-weight')?.value || '5';
                const length = box.querySelector('input[name^="orderItems["][name$="[length]"]')?.value || '11';
                const breadth = box.querySelector('input[name^="orderItems["][name$="[breadth]"]')?.value || '12';
                const height = box.querySelector('input[name^="orderItems["][name$="[height]"]')?.value || '14';
                
                orderItems.push({
                    noOfBox: 1,
                    physical_weight: weight,
                    phyWeight: parseFloat(weight),
                    length: parseInt(length),
                    breadth: parseInt(breadth),
                    height: parseInt(height)
                });
            });
            
            jsonData.orderItems = orderItems;
            
            // Handle file upload
            const invoiceFileUpload = document.getElementById('invoiceFileUpload');
            
            const submitToApi = function(data) {
                console.log('Submitting data:', data);
                
                // Call API
                fetch('order-proxy.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(responseData => {
                    console.log('API Response:', responseData);
                    
                    // Hide loader
                    if (orderLoader) orderLoader.classList.add('d-none');
                    
                    // Create a simple popup
                    const popup = document.createElement('div');
                    popup.style.position = 'fixed';
                    popup.style.top = '50%';
                    popup.style.left = '50%';
                    popup.style.transform = 'translate(-50%, -50%)';
                    popup.style.backgroundColor = 'white';
                    popup.style.padding = '20px';
                    popup.style.borderRadius = '8px';
                    popup.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                    popup.style.zIndex = '9999';
                    popup.style.maxWidth = '500px';
                    popup.style.width = '90%';
                    
                    // Create popup content
                    let content = '';
                    
                    if (responseData.success) {
                        // Success message
                        content = `
                            <div style="text-align:center; margin-bottom:15px;">
                                <h4 style="color:#28a745;">Order Created Successfully!</h4>
                                <p style="margin-top:10px;">${responseData.message || 'Your order has been submitted.'}</p>
                            </div>
                            <div style="border-top:1px solid #eee; padding-top:15px;">
                                <p><strong>Order ID:</strong> ${responseData.data || responseData.order_id || ''}</p>
                                <p><strong>Customer:</strong> ${data.buyer?.fName || ''} ${data.buyer?.lName || ''}</p>
                                <p><strong>Item:</strong> ${data.itemName || ''} (${data.qty || 1} boxes)</p>
                                <p><strong>Invoice Amount:</strong> ₹${data.invoiceAmt || 0}</p>
                            </div>
                        `;
                    } else {
                        // Error message
                        content = `
                            <div style="text-align:center; margin-bottom:15px;">
                                <h4 style="color:#dc3545;">Error</h4>
                                <p style="margin-top:10px;">${responseData.error || responseData.message || 'An error occurred while processing your order.'}</p>
                            </div>
                        `;
                    }
                    
                    // API response details
                    content += `
                        <div style="margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                            <h5 style="margin-bottom:10px;">API Response Details:</h5>
                            <pre style="background:#f8f9fa; padding:10px; border-radius:4px; overflow:auto; max-height:200px;">${JSON.stringify(responseData, null, 2)}</pre>
                        </div>
                    `;
                    
                    // Add buttons
                    content += `
                        <div style="text-align:right; margin-top:15px;">
                            <button id="closePopupBtn" style="background:#6c757d; color:white; border:none; padding:8px 16px; border-radius:4px; margin-right:10px; cursor:pointer;">Close</button>
                            <button id="newOrderBtn" style="background:#007bff; color:white; border:none; padding:8px 16px; border-radius:4px; cursor:pointer;">New Order</button>
                        </div>
                    `;
                    
                    popup.innerHTML = content;
                    document.body.appendChild(popup);
                    
                    // Add overlay
                    const overlay = document.createElement('div');
                    overlay.style.position = 'fixed';
                    overlay.style.top = '0';
                    overlay.style.left = '0';
                    overlay.style.width = '100%';
                    overlay.style.height = '100%';
                    overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    overlay.style.zIndex = '9998';
                    document.body.appendChild(overlay);
                    
                    // Add event listeners to buttons
                    document.getElementById('closePopupBtn').addEventListener('click', function() {
                        document.body.removeChild(popup);
                        document.body.removeChild(overlay);
                    });
                    
                    document.getElementById('newOrderBtn').addEventListener('click', function() {
                        document.body.removeChild(popup);
                        document.body.removeChild(overlay);
                        
                        // Reset form
                        orderForm.reset();
                        
                        // Reset default values
                        document.getElementById('orderDate').valueAsDate = new Date();
                        document.getElementById('pickUpId').value = '143442';
                        document.getElementById('pickUpCity').value = 'THANE';
                        document.getElementById('pickUpState').value = 'MAHARASHTRA';
                        document.getElementById('retrunId').value = '143442';
                        document.getElementById('returnCity').value = 'THANE';
                        document.getElementById('returnState').value = 'MAHARASHTRA';
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Hide loader
                    if (orderLoader) orderLoader.classList.add('d-none');
                    
                    // Show error alert
                    alert(`Error: ${error.message}`);
                });
            };
            
            // Handle file upload if present
            if (invoiceFileUpload && invoiceFileUpload.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    jsonData.invoiceFile = e.target.result;
                    submitToApi(jsonData);
                };
                reader.readAsDataURL(invoiceFileUpload.files[0]);
            } else {
                jsonData.invoiceFile = "data:image/png;base64,iVBORw0";
                submitToApi(jsonData);
            }
        });
    }
});
