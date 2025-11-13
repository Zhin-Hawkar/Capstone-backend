<?php

namespace App\Http\Controllers\Api;

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

class FeedbackController extends Controller
{
    public function addFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hospitalId' => "required",
            'rating' => "required",
            'feedback' => "required",
        ]);

        try {
            $validated = $validator->validate();
            $user = Auth::user();
            $hospital = DB::table("hospital")->where("id", $validated['hospitalId'])->first();
            DB::table("feedback")->insert([
                "hospitalId" => $hospital->id,
                "rating" => $validated["rating"],
                "feedback" => $validated["feedback"],
                "patientName" => $user->firstName,
                "hospitalName" => $hospital->hospitalName,
                "hospitalLocation" => $hospital->location,
                "hospitalImage" => $hospital->image,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
