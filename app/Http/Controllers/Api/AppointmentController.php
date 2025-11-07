<?php

namespace App\Http\Controllers\Api;

use App\Events\NewAppointmentRequest;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function sendAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => "required",
            'lastName' => "required",
            'age' => "required",
            'gender' => "required",
            'email' => 'required|email|',
            'department' => "required",
            'help' => "required",
            'date_time' => "required",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ]);
        }

        $appointment =  Appointment::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'age' => $request->age,
            'gender' => $request->gender,
            'email' => $request->email,
            'department' => $request->department,
            'help' => $request->help,
            'date_time' => $request->date_time,
        ]);

        $doctors = DB::table("doctor")
            ->join("appointment", "appointment.department", "doctor.department")
            ->select("doctor.*")
            ->get();

        foreach ($doctors as $doctor) {
            event(new NewAppointmentRequest($appointment, $doctor->id));
        }

        return response()->json([
            'code' => 200,
            'appointment' => $appointment,
            "doctor" => $doctors,
        ], 200);
    }
}
