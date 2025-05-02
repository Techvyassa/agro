<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Picking extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'box',
        'items',
        'dimension',
        'weight'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'items' => 'array',
    ];
}
