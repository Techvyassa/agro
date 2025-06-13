<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPdf extends Model
{
    protected $fillable = ['user_id', 'file_name', 'pdf_data'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //
}
