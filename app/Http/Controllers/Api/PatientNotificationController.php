<?php

namespace App\Http\Controllers\Api;

use App\Events\NewDoctorNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\AiChatLog;
use App\Models\PatientNotification;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
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
            ->where("doctorId", $doctor->id)
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
            ->where("patientId", $user->id)
            ->get();

        return response()->json([
            'code' => 200,
            'notification' => $notification
        ], 200);
    }
    public function showPatientAcceptedAppointments()
    {
        try {
            $user = Auth::user();

            $notification = DB::table('accepted_appointment')
                ->where("patientId", $user->id)
                ->get();

            return response()->json([
                'code' => 200,
                'notification' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function showDoctorAcceptedAppointments()
    {
        try {
            $user = Auth::user();

            $notification = DB::table('accepted_appointment')
                ->where("doctorId", $user->id)
                ->get();

            return response()->json([
                'code' => 200,
                'notification' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function showHospitalDoctorAcceptedAppointments()
    {
        try {
            $user = Auth::user();

            $notification = DB::table('accepted_appointment')
                ->where("hospitalId", $user->id)
                ->get();

            return response()->json([
                'code' => 200,
                'notification' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function showAppointments()
    {
        try {
            $user = Auth::user();

            $notification = DB::table('appointment')
                ->where("patientId", $user->id)
                ->get();

            return response()->json([
                'code' => 200,
                'notification' => $notification
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
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
            ], 401);
        }

        $validated = $validator->validate();
        try {

            $doctor = Auth::user();
            if (!$doctor) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Doctor not found.',
                ], 404);
            }


            $notification = DB::table('patient_notification')
                ->where('patientId', $validated['patientId'])
                ->where("doctorId", $doctor->id)
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
                    'doctorImage' => $doctor->doctorImage,
                    'email' => $doctor->email,
                    'department' => $doctor->department,
                    'status' => "rejected",
                    'comment' => $request->comment,
                    'date_time' => $notification->date_time,
                ]);

                DB::table('patient_notification')
                    ->where('patientId', $validated['patientId'])
                    ->where("doctorId", $doctor->id)
                    ->delete();

                DB::table('appointment')
                    ->where('patientId', $validated['patientId'])
                    ->where("doctorId", $doctor->id)
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
    public function rejectDoctorRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => 'required',
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
            $user = Auth::user();
            if (!$doctor) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Doctor not found.',
                ], 404);
            }


            $notification = DB::table('doctor_notification')
                ->where('patientId', $user->id)
                ->where("doctorId", $doctor->id)
                ->first();
            if (!$notification) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Patient notification not found.',
                ], 404);
            }

            DB::transaction(function () use ($validated, $user) {
                DB::table('patient_notification')->where("patientId", $user->id)->where("doctorId", $validated["doctorId"])->update([
                    'status' => "rejected",
                ]);

                DB::table('doctor_notification')
                    ->where('patientId', $user->id)
                    ->where("doctorId", $validated['doctorId'])
                    ->delete();

                DB::table('appointment')
                    ->where('patientId', $user->id)
                    ->where("doctorId", $validated["doctorId"])
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
            $doctor = Auth::user();
            $hospital = DB::table("hospital")->where("id", $doctor->hospitalId)->first();
            if (!$doctor || !$hospital) {
                return response()->json([
                    'code' => 404,
                    'error' => 'record not found.',
                ], 404);
            }

            $notification = DB::table('patient_notification')
                ->where('patientId', $validated['patientId'])
                ->where("doctorId", $doctor->id)
                ->first();
            if (!$notification) {
                return response()->json([
                    'code' => 404,
                    'error' => 'Patient notification not found.',
                ], 404);
            }

            DB::transaction(function () use ($doctor, $notification, $validated, $request, $hospital) {
                DB::table('doctor_notification')->insert([
                    'patientId' => $validated['patientId'],
                    'doctorId' => $doctor->id,
                    'firstName' => $doctor->firstName,
                    'lastName' => $doctor->lastName,
                    'age' => $doctor->age,
                    'email' => $doctor->email,
                    'doctorImage' => $doctor->doctorImage,
                    'department' => $doctor->department,
                    'status' => "accepted",
                    'comment' => $request->comment,
                    'date_time' => $notification->date_time,
                    'hospitalName' => $hospital->hospitalName,
                    'location' => $hospital->location,
                ]);

                DB::table('appointment')
                    ->where('patientId', $validated['patientId'])
                    ->where("doctorId", $doctor->id)
                    ->update([
                        'status' => 'accepted',
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

    public function showPatientLegalDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "doctorId" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 500);
        }
        $validated = $validator->validate();
        $user = Auth::user();
        $legalDocument = DB::table("legal_document")
            ->where("userId", $user->id)
            ->where("doctorId", $validated["doctorId"])
            ->first();

        return response()->json([
            'code' => 200,
            'legal_document' => $legalDocument
        ], 200);
    }
    public function showDoctorLegalDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "patientId" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 500);
        }
        $validated = $validator->validate();
        $user = Auth::user();
        $legalDocument = DB::table("legal_document")
            ->where("doctorId", $user->id)
            ->where("userId", $validated["patientId"])
            ->first();

        return response()->json([
            'code' => 200,
            'legal_document' => $legalDocument
        ], 200);
    }
}
