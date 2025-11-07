<?php

use App\Http\Controllers\Api\AiChatLogController;
use App\Http\Controllers\Api\MedicalRecordsController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/registerdoctor', [DoctorController::class, 'registerDoctor']);
Route::post('/registerhospital', [HospitalController::class, 'registerHospital']);
Route::post('/sendappointment', [AppointmentController::class, 'sendAppointment']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/talktoai', [AiChatLogController::class, 'talkToAi']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/uploadmedicalrecord', [MedicalRecordsController::class, 'uploadMedicalRecord']);
    Route::post('/deletemedicalrecord', [MedicalRecordsController::class, 'deleteMedicalRecord']);
    Route::post('/editdoctorprofile', [DoctorController::class, 'editDoctorProfile']);
    Route::post('/edithospitalprofile', [HospitalController::class, 'editHospitalProfile']);
    Route::post('/deletedoctor', [DoctorController::class, 'deleteDoctor']);
    Route::get('/showmedicalrecords', [MedicalRecordsController::class, 'showMedicalRecords']);
    Route::post('/edituserprofile', [UserController::class, 'editProfile']);
});
