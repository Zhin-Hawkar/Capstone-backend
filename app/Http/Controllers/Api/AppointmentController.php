<?php

namespace App\Http\Controllers\Api;

use App\Events\NewAppointmentRequest;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PatientNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function sendAppointment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'patientId' => 'required',
                'firstName' => "required",
                'lastName' => "required",
                'age' => "required",
                'gender' => "required",
                'email' => 'required|email',
                'department' => "required",
                'help' => "required",
                'date_time' => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'error' => $validator->errors(),
                ], 400);
            }

            $validated = $validator->validated();



            $doctors = DB::table("doctor")
                ->where("department", $validated["department"])
                ->get();

            foreach ($doctors as $doctor) {
                PatientNotification::create([
                    'patientId' => $validated['patientId'],
                    'doctorId' => $doctor->id,
                    'firstName' => $validated['firstName'],
                    'lastName' => $validated['lastName'],
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'email' => $validated['email'],
                    'department' => $validated['department'],
                    'help' => $validated['help'],
                    'date_time' => $validated['date_time'],
                ]);

                Appointment::create([
                    'patientId' => $validated['patientId'],
                    'doctorId' => $doctor->id,
                    'firstName' => $validated['firstName'],
                    'lastName' => $validated['lastName'],
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'email' => $validated['email'],
                    'department' => $validated['department'],
                    'help' => $validated['help'],
                    'date_time' => $validated['date_time'],
                ]);
            }

            $appointments = DB::table("appointment")->where("patientId", $validated["patientId"]);
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
            'patientId' => "required",
            'doctorId' => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 401,
                'error' => $validator->errors(),
            ]);
        }

        $validated = $validator->validate();

        $doctor = DB::table("doctor")->where("id", $validated["doctorId"])->first();

        $appointment = DB::table('appointment')
            ->where('patientId', $validated['patientId'])
            ->where('doctorId', $validated['doctorId'])
            ->first();

        if ($appointment) {
            DB::table('accepted_appointment')->insert([
                'patientId'   => $appointment->patientId,
                'doctorId'    => $appointment->doctorId,
                'firstName'    => $appointment->firstName,
                'lastName'    => $appointment->lastName,
                'doctorFirstName'    => $doctor->firstName,
                'doctorLastName'    => $doctor->lastName,
                'age'    => $appointment->age,
                'gender'    => $appointment->gender,
                'email'    => $appointment->email,
                'department'    => $appointment->department,
                'help'    => $appointment->help,
                'medical_record'    => $appointment->medical_record,
                'date_time'   => $appointment->date_time ?? null,
                'status'      => $appointment->status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table('appointment')
                ->where('patientId', $validated['patientId'])
                ->delete();
        } else {
            return response()->json([
                'code' => 404,
                'error' => 'Appointment not found.',
            ], 404);
        }
    }
}
