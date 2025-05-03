document.addEventListener('DOMContentLoaded', function() {
    const freightForm = document.getElementById('freightForm');
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');
    const loader = document.getElementById('loader');

    freightForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loader and results section
        resultsSection.style.display = 'block';
        loader.style.display = 'block';
        resultsContainer.innerHTML = '';
        
        // Build request payload
        const payload = {
            common: {
                pincode: {
                    source: document.getElementById('sourcePincode').value,
                    destination: document.getElementById('destinationPincode').value
                },
                payment: {
                    type: document.getElementById('paymentType').value,
                    cheque_payment: document.getElementById('chequePayment').checked
                },
                invoice_amount: parseFloat(document.getElementById('invoiceAmount').value),
                insurance: {
                    rov: document.getElementById('rov').checked
                }
            },
            shipment_details: {
                dimensions: [
                    {
                        length_cm: parseFloat(document.getElementById('length').value),
                        width_cm: parseFloat(document.getElementById('width').value),
                        height_cm: parseFloat(document.getElementById('height').value),
                        box_count: parseInt(document.getElementById('boxCount').value),
                        each_box_dead_weight: parseFloat(document.getElementById('deadWeight').value)
                    }
                ],
                weight_g: parseFloat(document.getElementById('totalWeight').value),
                freight_mode: document.getElementById('freightMode').value
            }
        };

        // Call our Laravel proxy route which will handle the API request and avoid CORS issues
        fetch('/freight-proxy', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            // Hide loader
            loader.style.display = 'none';
            
            // Process and display the actual API results
            displayResults(data);
            
            // Log the response for debugging
            console.log('API Response:', data);
        })
        .catch(error => {
            // Handle errors gracefully
            loader.style.display = 'none';
            resultsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error: ${error.message}
                        <div class="mt-3">
                            <p>The API may not be accessible due to CORS restrictions. Please consider the following options:</p>
                            <ol>
                                <li>Run this application with CORS enabled on the server</li>
                                <li>Use a CORS proxy service</li>
                                <li>Contact API provider to allow CORS from your domain</li>
                            </ol>
                        </div>
                    </div>
                </div>
            `;
            console.error('API Error:', error);
        });
    });

    function displayResults(data) {
        resultsContainer.innerHTML = '';
        
        // Check if data is empty
        if (Object.keys(data).length === 0) {
            resultsContainer.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> No freight estimates available.
                    </div>
                </div>
            `;
            return;
        }

        // Process each carrier's results
        for (const [carrier, estimates] of Object.entries(data)) {
            if (!Array.isArray(estimates) || estimates.length === 0) continue;
            
            // Create a card for each carrier
            const carrierCard = document.createElement('div');
            carrierCard.className = 'col-12 mb-4';
            carrierCard.innerHTML = `
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">${formatCarrierName(carrier)}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="row m-0">
                            ${createEstimateCards(estimates, carrier)}
                        </div>
                    </div>
                </div>
            `;
            
            resultsContainer.appendChild(carrierCard);
        }

        // Add event listeners to toggle buttons
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                const extraDetails = this.closest('.card-body').querySelector('.extra-details');
                
                // Toggle expanded class
                if (extraDetails.style.maxHeight === '1000px') {
                    extraDetails.style.maxHeight = '0';
                    this.innerHTML = 'Show details <i class="fas fa-chevron-down"></i>';
                } else {
                    extraDetails.style.maxHeight = '1000px';
                    this.innerHTML = 'Hide details <i class="fas fa-chevron-up"></i>';
                }
            });
        });
    }

    function createEstimateCards(estimates, carrier) {
        return estimates.map((estimate, index) => {
            const {
                service_name,
                total_charges,
                tat,
                charged_wt,
                risk_type,
                risk_type_charge,
                extra
            } = estimate;

            // Format extra details as a readable JSON
            const extraDetails = extra ? formatExtraDetails(extra) : 'No additional details available';
            
            return `
                <div class="col-md-6 col-lg-4 p-2">
                    <div class="card result-card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">${service_name || 'Service'}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="h3 mb-0">₹${total_charges.toFixed(2)}</span>
                                ${tat ? `<span class="badge bg-info">TAT: ${tat} days</span>` : ''}
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><strong>Charged Weight:</strong></span>
                                    <span>${charged_wt} kg</span>
                                </div>
                                ${risk_type !== null ? `
                                <div class="d-flex justify-content-between">
                                    <span><strong>Risk Type:</strong></span>
                                    <span>${risk_type}</span>
                                </div>` : ''}
                                ${risk_type_charge ? `
                                <div class="d-flex justify-content-between">
                                    <span><strong>Risk Charge:</strong></span>
                                    <span>₹${risk_type_charge.toFixed(2)}</span>
                                </div>` : ''}
                            </div>
                            
                            <button class="btn btn-sm btn-outline-secondary w-100 toggle-details mt-2">
                                Show details <i class="fas fa-chevron-down"></i>
                            </button>
                            
                            <div class="extra-details mt-3">
                                <h6 class="border-bottom pb-2">Price Breakdown</h6>
                                <pre class="bg-light p-3 rounded" style="font-size: 0.8rem; overflow-x: auto;">${extraDetails}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function formatExtraDetails(extra) {
        // Create a formatted version of the extra details
        try {
            // For price breakup, create a more readable format
            if (extra.price_breakup) {
                return formatPriceBreakup(extra);
            }
            
            // For other formats (like bigship)
            return formatGenericExtra(extra);
        } catch (error) {
            return JSON.stringify(extra, null, 2);
        }
    }

    function formatPriceBreakup(extra) {
        // Format for Delhivery-style response
        let result = '';
        
        if (extra.min_charged_wt) {
            result += `Minimum Charged Weight: ${extra.min_charged_wt} kg\n`;
        }
        
        if (extra.price_breakup) {
            result += 'Price Breakup:\n';
            const pb = extra.price_breakup;
            
            // Base charges
            if (pb.base_freight_charge) result += `  Base Freight: ₹${pb.base_freight_charge}\n`;
            if (pb.fuel_surcharge) result += `  Fuel Surcharge: ₹${pb.fuel_surcharge}\n`;
            if (pb.fuel_hike) result += `  Fuel Hike: ₹${pb.fuel_hike}\n`;
            if (pb.insurance_rov) result += `  Insurance (ROV): ₹${pb.insurance_rov}\n`;
            
            // Additional charges
            if (pb.fm) result += `  FM: ₹${pb.fm}\n`;
            if (pb.lm) result += `  LM: ₹${pb.lm}\n`;
            if (pb.green) result += `  Green Tax: ₹${pb.green}\n`;
            
            // ODA charges
            if (pb.oda && (pb.oda.fm || pb.oda.lm)) {
                result += `  ODA: FM ₹${pb.oda.fm || 0}, LM ₹${pb.oda.lm || 0}\n`;
            }
            
            // Total pre-tax
            if (pb.pre_tax_freight_charges) result += `  Pre-tax Charges: ₹${pb.pre_tax_freight_charges}\n`;
            
            // GST
            if (pb.gst) result += `  GST (${pb.gst_percent}%): ₹${pb.gst}\n`;
            
            // Markup
            if (pb.markup) result += `  Markup: ₹${pb.markup}\n`;
            
            // Handling charges
            if (pb.other_handling_charges) result += `  Handling Charges: ₹${pb.other_handling_charges}\n`;
            
            // Meta charges
            if (pb.meta_charges && Object.keys(pb.meta_charges).length > 0) {
                result += '  Meta Charges:\n';
                for (const [key, value] of Object.entries(pb.meta_charges)) {
                    if (value > 0) {
                        result += `    ${formatMetaChargeKey(key)}: ₹${value}\n`;
                    }
                }
            }
        }
        
        return result;
    }

    function formatGenericExtra(extra) {
        // Format for Bigship-style response
        let result = '';
        
        if (extra.courier_partner_id) {
            result += `Courier Partner ID: ${extra.courier_partner_id}\n`;
        }
        
        if (extra.courier_type) {
            result += `Courier Type: ${extra.courier_type}\n`;
        }
        
        if (extra.plan_name) {
            result += `Plan: ${extra.plan_name}\n`;
        }
        
        if (extra.risk_type_name) {
            result += `Risk Type: ${extra.risk_type_name}\n`;
        }
        
        if (extra.zone) {
            result += `Zone: ${extra.zone}\n`;
        }
        
        if (extra.freight_charge) {
            result += `Freight Charge: ₹${extra.freight_charge}\n`;
        }
        
        if (extra.total_freight_charge) {
            result += `Total Freight: ₹${extra.total_freight_charge}\n`;
        }
        
        // Format additional charges
        if (extra.additional_charges && Object.keys(extra.additional_charges).length > 0) {
            result += 'Additional Charges:\n';
            for (const [key, value] of Object.entries(extra.additional_charges)) {
                if (value > 0) {
                    result += `  ${formatChargeKey(key)}: ₹${value}\n`;
                }
            }
        }
        
        // Format other additional charges
        if (extra.other_additional_charges && extra.other_additional_charges.length > 0) {
            result += 'Other Charges:\n';
            for (const charge of extra.other_additional_charges) {
                if (charge.key_value > 0) {
                    result += `  ${formatChargeKey(charge.key_name)}: ₹${charge.key_value}\n`;
                }
            }
        }
        
        return result;
    }

    function formatCarrierName(carrier) {
        // Format carrier name for display
        if (carrier === 'delhivery') {
            return 'Delhivery';
        } else if (carrier.includes('@')) {
            // For email-based carriers, show the full identifier
            return 'Bigship (' + carrier + ')';
        }
        return carrier.charAt(0).toUpperCase() + carrier.slice(1);
    }

    function formatChargeKey(key) {
        // Format charge key for better display
        return key
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    function formatMetaChargeKey(key) {
        // Special format for meta charge keys
        const keyMap = {
            'cod': 'COD',
            'demurrage': 'Demurrage',
            'reattempt': 'Reattempt',
            'handling': 'Handling',
            'pod': 'POD',
            'sunday': 'Sunday Delivery',
            'to_pay': 'To Pay',
            'cheque': 'Cheque',
            'csd': 'CSD',
            'add_cost': 'Additional Cost',
            'adh_vhl': 'Adhoc Vehicle',
            'sp_dlv_area': 'Special Delivery Area',
            'add_machine': 'Additional Machine',
            'add_man_pwr': 'Additional Manpower',
            'mathadi_un': 'Mathadi Union',
            'appt_chg': 'Appointment Charge'
        };
        
        return keyMap[key] || formatChargeKey(key);
    }
});
