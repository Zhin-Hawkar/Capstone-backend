<?php

namespace App\Http\Controllers\Api;

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
            ]);
        }

        $validated = $validator->validate();

        DB::table('patient_notification')
            ->where('patientId', $validated['patientId'])
            ->delete();

        DB::table('appointment')
            ->where('patientId', $validated['patientId'])
            ->delete();

        return response()->json([
            'code' => 200,
            'response' => 'Request rejected successfully',
        ], 200);
    }
}
