<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PatientNotification extends Model
{
    use HasFactory;
    protected $table = 'patient_notification';
    protected $fillable = [
        'patientId',
        'doctorId',
        'firstName',
        'ai_analysis',
        'lastName',
        'image',
        'age',
        'gender',
        'email',
        'department',
        'help',
        'date_time',
    ];
}
