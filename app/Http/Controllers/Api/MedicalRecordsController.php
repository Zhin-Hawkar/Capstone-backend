<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecords;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;



class MedicalRecordsController extends Controller
{
    public function uploadMedicalRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'medicalRecord' => "required|file|mimes:pdf,jpeg|max:20480"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ]);
        }
        $validated = $validator->validated();
        $user = Auth::user();
        if ($request->hasFile('medicalRecord')) {
            $path = $request->file('medicalRecord')->store('user_medical_records', 'public');
            $validated['medicalRecord'] = url('storage/' . $path);
        }

        MedicalRecords::create([
            'userId' => $user->id,
            'fileName' => $request['fileName'],
            'medicalRecord' => $validated['medicalRecord'],
            'privacy' => "public"

        ]);

        $record = DB::table('users')
            ->join('medical_records', 'users.id', '=', 'medical_records.userId')
            ->select('users.email', 'medical_records.fileName', 'medical_records.medicalRecord')
            ->where('users.id', $user->id)
            ->get();

        return response()->json([
            'code' => 200,
            'record' => $record,
        ], 200);
    }
    public function uploadMedicalDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patientId' => "required",
            'medicalRecord' => "required|file|mimes:pdf,jpeg|max:20480"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ]);
        }
        $validated = $validator->validated();
        $user = DB::table("users")->where("id", $validated["patientId"])->first();

        if ($request->hasFile('medicalRecord')) {
            $path = $request->file('medicalRecord')->store('user_medical_records', 'public');
            $validated['medicalRecord'] = url('storage/' . $path);
        }

        MedicalRecords::create([
            'userId' => $user->id,
            'fileName' => $request['fileName'],
            'medicalRecord' => $validated['medicalRecord'],
            'privacy' => "public"
        ]);

        DB::table("accepted_appointment")->where("patientId", $user->id)->update(['status' => "completed"]);

        $record = DB::table('users')
            ->join('medical_records', 'users.id', '=', 'medical_records.userId')
            ->select('users.email', 'medical_records.fileName', 'medical_records.medicalRecord')
            ->where('users.id', $user->id)
            ->get();

        return response()->json([
            'code' => 200,
            'record' => $record,
        ], 200);
    }

    public function showMedicalRecords()
    {
        $user = Auth::user();
        $record = DB::table('users')
            ->join('medical_records', 'users.id', '=', 'medical_records.userId')
            ->select('medical_records.id', 'users.email', 'medical_records.fileName', 'medical_records.medicalRecord', 'medical_records.privacy')
            ->where('users.id', $user->id)
            ->get();
        return response()->json([
            'code' => 200,
            'record' => $record,
        ], 200);
    }
    public function showMedicalRecordsToDoctor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "patientId" => "required",
        ]);
        $validated = $validator->validate();
        $user = DB::table("users")->where("id", $validated["patientId"])->first();
        $record = DB::table('users')
            ->join('medical_records', 'users.id', '=', 'medical_records.userId')
            ->select('medical_records.id', 'users.email', 'medical_records.fileName', 'medical_records.medicalRecord')
            ->where('users.id', $user->id)
            ->where('medical_records.privacy', "public")
            ->get();
        return response()->json([
            'code' => 200,
            'record' => $record,
        ], 200);
    }



    public function editPrivacy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer",
            "privacy" => "required|in:public,private",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "code" => 500,
                "error" => $validator->errors()
            ], 500);
        }

        $validated = $validator->validated();

        try {
            DB::table("medical_records")
                ->where("id", $validated["id"])
                ->update(["privacy" => $validated["privacy"]]);

            return response()->json([
                "code" => 200,
                "message" => "Privacy updated successfully"
            ]);
        } catch (Exception $e) {
            return response()->json([
                "code" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }


    public function deleteMedicalRecord(Request $request)
    {
        $record = MedicalRecords::find($request->id);
        if ($record) {
            $record->delete();
            return response()->json([
                "code" => 200,
                "msg" => "record deleted",
            ]);
        }
    }
}
