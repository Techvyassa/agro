<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'order_date',
        'pickup_id',
        'pickup_city',
        'pickup_state',
        'return_id',
        'return_city',
        'return_state',
        'invoice_amount',
        'item_name',
        'cod_amount',
        'quantity',
        'buyer_name',
        'buyer_email',
        'buyer_address',
        'buyer_phone',
        'buyer_pincode',
        'order_status',
        'response_code',
        'response_message',
        'raw_request',
        'raw_response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'quantity' => 'integer',
        'response_code' => 'integer',
    ];
}
