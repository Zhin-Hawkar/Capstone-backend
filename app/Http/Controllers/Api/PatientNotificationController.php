<?php

namespace App\Http\Controllers\Api;

use App\Events\NewDoctorNotification;
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
            ->where("patientId", $user->id)
            ->get();

        return response()->json([
            'code' => 200,
            'notification' => $notification
        ], 200);
    }


    public function rejectPatientRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required',
            'patientId' => 'required',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'error' => $validator->errors(),
            ], 401);
        }

        $validated = $validator->validate();

        try {
            $doctor = DB::table('doctor')->where('id', $validated['doctorId'])->first();
            if (!$doctor) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Doctor not found.',
                ], 404);
            }

            $notification = DB::table('patient_notification')
                ->where('patientId', $validated['patientId'])
                ->first();
            if (!$notification) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Patient notification not found.',
                ], 404);
            }

            DB::transaction(function () use ($doctor, $notification, $validated, $request) {
                DB::table('doctor_notification')->insert([
                    'patientId' => $validated['patientId'],
                    'firstName' => $doctor->firstName,
                    'lastName' => $doctor->lastName,
                    'age' => $doctor->age,
                    'email' => $doctor->email,
                    'department' => $doctor->department,
                    'comment' => $request->comment,
                    'date_time' => $notification->date_time,
                ]);

                DB::table('patient_notification')
                    ->where('patientId', $validated['patientId'])
                    ->where("doctorId", $validated['doctorId'])
                    ->delete();

                DB::table('appointment')
                    ->where('patientId', $validated['patientId'])
                    ->update(['status' => 'rejected']);
            });

            event(new NewDoctorNotification($doctor));

            return response()->json([
                'code' => 200,
                'doctor' => $doctor,
                'response' => 'Request rejected successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function acceptPatientRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required',
            'patientId' => 'required',
            'comment' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'error' => $validator->errors(),
            ], 401);
        }

        $validated = $validator->validate();

        try {
            $doctor = DB::table('doctor')->where('id', $validated['doctorId'])->first();
            if (!$doctor) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Doctor not found.',
                ], 404);
            }

            $notification = DB::table('patient_notification')
                ->where('patientId', $validated['patientId'])
                ->first();
            if (!$notification) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Patient notification not found.',
                ], 404);
            }

            DB::transaction(function () use ($doctor, $notification, $validated, $request) {
                DB::table('doctor_notification')->insert([
                    'patientId' => $validated['patientId'],
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
                    ->update([
                        'status' => 'accepted',
                        'doctorId' => $validated['doctorId']
                    ]);
            });

            event(new NewDoctorNotification($doctor));

            return response()->json([
                'code' => 200,
                'response' => 'Request accepted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
