<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\AiChatLog;
use App\Models\PatientNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientNotificationController extends Controller
{
    public function sendPatientNotification()
    {
        $doctor = Auth::user();

        $notification = DB::table('patient_notification')
            ->where('department', $doctor->department)
            ->get();

        return response()->json([
            'code' => 200,
            'notification' => $notification
        ], 200);
    }

    public function sendDoctorNotification()
    {
        $user = Auth::user();

        $notification = DB::table('doctor_notification')
            ->where('department', $user->department)
            ->get();

        return response()->json([
            'code' => 200,
            'notification' => $notification
        ], 200);
    }


    public function rejectPatientRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patientId' => 'required',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'error' => $validator->errors(),
            ]);
        }

        $validated = $validator->validate();


        $doctor = Auth::user();

        $notification = DB::table('patient_notification')
            ->where('patientId', $request->patientId)
            ->first();

        if (!$notification) {
            return response()->json([
                'code' => 404,
                'error' => 'Patient notification not found.',
            ]);
        }

        DB::table('doctor_notification')->insert([
            'patientId' => $request->patientId,
            'firstName' => $doctor->firstName,
            'lastName' => $doctor->lastName,
            'age' => $doctor->age,
            'gender' => $doctor->gender,
            'email' => $doctor->email,
            'department' => $doctor->department,
            'comment' => $request->comment,
            'date_time' => $notification->date_time,
        ]);

        DB::table('patient_notification')
            ->where('patientId', $validated['patientId'])
            ->delete();

        DB::table('appointment')
            ->where('patientId', $validated['patientId'])
            ->update(['status' => 'rejected']);

        return response()->json([
            'code' => 200,
            'response' => 'Request rejected successfully',
        ], 200);
    }


    public function acceptPatientRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patientId' => 'required',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'error' => $validator->errors(),
            ]);
        }

        $validated = $validator->validate();
        $doctor = Auth::user();

        $notification = DB::table('patient_notification')
            ->where('patientId', $request->patientId)
            ->first();

        if (!$notification) {
            return response()->json([
                'code' => 404,
                'error' => 'Patient notification not found.',
            ]);
        }

        DB::table('doctor_notification')->insert([
            'patientId' => $request->patientId,
            'firstName' => $doctor->firstName,
            'lastName' => $doctor->lastName,
            'age' => $doctor->age,
            'email' => $doctor->email,
            'department' => $doctor->department,
            'comment' => $request->comment,
            'date_time' => $notification->date_time,
        ]);


        DB::table('appointment')
            ->where('patientId', $validated['patientId'])
            ->update(['status' => 'accepted']);

        return response()->json([
            'code' => 200,
            'response' => 'Request rejected successfully',
        ], 200);
    }
}
