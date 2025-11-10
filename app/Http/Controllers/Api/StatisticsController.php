<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class StatisticsController extends Controller
{
    public function getStatistics()
    {
        $doctor = Auth::user();

        $numberOfPatients = DB::table("accepted_appointment")->where("doctorId", $doctor->id)->count();
        if ($numberOfPatients == null) {
            return response()->json([
                'code' => 200,
                'error' => "no record"
            ], 200);
        }
        $numberOfRequests = DB::table("doctor_notification")->where("doctorId", $doctor->id)->count();
        if ($numberOfRequests == null) {
            return response()->json([
                'code' => 200,
                'error' => "no record"
            ], 200);
        }

        return response()->json([
            'code' => 200,
            'numberOfPatients' => $numberOfPatients,
            'numberOfRequests' => $numberOfRequests,
        ]);
    }
}
