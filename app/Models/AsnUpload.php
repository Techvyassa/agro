<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsnUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'invoice_number',
        'asn_timestamp',
        'sr',
        'description',
        'part_no',
        'model',
        'pcs',
    ];

    protected $casts = [
        'asn_timestamp' => 'datetime',
    ];
} 