<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Hospital extends Model
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'hospital';

    protected $fillable = [
        'hospitalName',
        'hospitalCode',
        'licenseId',
        'location',
        'image',
        'email',
        'type',
        'phoneNumber',
        'website',
        'departments',
        'workingHours',
        'services',
        'numberOfBeds',
        'description',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
