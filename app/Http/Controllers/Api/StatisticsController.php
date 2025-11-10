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

    $numberOfPatients = DB::table('accepted_appointment')
        ->where('doctorId', $doctor->id)
        ->count();

    $numberOfRequests = DB::table('doctor_notification')
        ->where('doctorId', $doctor->id)
        ->count();

    return response()->json([
        'code' => 200,
        'numberOfPatients' => $numberOfPatients,
        'numberOfRequests' => $numberOfRequests,
    ], 200);
}

}
