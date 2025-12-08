<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class StatisticsController extends Controller
{
    public function getStatistics()
    {
        $doctor = Auth::user();
        try {

            $numberOfPatients = DB::table('accepted_appointment')
                ->where('doctorId', $doctor->id)
                ->count();

            $numberOfRequests = DB::table('patient_notification')->where("doctorId", $doctor->id)->count();

            return response()->json([
                'code' => 200,
                'numberOfPatients' => $numberOfPatients,
                'numberOfRequests' => $numberOfRequests,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function totalDoctors()
    {
        $hospital = Auth::user();

        try {
            $doctors = DB::table("doctor")->where("hospitalId", $hospital->id)->count();

            return response()->json([
                "code" => 200,
                "doctors" => $doctors
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "code" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function totalPatients()
    {
        $hospital = Auth::user();
        try {
            $doctorsId = DB::table("doctor")->where("hospitalId", $hospital->id)->pluck("id");
            $patients = DB::table("accepted_appointment")->whereIn("doctorId", $doctorsId)->count();
            return response()->json([
                "code" => 200,
                "patients" => $patients
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "code" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
