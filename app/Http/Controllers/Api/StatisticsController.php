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
}
