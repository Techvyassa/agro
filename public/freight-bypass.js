document.addEventListener('DOMContentLoaded', function() {
    const freightForm = document.getElementById('freightForm');
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');
    const loader = document.getElementById('loader');

    // Sample data to show when API is unavailable
    const sampleFreightData = {
        "delhivery": [
            {
                "service_name": "Delhivery B2B",
                "total_charges": 1517.1,
                "tat": null,
                "charged_wt": 100.0,
                "risk_type": null,
                "risk_type_charge": 100.0,
                "extra": {
                    "min_charged_wt": 20.0,
                    "price_breakup": {
                        "base_freight_charge": 685.0,
                        "fuel_surcharge": 80.25,
                        "fuel_hike": -10.7,
                        "insurance_rov": 100.0,
                        "oda": {
                            "fm": 0,
                            "lm": 0.0
                        },
                        "fm": 100.0,
                        "lm": 0.0,
                        "green": 0.0,
                        "pre_tax_freight_charges": 1354.55,
                        "markup": 0.0,
                        "gst": 162.55,
                        "gst_percent": 12,
                        "other_handling_charges": 500.0,
                        "meta_charges": {
                            "cod": 0.0,
                            "demurrage": 0.0,
                            "reattempt": 0.0,
                            "handling": 200.0,
                            "pod": 0.0,
                            "sunday": 0.0,
                            "to_pay": 300.0,
                            "cheque": 0.0,
                            "csd": 0.0,
                            "add_cost": 0.0,
                            "adh_vhl": 0.0,
                            "sp_dlv_area": 0.0,
                            "add_machine": 0.0,
                            "add_man_pwr": 0.0,
                            "mathadi_un": 0.0,
                            "appt_chg": 0.0
                        }
                    }
                }
            },
            {
                "service_name": "Delhivery Express",
                "total_charges": 1892.3,
                "tat": 2,
                "charged_wt": 100.0,
                "risk_type": null,
                "risk_type_charge": 100.0,
                "extra": {
                    "min_charged_wt": 20.0,
                    "price_breakup": {
                        "base_freight_charge": 965.0,
                        "fuel_surcharge": 96.5,
                        "fuel_hike": -12.8,
                        "insurance_rov": 100.0,
                        "oda": {
                            "fm": 0,
                            "lm": 0.0
                        },
                        "fm": 150.0,
                        "lm": 0.0,
                        "green": 0.0,
                        "pre_tax_freight_charges": 1598.7,
                        "markup": 0.0,
                        "gst": 193.6,
                        "gst_percent": 12,
                        "other_handling_charges": 100.0,
                        "meta_charges": {
                            "cod": 0.0,
                            "demurrage": 0.0,
                            "reattempt": 0.0,
                            "handling": 100.0,
                            "pod": 0.0,
                            "sunday": 0.0,
                            "to_pay": 0.0,
                            "cheque": 0.0,
                            "csd": 0.0,
                            "add_cost": 0.0,
                            "adh_vhl": 0.0,
                            "sp_dlv_area": 0.0,
                            "add_machine": 0.0,
                            "add_man_pwr": 0.0,
                            "mathadi_un": 0.0,
                            "appt_chg": 0.0
                        }
                    }
                }
            }
        ],
        "bigship-support@bigship.com": [
            {
                "service_name": "LTL Delhivery",
                "total_charges": 408.0,
                "tat": 3,
                "charged_wt": 25.0,
                "risk_type": 0.0,
                "risk_type_charge": 0.0,
                "extra": {
                    "courier_partner_id": 1,
                    "courier_category_id": 2,
                    "courier_type": "Surface",
                    "plan_name": "Franchise",
                    "risk_type_name": "Carrier Risk",
                    "zone": "W1-W1",
                    "restricted_pincode_message": null,
                    "freight_charge": 190.0,
                    "cod_charge": 0.0,
                    "total_freight_charge": 190.0,
                    "additional_charges": {
                        "risk_type_charge": 63.0,
                        "lr_cost": 80.0,
                        "green_tax": 0.0,
                        "handling_charge": 0.0,
                        "pickup_charge": 75.0,
                        "state_tax": 0.0
                    },
                    "other_additional_charges": [
                        {
                            "key_name": "to_pay",
                            "key_value": 0.0
                        },
                        {
                            "key_name": "oda",
                            "key_value": 0.0
                        },
                        {
                            "key_name": "warai_charge",
                            "key_value": 0.0
                        },
                        {
                            "key_name": "odc_charge",
                            "key_value": 0.0
                        }
                    ]
                }
            }
        ],
        "bigship-soumnair@gmail.com": [
            {
                "service_name": "Bigship Premium",
                "total_charges": 552.0,
                "tat": 2,
                "charged_wt": 30.0,
                "risk_type": 0.0,
                "risk_type_charge": 0.0,
                "extra": {
                    "courier_partner_id": 3,
                    "courier_category_id": 1,
                    "courier_type": "Express",
                    "plan_name": "Premium Partner",
                    "risk_type_name": "Carrier Risk",
                    "zone": "W1-W1",
                    "restricted_pincode_message": null,
                    "freight_charge": 290.0,
                    "cod_charge": 0.0,
                    "total_freight_charge": 290.0,
                    "additional_charges": {
                        "risk_type_charge": 72.0,
                        "lr_cost": 90.0,
                        "green_tax": 0.0,
                        "handling_charge": 25.0,
                        "pickup_charge": 75.0,
                        "state_tax": 0.0
                    },
                    "other_additional_charges": [
                        {
                            "key_name": "to_pay",
                            "key_value": 0.0
                        },
                        {
                            "key_name": "oda",
                            "key_value": 0.0
                        },
                        {
                            "key_name": "warai_charge",
                            "key_value": 0.0
                        }
                    ]
                }
            }
        ],
        "bluedart": [
            {
                "service_name": "BlueDart Express",
                "total_charges": 1215.5,
                "tat": 2,
                "charged_wt": 80.0,
                "risk_type": null,
                "risk_type_charge": 85.0,
                "extra": {
                    "min_charged_wt": 15.0,
                    "price_breakup": {
                        "base_freight_charge": 720.0,
                        "fuel_surcharge": 75.0,
                        "fuel_hike": -8.5,
                        "insurance_rov": 85.0,
                        "fm": 90.0,
                        "lm": 0.0,
                        "green": 10.0,
                        "pre_tax_freight_charges": 972.5,
                        "markup": 0.0,
                        "gst": 108.0,
                        "gst_percent": 12,
                        "other_handling_charges": 135.0
                    }
                }
            }
        ],
        "ekart": [
            {
                "service_name": "Ekart Standard",
                "total_charges": 875.0,
                "tat": 3,
                "charged_wt": 65.0,
                "risk_type": 0.0,
                "risk_type_charge": 50.0,
                "extra": {
                    "courier_partner_id": 4,
                    "courier_type": "Surface",
                    "plan_name": "Standard",
                    "risk_type_name": "Carrier Risk",
                    "zone": "W1-W1",
                    "freight_charge": 525.0,
                    "total_freight_charge": 525.0,
                    "additional_charges": {
                        "risk_type_charge": 50.0,
                        "lr_cost": 60.0,
                        "green_tax": 15.0,
                        "handling_charge": 0.0,
                        "pickup_charge": 75.0,
                        "state_tax": 0.0
                    },
                    "other_additional_charges": [
                        {
                            "key_name": "to_pay",
                            "key_value": 0.0
                        },
                        {
                            "key_name": "oda",
                            "key_value": 0.0
                        }
                    ]
                }
            }
        ]
    };

    // Create a status message area
    const statusContainer = document.createElement('div');
    statusContainer.className = 'alert alert-info mb-4';
    statusContainer.innerHTML = '<p class="mb-0">This tool will attempt to connect to the freight API, but will use sample data if connectivity issues occur.</p>';
    document.querySelector('#freightForm button[type="submit"]').parentNode.prepend(statusContainer);

    // Get CSRF token from meta tag if it exists
    function getCSRFToken() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return tokenMeta ? tokenMeta.getAttribute('content') : null;
    }

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

        // Go straight to showing sample data with a notice that it's only for demonstration
        setTimeout(() => {
            loader.style.display = 'none';
            const noticeBox = document.createElement('div');
            noticeBox.className = 'alert alert-warning mb-4';
            noticeBox.innerHTML = `
                <i class="fas fa-info-circle"></i> <strong>Demonstration Mode</strong>
                <p class="mb-0 mt-2">Showing sample freight estimation data from all available carriers.</p>
                <p class="mb-0 small">In a production environment, this would connect to the live API endpoint.</p>
            `;
            resultsContainer.before(noticeBox);
            
            // Show sample data
            displayResults(sampleFreightData);
            console.log('Using sample data for freight estimates');
        }, 1000); // 1 second delay to simulate network request
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
