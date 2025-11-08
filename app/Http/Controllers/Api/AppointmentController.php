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
                'code' => 401,
                'error' => $validator->errors(),
            ], 401);
        }

        $appointment = Appointment::create([
            'patientId' => $request->patientId,
            'doctorId' => null,
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'age' => $request->age,
            'gender' => $request->gender,
            'email' => $request->email,
            'department' => $request->department,
            'help' => $request->help,
            'date_time' => $request->date_time,
            'status' => 'pending',
        ]);

        $doctors = DB::table("doctor")
            ->where("department", $request->department)
            ->get();

        if ($doctors->isEmpty()) {
            return response()->json([
                'code' => 404,
                'error' => 'No doctors found in this department.',
            ], 404);
        }

        foreach ($doctors as $doctor) {
            PatientNotification::create([
                'patientId' => $request->patientId,
                'doctorId' => $doctor->id,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'age' => $request->age,
                'gender' => $request->gender,
                'email' => $request->email,
                'department' => $request->department,
                'help' => $request->help,
                'date_time' => $request->date_time,
            ]);
        }

        event(new NewAppointmentRequest($appointment, $doctors));

        return response()->json([
            'code' => 200,
            'message' => 'Appointment sent successfully.',
            'appointment' => $appointment,
            'notifiedDoctorsCount' => count($doctors),
            'doctors' => $doctors,
        ], 200);
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
