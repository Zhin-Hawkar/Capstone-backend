<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Doctor extends Model
{

    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'doctor';

    protected $fillable = [
        'firstName',
        'lastName',
        'age',
        'location',
        'image',
        'email',
        'specialization',
        'qualification',
        'licenseId',
        'yearsofexperience',
        'department',
        'description',
        'hospital',
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
