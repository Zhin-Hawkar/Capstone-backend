<?php

use App\Http\Controllers\Api\AiChatLogController;
use App\Http\Controllers\Api\MedicalRecordsController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PatientNotificationController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/registerdoctor', [DoctorController::class, 'registerDoctor']);
Route::get('/getallfeedbacks', [FeedbackController::class, 'getAllFeedbacks']);
Route::post('/registerhospital', [HospitalController::class, 'registerHospital']);
Route::post('/loghospitalin', [HospitalController::class, 'logHospitalIn']);
Route::get('/getallhospitals', [HospitalController::class, 'getAllHospitals']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/talktoai', [AiChatLogController::class, 'talkToAi']);
Route::post('/generateresetcode', [UserController::class, 'generateResetCode']);
Route::post('/verifycode', [UserController::class, 'verifyCode']);
Route::post('/resetpassword', [UserController::class, 'resetPassword']);
Route::post('/analyzemedicaldata', [AiChatLogController::class, 'analyzeMedicalData'])
->name('analyze.medical.data');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/showpatientacceptedappointments', [PatientNotificationController::class, 'showPatientAcceptedAppointments']);
    Route::post('/showdoctoracceptedappointments', [PatientNotificationController::class, 'showDoctorAcceptedAppointments']);
    Route::post('/showappointments', [PatientNotificationController::class, 'showAppointments']);
    Route::post('/acceptpatientrequest', [PatientNotificationController::class, 'acceptPatientRequest']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/generatelegaldocument', [AiChatLogController::class, 'generateLegalDocument']);
    Route::post('/rejectpatientrequest', [PatientNotificationController::class, 'rejectPatientRequest']);
    Route::post('/rejectdoctorrequest', [PatientNotificationController::class, 'rejectDoctorRequest']);
    Route::post('/sendappointment', [AppointmentController::class, 'sendAppointment']);
    Route::post('/acceptdoctorrequest', [AppointmentController::class, 'acceptDoctorRequest']);
    Route::post('/uploadmedicalrecord', [MedicalRecordsController::class, 'uploadMedicalRecord']);
    Route::post('/uploadmedicaldocument', [MedicalRecordsController::class, 'uploadMedicalDocument']);
    Route::post('/deletemedicalrecord', [MedicalRecordsController::class, 'deleteMedicalRecord']);
    Route::post('/editdoctorprofile', [DoctorController::class, 'editDoctorProfile']);
    Route::post('/edithospitalprofile', [HospitalController::class, 'editHospitalProfile']);
    Route::get('/sendpatientnotification', [PatientNotificationController::class, 'sendPatientNotification']);
    Route::get('/senddoctornotification', [PatientNotificationController::class, 'sendDoctorNotification']);
    Route::get('/getalldoctors', [HospitalController::class, 'getAllDoctors']);
    Route::post('/loghospitalout', [HospitalController::class, 'logHospitalOut']);
    Route::post('/deletedoctor', [DoctorController::class, 'deleteDoctor']);
    Route::get('/showmedicalrecords', [MedicalRecordsController::class, 'showMedicalRecords']);
    Route::get('/getstatistics', [StatisticsController::class, 'getStatistics']);
    Route::post('/showmedicalrecordstodoctor', [MedicalRecordsController::class, 'showMedicalRecordsToDoctor']);
    Route::post('/edituserprofile', [UserController::class, 'editProfile']);
    Route::post('/addfeedback', [FeedbackController::class, 'addFeedback']);
});
