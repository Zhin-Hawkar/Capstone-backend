<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecords extends Model
{
    use HasFactory;
    protected $table = 'medical_records';

    protected $fillable = [
        'id',
        'userId',
        'fileName',
        'medicalRecord',
    ];
}
