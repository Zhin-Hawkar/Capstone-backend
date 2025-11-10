<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Appointment extends Model
{
    use HasFactory, Notifiable, HasApiTokens;
    protected $table = 'appointment';

    protected $fillable = [
        'patientId',
        'doctorId',
        'doctorFirstName',
        'doctorLastName',
        'ai_analysis',
        'firstName',
        'lastName',
        'age',
        'gender',
        'email',
        'department',
        'help',
        'medical_record',
        'date_time',
        'status',
    ];



    public function patient()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
