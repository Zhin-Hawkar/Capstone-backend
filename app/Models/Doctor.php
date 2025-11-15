<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Appointment;

class Doctor extends Model
{

    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'doctor';

    protected $fillable = [
        'firstName',
        'lastName',
        'age',
        'location',
        'doctorImage',
        'email',
        'specialization',
        'qualification',
        'licenseId',
        'yearsofexperience',
        'department',
        'description',
        'hospital',
        'hospitalId',
        'role',
        'password',
    ];

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class);
    }

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
