<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperadminItemMaster extends Model
{
    protected $fillable = [
        'item_name',
        'category_name',
        'unit',
        'moq',
        'sku_name_code',
        'category_code',
    ];
}
