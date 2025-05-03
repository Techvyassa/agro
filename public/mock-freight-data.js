// Mock data for freight estimates based on the example response
const mockFreightData = {
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
        },
        {
            "service_name": "FTL Delivery",
            "total_charges": 635.0,
            "tat": 1,
            "charged_wt": 45.0,
            "risk_type": 0.0,
            "risk_type_charge": 0.0,
            "extra": {
                "courier_partner_id": 2,
                "courier_category_id": 1,
                "courier_type": "Express",
                "plan_name": "Premium",
                "risk_type_name": "Carrier Risk",
                "zone": "W1-W1",
                "restricted_pincode_message": null,
                "freight_charge": 420.0,
                "cod_charge": 0.0,
                "total_freight_charge": 420.0,
                "additional_charges": {
                    "risk_type_charge": 75.0,
                    "lr_cost": 50.0,
                    "green_tax": 15.0,
                    "handling_charge": 25.0,
                    "pickup_charge": 50.0,
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
