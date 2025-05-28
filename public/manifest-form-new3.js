    // Function to add dimension row
    function addDimensionRow() {
        const container = document.getElementById('dimensionsContainer');
        const template = document.querySelector('.dimension-row');
        const newRow = template.cloneNode(true);
        
        // Clear input values but maintain default box count
        newRow.querySelector('.dimension-length').value = '';
        newRow.querySelector('.dimension-width').value = '';
        newRow.querySelector('.dimension-height').value = '';
        newRow.querySelector('.dimension-box-count').value = '1';
        
        // Add event listener to remove button
        const removeButton = newRow.querySelector('.remove-dimension');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (document.querySelectorAll('.dimension-row').length > 1) {
                    newRow.remove();
                    updateRemoveButtonsVisibility();
                }
            });
        }
        
        container.appendChild(newRow);
        updateRemoveButtonsVisibility();
    }
    
    // Function to add shipment row
    function addShipmentRow() {
        const container = document.getElementById('shipmentContainer');
        const template = document.querySelector('.shipment-row');
        const newRow = template.cloneNode(true);
        
        // Clear input values but maintain default box count
        newRow.querySelector('.shipment-order-id').value = '';
        newRow.querySelector('.shipment-box-count').value = '1';
        newRow.querySelector('.shipment-description').value = '';
        
        // Add event listener to remove button
        const removeButton = newRow.querySelector('.remove-shipment');
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                if (document.querySelectorAll('.shipment-row').length > 1) {
                    newRow.remove();
                    updateRemoveButtonsVisibility();
                }
            });
        }
        
        container.appendChild(newRow);
        updateRemoveButtonsVisibility();
    }
    
    // Function to collect invoices data
    function getInvoicesData() {
        const invoices = [];
        document.querySelectorAll('.invoice-row').forEach(row => {
            const invNum = row.querySelector('.invoice-number').value;
            const invAmt = parseFloat(row.querySelector('.invoice-amount').value);
            const ewaybill = row.querySelector('.invoice-eway').value;
            
            if (invNum && !isNaN(invAmt)) {
                invoices.push({
                    inv_num: invNum,
                    inv_amt: invAmt,
                    ewaybill: ewaybill || ""
                });
            }
        });
        return invoices;
    }
    
    // Function to collect dimensions data
    function getDimensionsData() {
        const dimensions = [];
        document.querySelectorAll('.dimension-row').forEach(row => {
            const length = parseFloat(row.querySelector('.dimension-length').value);
            const width = parseFloat(row.querySelector('.dimension-width').value);
            const height = parseFloat(row.querySelector('.dimension-height').value);
            const boxCount = parseInt(row.querySelector('.dimension-box-count').value);
            
            if (!isNaN(length) && !isNaN(width) && !isNaN(height) && !isNaN(boxCount)) {
                dimensions.push({
                    length_cm: length,
                    width_cm: width,
                    height_cm: height,
                    box_count: boxCount
                });
            }
        });
        return dimensions;
    }
    
    // Function to collect shipment data
    function getShipmentData() {
        const shipments = [];
        document.querySelectorAll('.shipment-row').forEach(row => {
            const orderId = row.querySelector('.shipment-order-id').value;
            const boxCount = parseInt(row.querySelector('.shipment-box-count').value);
            const description = row.querySelector('.shipment-description').value;
            
            if (orderId && !isNaN(boxCount) && description) {
                shipments.push({
                    order_id: orderId,
                    box_count: boxCount,
                    description: description
                });
            }
        });
        return shipments;
    }
    
    // Function to get document data
    function getDocumentData() {
        const invoices = getInvoicesData();
        const invoiceNums = invoices.map(inv => inv.inv_num);
        
        return [
            {
                doc_type: "INVOICE_COPY",
                doc_meta: {
                    invoice_num: invoiceNums
                }
            }
        ];
    }
    
    // Function to validate form
    function validateForm() {
        // Check if pickup and drop locations are selected
        if (!selectedPickupLocation || !selectedDropLocation) {
            alert('Please select both pickup and drop locations.');
            return false;
        }
        
        // Check if billing address is fetched
        if (!billingAddressData) {
            alert('Please fetch billing address before creating manifest.');
            return false;
        }
        
        // Check if weight is entered
        const weight = document.getElementById('weightGrams').value;
        if (!weight || isNaN(parseInt(weight))) {
            alert('Please enter a valid weight in grams.');
            return false;
        }
        
        // Check if at least one invoice is entered
        const invoices = getInvoicesData();
        if (invoices.length === 0) {
            alert('Please enter at least one invoice.');
            return false;
        }
        
        // Check if at least one dimension is entered
        const dimensions = getDimensionsData();
        if (dimensions.length === 0) {
            alert('Please enter at least one dimension.');
            return false;
        }
        
        // Check if at least one shipment is entered
        const shipments = getShipmentData();
        if (shipments.length === 0) {
            alert('Please enter at least one shipment detail.');
            return false;
        }
        
        // Check if invoice PDF is uploaded
        const invoiceCopy = document.getElementById('invoiceCopy');
        if (!invoiceCopy.files || invoiceCopy.files.length === 0) {
            alert('Please upload an invoice copy PDF.');
            return false;
        }
        
        return true;
    }
    
    // Function to create manifest
    function createManifest() {
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Get form data
        const freightMode = document.querySelector('input[name="freightMode"]:checked').value;
        const paymentMode = document.querySelector('input[name="paymentMode"]:checked').value;
        const weight = parseInt(document.getElementById('weightGrams').value);
        
        // Get facility ID based on freight mode
        const facilityId = freightMode === 'fod' 
            ? selectedDropLocation.id
            : selectedPickupLocation.id;
        
        // Get address details from billing address data
        const addressDetails = billingAddressData.address_details || {};
        const billingDetails = billingAddressData.billing_details || {};
        const shippingAddress = billingAddressData.shipping_address || {};
        
        // Create manifest payload
        const manifestPayload = {
            pickup_location_name: selectedPickupLocation.name,
            dropoff_store_code: selectedDropLocation.name, // Changed from ID to name as per API requirement
            rov_insurance: false,
            fm_pickup: freightMode === 'fop',
            freight_mode: freightMode,
            billing_store_id: facilityId,
            billing_warehouse_id: null,
            billing_address: {
                name: addressDetails.contact_person || shippingAddress.name || 'Unknown',
                company: addressDetails.company || 'Unknown Company',
                consignor: addressDetails.company || 'Unknown Consignor',
                address: shippingAddress.line1 || addressDetails.address || 'Unknown Address',
                city: shippingAddress.city || 'Unknown City',
                state: shippingAddress.state || 'Unknown State',
                pin: (shippingAddress.pin_code || '000000').toString(),
                phone: addressDetails.phone_number || 'Unknown Phone',
                pan_number: billingDetails?.pan_number || null,
                gst_number: billingDetails?.gst_number || null
            },
            invoices: getInvoicesData(),
            dimensions: getDimensionsData(),
            weight_g: weight,
            shipment_details: getShipmentData(),
            payment_mode: paymentMode,
            doc_data: getDocumentData()
        };
        
        console.log('Manifest Payload:', manifestPayload);
        
        // In a real implementation, we would send this to the server
        // For now, we'll just show a success alert
        alert('Manifest created successfully! Check console for payload details.');
        
        // In a real implementation, we would also handle the file upload
        // For example:
        /*
        const formData = new FormData();
        formData.append('login_type', 'b2b');
        formData.append('manifest_payload', JSON.stringify(manifestPayload));
        formData.append('file', document.getElementById('invoiceCopy').files[0]);
        
        fetch('http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/create-manifest', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Manifest API response:', data);
            if (data.status === 'success') {
                alert('Manifest created successfully!');
                window.location.href = 'freight.html';
            } else {
                alert('Error creating manifest: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error creating manifest:', error);
            alert('Error creating manifest: ' + error.message);
        });
        */
    }
