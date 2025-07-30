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
        'so_no',
        'items',
        'dimension',
        'weight',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'items' => 'array',
    ];


    /**
     * Accessor to get items as array safely.
     * Use $picking->items_array in Blade or Controller
     */
    // public function getItemsArrayAttribute()
    // {
    //     // If items is not an array, something's wrong
    //     if (!is_array($this->items)) {
    //         return [];
    //     }


    //     $decoded = [];

    //     foreach ($this->items as $item) {
    //         // Handle if element is still a JSON string
    //         if (is_string($item)) {
    //             $parsed = json_decode($item, true);
    //             if (json_last_error() === JSON_ERROR_NONE) {
    //                 $decoded[] = $parsed;
    //             }
    //         } elseif (is_array($item)) {
    //             $decoded[] = $item;
    //         }
    //     }
    //     return $decoded;

    // }

    // /**
    //  * Mutator to store items as JSON string if array is passed.
    //  */
    // public function setItemsAttribute($value)
    // {
    //     $this->attributes['items'] = is_array($value)
    //         ? json_encode($value)
    //         : $value;
    // }

}
