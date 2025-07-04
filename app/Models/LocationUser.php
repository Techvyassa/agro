<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class LocationUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'location_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'location_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
} 