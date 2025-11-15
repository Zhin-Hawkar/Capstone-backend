<?php

namespace App\Http\Controllers\Api;

use App\Events\NewAppointmentRequest;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;


class AppointmentController extends Controller
{
    public function sendAppointment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstName' => "required",
                'lastName' => "required",
                'age' => "required",
                'gender' => "required",
                'email' => 'required|email',
                'department' => "required",
                'help' => "required",
                'date_time' => "required",
                'medical_record' => 'nullable|file|mimes:pdf,txt,doc,docx,jpg,jpeg,png|max:5120',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'error' => $validator->errors(),
                ], 400);
            }

            $validated = $validator->validated();
            $user = Auth::user();
            $http = Http::asMultipart();

            if ($request->hasFile('medical_record')) {
                $file = $request->file('medical_record');
                $http = $http->attach(
                    'medical_record',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }

            $aiRequest = $http->post(route('analyze.medical.data'), [
                'text' => $validated['help'],
            ]);

            $aiResponse = $aiRequest->json();
            $aiResult = $aiResponse['ai_analysis'] ?? 'No AI analysis available.';

            $doctors = DB::table("doctor")
                ->where("department", $validated["department"])
                ->get();

            foreach ($doctors as $doctor) {
                PatientNotification::create([
                    'patientId' => $user->id,
                    'doctorId' => $doctor->id,
                    'firstName' => $validated['firstName'],
                    'lastName' => $validated['lastName'],
                    'image' => $user->image,
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'email' => $validated['email'],
                    'department' => $validated['department'],
                    'ai_analysis' => $aiResult,
                    'help' => $validated['help'],
                    'date_time' => $validated['date_time'],
                ]);

                Appointment::create([
                    'patientId' => $user->id,
                    'doctorId' => $doctor->id,
                    'firstName' => $validated['firstName'],
                    'doctorFirstName' => $doctor->firstName,
                    'doctorLastName' => $doctor->lastName,
                    'doctorImage' => $doctor->doctorImage,
                    'lastName' => $validated['lastName'],
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'email' => $validated['email'],
                    'department' => $validated['department'],
                    'help' => $validated['help'],
                    'ai_analysis' => $aiResult,
                    'date_time' => $validated['date_time'],
                ]);
            }

            $appointments = DB::table("appointment")->where("patientId", $user->id)->get();
            foreach ($appointments as $appointment) {
                event(new NewAppointmentRequest($appointment, $appointment->patientId));
            }

            return response()->json([
                'code' => 200,
                'appointment' => $appointment,
                'doctors' => $doctors,
                'message' => 'Appointment request sent successfully to all matching doctors.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function acceptDoctorRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctorId' => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'error' => $validator->errors(),
            ]);
        }

        $validated = $validator->validate();

        $user = Auth::user();
        $doctor = DB::table("doctor")->where("id", $validated["doctorId"])->first();
        $hospital = DB::table("hospital")->where("id", $doctor->hospitalId)->first();

        $appointment = DB::table('appointment')
            ->where('patientId', $user->id)
            ->where('doctorId', $validated['doctorId'])
            ->first();

        if ($appointment) {
            DB::table('accepted_appointment')->insert([
                'patientId'   => $appointment->patientId,
                'doctorId'    => $appointment->doctorId,
                'firstName'    => $appointment->firstName,
                'lastName'    => $appointment->lastName,
                'image' => $user->image,
                'doctorFirstName'    => $doctor->firstName,
                'doctorLastName'    => $doctor->lastName,
                'doctorImage'    => $doctor->doctorImage,
                'age'    => $appointment->age,
                'gender'    => $appointment->gender,
                'email'    => $appointment->email,
                'department'    => $appointment->department,
                'help'    => $appointment->help,
                'medical_record'    => $appointment->medical_record,
                'date_time'   => $appointment->date_time ?? null,
                'status'      => $appointment->status,
                "hospitalId"=>$hospital->id,
                "hospitalName"=>$hospital->hospitalName,
                "hospitalLocation"=>$hospital->location,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('appointment')
                ->where('patientId', $user->id)
                ->delete();

            DB::table('patient_notification')
                ->where('patientId', $user->id)
                ->delete();
        } else {
            return response()->json([
                'code' => 404,
                'error' => 'Appointment not found.',
            ], 404);
        }
    }
}
